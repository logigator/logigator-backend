<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAllComponentsInfo extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$data = $this->container->get('ProjectService')->getAllComponentsInfo($this->getTokenPayload()->sub);
		return ApiHelper::createJsonResponse($response, $data);
	}
}
