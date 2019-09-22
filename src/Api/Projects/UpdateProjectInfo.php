<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class UpdateProjectInfo extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		// TODO: all the things
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['projectId', 'name', 'isComponent'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$description = !isset($body['description']) ? null : $body['description'];
		$symbol = !isset($body['symbol']) ? null : $body['symbol'];

		if (!$this->container->get('ProjectService')->updateProjectInfo($body['projectId'], $body['name'], $body['isComponent'], $this->getTokenPayload()->sub, $description, $symbol))
			throw new HttpBadRequestException($request, 'Project not found.');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
