<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAllProjectsInfo extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$data = $this->container->get('ProjectService')->getAllProjectsInfo((int)$this->getTokenPayload()->sub);
		for ($i = 0; $i < count($data); $i++) {
			if ($data[$i]['last_edited'] === $data[$i]['created_on']) {
				$data[$i]['location'] = $this->container->get('ConfigService')->getConfig('project_default_image');
			}
		}
		return ApiHelper::createJsonResponse($response, $data);
	}
}
