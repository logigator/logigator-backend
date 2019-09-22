<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class DeleteProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		if(!$this->container->get('ProjectService')->deleteProject($args['id'], $this->getTokenPayload()->sub))
		    throw new HttpBadRequestException($request, 'Project not found.');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
