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

		if(!ApiHelper::checkRequiredArgs($body, ['user', 'password'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

        $userId = $this->getDbalQueryBuilder()
            ->select('pk_id')
            ->from('users')
            ->where('(email = ? or username = ?) and login_type = \'local\'')
            ->setParameter(0, $body['user'])
            ->setParameter(1, $body['user'])
            ->execute()
            ->fetch()["pk_id"];

		if ($userId == null)
			throw new HttpBadRequestException($request, 'User not found.');

        $passwordCorrect = $this->container->get('UserService')->verifyPassword($userId, $body['password']);

		if(!$passwordCorrect)
			throw new HttpBadRequestException($request, 'password is incorrect');

		$this->container->get('AuthenticationService')->setUserAuthenticated($userId, 'email');
		return ApiHelper::createJsonResponse($response, ['loggedIn' => true]);
	}
}
