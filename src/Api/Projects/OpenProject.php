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

class OpenProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['id'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$project = $this->container->get('ProjectService')->getProjectInfo($body['id'], $this->getTokenPayload()->sub);

		if (!$project)
            throw new HttpBadRequestException($request, 'Project not found.');

		$path = ApiHelper::getProjectPath($this->container, $project['location']);

		$project['data'] = file_exists($path);

		if($project['data'])
            $project['data'] = file_get_contents($path);

        if(!$project['data'])
            $project['data'] = '{}';

		return ApiHelper::createJsonResponse($response, ['project' => json_encode($project)]);
	}
}
