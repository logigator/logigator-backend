<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class Logout extends BaseController
{
	public function __invoke(ServerRequestInterface $request, Response $response, array $args) {
		if(!$this->isUserAuthenticated()) {
			return ApiHelper::createJsonResponse($response, null, 401, 'Not logged in');
		}
		$token = $this->getUserToken();
		$this->container->get('AuthenticationService')->logoutUser($token);
		return ApiHelper::createJsonResponse($response, ['loggedOut' => 'true']);
	}
}
