<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class RegisterEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, Response $response, array $args) {
		$body = $request->getParsedBody();

		if(!ApiHelper::checkRequiredArgs($body, ['email', 'password'])) {
			return ApiHelper::createJsonResponse($response, null, 400, 'Not all required args were given');
		}

		// TODO: check if user exists
		$userExists = false;

		if ($userExists) {
			return ApiHelper::createJsonResponse($response, null, 409, 'User already exists');
		}

		// TODO: save user data to db and generate userId
		$userId = 0;

		$this->container->get('AuthenticationService')->setUserAuthenticated($userId, 'email');
		return ApiHelper::createJsonResponse($response, ['loggedIn' => 'true']);
	}
}
