<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class GetShare extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		if($this->isUserAuthenticated())
            $share = $this->container->get("ProjectService")->fetchShare($args['address'], (int)$this->getTokenPayload()->sub);
		else
			$share = $this->container->get("ProjectService")->fetchShare($args['address'], 0, true);

        if(!$share)
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if(file_exists(ApiHelper::getProjectPath($this->container, $share['project.location']))) {
			$share['data'] = json_decode(file_get_contents(ApiHelper::getProjectPath($this->container, $share['project.location'])));
			$share['components'] = $this->resolveDependencies($share['user.pk_id'], $share['data'], []);
		}
		else
			$share['data'] = [];

		unset ($share['user.pk_id']);
        return ApiHelper::createJsonResponse($response, $share, true);
    }

    private function resolveDependencies($user, $data, $components) {
		foreach ($data->mappings as $compId) {
			if (isset($components[$compId->database]))
				continue;

			$compData = $this->getDbalQueryBuilder()
				->select('pk_id, name, description, symbol, last_edited, created_on, is_component, location, num_inputs, num_outputs')
				->from('projects')
				->where('pk_id = ? and is_component = 1 and fk_user = ?')
				->setParameter(0, $compId->database, \Doctrine\DBAL\ParameterType::INTEGER)
				->setParameter(1, $user, \Doctrine\DBAL\ParameterType::INTEGER)
				->execute()
				->fetch();

			if ($compData) {
				$components[$compId->database] = $compData;
				if(file_exists(ApiHelper::getProjectPath($this->container, $compData['location']))) {
					$json = json_decode(file_get_contents(ApiHelper::getProjectPath($this->container, $compData['location'])));
					$components[$compId->database]['data'] = $json;
					$components = $this->resolveDependencies($user, $json, $components);
				}
			}
		}
		return $components;
	}
}
