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

		if (!ApiHelper::checkRequiredArgs($body, ['isComponent', 'name'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$body['isComponent'] = filter_var($body['isComponent'], FILTER_VALIDATE_BOOLEAN);

		if($body['isComponent'] && !ApiHelper::checkRequiredArgs($body, ['symbol'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$description = !isset($body['description']) ? null : $body['description'];
        $symbol = !isset($body['symbol']) ? '' : $body['symbol'];
        $id = $this->container->get('ProjectService')->createProject($body['name'], $body['isComponent'], $this->getTokenPayload()->sub, $description, $symbol);

		return ApiHelper::createJsonResponse($response, ['id' => $id]);
	}
}
