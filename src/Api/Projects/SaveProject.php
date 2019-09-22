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

		if (!ApiHelper::checkRequiredArgs($body, ['projectId', 'data'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		if (isset($body['data']) && is_array($body['data']) && count($body['data']) > 0) {
			//TODO: json file prüfen
			$data = $body['data'];
		} else {
			throw new HttpBadRequestException($request, 'Could not save. Corrupted or empty file.');
		}

		if (!$this->container->get('ProjectService')->saveProject($body['projectId'],$this->getTokenPayload()->sub, $data))
			throw new HttpBadRequestException($request, 'Project not found.');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
