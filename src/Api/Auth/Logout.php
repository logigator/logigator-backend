<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Logout extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$token = $this->getUserToken();
		$this->container->get('AuthenticationService')->logoutUser($token);
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
