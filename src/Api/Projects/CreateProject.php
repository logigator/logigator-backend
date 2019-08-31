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
use Slim\Http\Response;

class CreateProject
{
	public function __invoke(ServerRequestInterface $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		if(!ApiHelper::checkRequiredArgs($body, ['name','isComponent'])) {
			return ApiHelper::createJsonResponse($response, null, 400, 'Not all required args were given');
		}

		$this->container->get('ProjectService')->createProject($body['name'],$body['isComponent']);
		return ApiHelper::createJsonResponse($response, ['createdProject' => 'true']);
	}
}
