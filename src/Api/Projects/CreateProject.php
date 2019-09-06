<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class CreateProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['name', 'isComponent'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$description = !isset($body['description']) ? null : $body['description'];
		$id = $this->container->get('ProjectService')->createProject($body['name'], $body['isComponent'], $this->getTokenPayload()->sub, $description);

		return ApiHelper::createJsonResponse($response, ['pk_id' => $id]);
	}
}
