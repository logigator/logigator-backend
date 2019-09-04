<?php

namespace Logigator\Api\Auth;

use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class LoginEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

		if(!ApiHelper::checkRequiredArgs($body, ['email', 'password'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$userId = $this->container->get('UserService')->fetchUserIdPerEmail($body['email']);
		$passwordCorrect = $this->container->get('UserService')->verifyPassword($body['email'],$body['password']);

		if ($userId == null)
			throw new HttpBadRequestException($request, 'User not found.');

		if(!$passwordCorrect)
			throw new HttpBadRequestException($request, 'password is incorrect');

		$this->container->get('AuthenticationService')->setUserAuthenticated($userId, 'email');
		return ApiHelper::createJsonResponse($response, ['loggedIn' => 'true']);
	}
}
