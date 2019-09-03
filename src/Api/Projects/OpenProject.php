<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 10:41
 */

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class OpenProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, Response $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!$this->isUserAuthenticated()) {
			return ApiHelper::createJsonResponse($response, null, 401, 'Not logged in');
		}
		if (!ApiHelper::checkRequiredArgs($body, ['project_id'])) {
			return ApiHelper::createJsonResponse($response, null, 400, 'Not all required args were given');
		}

		$location = $this->container->get('ProjectService')->fetchLocation($body['project_id'],$this->getTokenPayload()->sub);
		if ($location == null){
			return ApiHelper::createJsonResponse($response, null, 403, "You don't have permission to view this file");
		} else {
			return ApiHelper::createJsonResponse($response, file_get_contents($location));
		}
	}
}
