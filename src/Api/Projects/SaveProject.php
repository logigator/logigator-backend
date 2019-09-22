<?php

namespace Logigator\Api\Projects;

use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class SaveProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['id', 'data'])) {
			throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);
		}

		$path = $this->container->get('ProjectService')->fetchLocation($body['id'], $this->getTokenPayload()->sub);
		if(!$path)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		// TODO: JSON file check
		if(file_put_contents(ApiHelper::getProjectPath($this->container, $path), json_encode($body['data'])) === false)
			throw new \Exception();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
