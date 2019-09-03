<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class RegisterEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, Response $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['email', 'password'])) {
			return ApiHelper::createJsonResponse($response, null, 400, 'Not all required args were given');
		}
		if ($this->container->get('UserService')->fetchUserIdPerEmail($body['email']) != null) {
			return ApiHelper::createJsonResponse($response, null, 409, 'User already exists');
		}

		//TODO: Security
		$this->container->get('UserService')->createUser(explode("@", $body['email'])[0], null, $body['email'],'local', $body['password']);
		$this->container->get('AuthenticationService')->setUserAuthenticated($this->container->get('UserService')->fetchUserIdPerEmail($body['email']), 'email');

		return ApiHelper::createJsonResponse($response, ['loggedIn' => 'true']);
	}
}
