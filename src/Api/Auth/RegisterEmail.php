<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class RegisterEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

		if(!ApiHelper::checkRequiredArgs($body, ['email', 'password']))
			throw new HttpBadRequestException($request, 'Not all required args were given');

		// TODO: check if user exists
		$userExists = false;

		if ($userExists)
			throw new HttpBadRequestException($request, 'User already exists');

		// TODO: save user data to db and generate userId
		$userId = 0;

		$this->container->get('AuthenticationService')->setUserAuthenticated($userId, 'email');
		return ApiHelper::createJsonResponse($response, ['loggedIn' => 'true']);
	}
}
