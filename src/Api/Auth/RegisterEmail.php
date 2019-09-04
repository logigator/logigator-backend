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

    if ($this->container->get('UserService')->fetchUserIdPerEmail($body['email']) != null) {
			throw new HttpBadRequestException($request, 'User already exists');
		}
    

		//TODO: Security
		$this->container->get('UserService')->createUser(explode("@", $body['email'])[0], null, $body['email'],'local', $body['password']);
		$this->container->get('AuthenticationService')->setUserAuthenticated($this->container->get('UserService')->fetchUserIdPerEmail($body['email']), 'email');

		return ApiHelper::createJsonResponse($response, ['loggedIn' => 'true']);
	}
}
