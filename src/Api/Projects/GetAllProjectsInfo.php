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

class GetAllProjectsInfo extends BaseController
{
	public function __invoke(ServerRequestInterface $request, Response $response, array $args)
	{
		$data = $this->container->get('ProjectService')->getAllProjectsInfo($this->getTokenPayload()->sub);
		return ApiHelper::createJsonResponse($response, $data);
	}
}
