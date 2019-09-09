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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class DeleteProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['project_id'])) {
            throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		if(!$this->container->get('ProjectService')->deleteProject($body['project_id'],$this->getTokenPayload()->sub))
		    throw new HttpBadRequestException($request, 'Project not found.');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
