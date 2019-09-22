<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;

class SaveProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['id', 'data'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$path = ApiHelper::getProjectPath($this->container,  $this->container->get('ProjectService')->fetchLocation($body['id'], $this->getTokenPayload()->sub));
		if(!$path)
			throw new HttpBadRequestException($request, 'Project not found.');

		// TODO: JSON file check
		if(file_put_contents(ApiHelper::getProjectPath($this->container, $path), $body['data']) === false)
			throw new HttpInternalServerErrorException($request, 'An error occured trying to save your project.');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
