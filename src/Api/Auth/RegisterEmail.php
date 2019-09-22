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

		if(!ApiHelper::checkRequiredArgs($body, ['email', 'password', 'username']))
			throw new HttpBadRequestException($request, 'Not all required args were given');

		if(!ApiHelper::checkArgumentFormat('/^[^ ]+$/', [$body['username']]))
            throw new HttpBadRequestException($request, 'Username is invalid.');

        if ($this->container->get('UserService')->fetchUserIdPerEmail($body['email']) != null) {
			throw new HttpBadRequestException($request, 'Email has already been taken.');
		}

        if ($this->container->get('UserService')->fetchUserIdPerUsername($body['username']) != null) {
            throw new HttpBadRequestException($request, 'Username has already been taken.');
        }

		//TODO: Recaptcha

		$this->container->get('UserService')->createUser($body['username'], null, $body['email'], 'local', $body['password']);
		$this->container->get('AuthenticationService')->setUserAuthenticated($this->container->get('UserService')->fetchUserIdPerEmail($body['email']), 'email');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
