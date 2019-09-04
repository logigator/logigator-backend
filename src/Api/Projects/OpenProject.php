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
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class OpenProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['project_id'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$location = $this->container->get('ProjectService')->fetchLocation($body['project_id'],$this->getTokenPayload()->sub);
		if ($location == null){
			return ApiHelper::createJsonResponse($response, null, 403, "You don't have permission to view this file");
		} else {
			return ApiHelper::createJsonResponse($response, file_get_contents($location));
		}
	}
}
