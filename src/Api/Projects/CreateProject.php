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

class CreateProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['name', 'isComponent'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$description = !isset($body['description']) ? null : $body['description'];
		$symbol = !isset($body['symbol']) ? null : $body['symbol'];
		$this->container->get('ProjectService')->createProject($body['name'], $body['isComponent'], $this->getTokenPayload()->sub, $description, $symbol);

		return ApiHelper::createJsonResponse($response, ['createdProject' => 'true']);
	}
}
