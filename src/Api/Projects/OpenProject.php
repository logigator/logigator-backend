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
		$project = $this->container->get('ProjectService')->getProjectInfo($args['id'], (int)$this->getTokenPayload()->sub);

		if (!$project)
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		$path = ApiHelper::getProjectPath($this->container, $project['location']);

		$project['data'] = file_exists($path);

		if($project['data'])
            $project['data'] = json_decode(file_get_contents($path));

        if(!$project['data'])
            $project['data'] = [];

		return ApiHelper::createJsonResponse($response, ['project' => $project]);
	}
}
