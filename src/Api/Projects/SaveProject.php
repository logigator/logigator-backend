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

		if (!ApiHelper::checkRequiredArgs($body, ['projectId', 'name', 'isComponent'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$description = !isset($body['description']) ? null : $body['description'];
		$symbol = !isset($body['symbol']) ? null : $body['symbol'];
		if (isset($body['data']) && is_array($body['data']) && count($body['data']) > 0) {
			//TODO: json file prüfen
			$data = $body['data'];
		} else $data = null;

		if (!$this->container->get('ProjectService')->saveProject($body['projectId'], $body['name'], $body['isComponent'], $this->getTokenPayload()->sub, $data, $description, $symbol))
			throw new HttpBadRequestException($request, 'Project not found.');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
