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
		$share = $this->container->get("ProjectService")->fetchShare($args['address'], (int)$this->getTokenPayload()->sub);

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

		if (!$project || $project['fk_user'] !== $oldUser) {
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
			->setParameter(0, $project['name'], \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(1, $project['is_component'], \Doctrine\DBAL\ParameterType::BOOLEAN)
			->setParameter(2, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(3, $location, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(4, $project['description'], \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(5, $project['pk_id'], \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(6, $project['created_on'], \Doctrine\DBAL\ParameterType::STRING);

		if($project['is_component']) {
			$query = $query->setValue('symbol', '?')->setParameter(7, $project['symbol'], \Doctrine\DBAL\ParameterType::STRING)
				->setValue('num_inputs', '?')->setParameter(8, $project['num_inputs'], \Doctrine\DBAL\ParameterType::INTEGER)
				->setValue('num_outputs', '?')->setParameter(9, $project['num_outputs'], \Doctrine\DBAL\ParameterType::INTEGER)
				->setValue('labels', '?')->setParameter(10, $project['labels'], \Doctrine\DBAL\ParameterType::STRING);
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

		if (file_exists(ApiHelper::getProjectPreviewPath($this->container, $project['location'])))
			copy(ApiHelper::getProjectPreviewPath($this->container, $project['location']), ApiHelper::getProjectPreviewPath($this->container, $location));
		file_put_contents(ApiHelper::getProjectPath($this->container, $location), json_encode($json));

		return $mappings;
	}
}
