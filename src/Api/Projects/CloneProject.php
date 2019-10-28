<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;

class CloneProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$share = $this->container->get("ProjectService")->fetchShare($args['address'], $this->getTokenPayload()->sub);

		if(!$share)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		$user = $this->container->get("UserService")->fetchUserIdPerUsername($share['user.username']);

		if(!$user)
			throw new \Exception();

		$mappings = $this->clone($share['project.id'], $user, []);

		return ApiHelper::createJsonResponse($response, ['success' => true, 'id' => $mappings[$share['project.id']]]);
	}

	private function clone(int $id, int $oldUser, array $mappings): array {
		$project = $this->container->get('ProjectService')->getProjectInfo($id);

		if (!$project) {
			$mappings[$id] = 0;
			return $mappings;
		}

		$location = Uuid::uuid4()->toString();

		$query = $this->getDbalQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('description', '?')
			->setValue('fk_originates_from', '?')
			->setValue('created_on', '?')
			->setParameter(0, $project['name'])
			->setParameter(1, !!$project['is_component'])
			->setParameter(2, $this->getTokenPayload()->sub)
			->setParameter(3, $location)
			->setParameter(4, $project['description'])
			->setParameter(5, $oldUser)
			->setParameter(6, $project['created_on']);

		if($project['is_component']) {
			$query = $query->setValue('symbol', '?')->setParameter(7, $project['symbol'])
				->setValue('num_inputs', '?')->setParameter(8, $project['num_inputs'])
				->setValue('num_outputs', '?')->setParameter(9, $project['num_outputs']);
		}

		$query->execute();
		$mappings[$id] = (int)$this->getDbalConnection()->lastInsertId();

		$path = ApiHelper::getProjectPath($this->container, $project['location']);
		$json = file_exists($path);

		if(!$json)
			return $mappings;
		$json = json_decode(file_get_contents($path));
		if(!$json)
			return $mappings;

		for($i = 0; $i < count($json->mappings); $i++) {
			if(!isset($mappings[$json->mappings[$i]->database])) {
				$mappings = $this->clone($json->mappings[$i]->database, $oldUser, $mappings);
			}
			$json->mappings[$i]->database = $mappings[$json->mappings[$i]->database];
		}

		file_put_contents(ApiHelper::getProjectPath($this->container, $location), json_encode($json));

		return $mappings;
	}
}
