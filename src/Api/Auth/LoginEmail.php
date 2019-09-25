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

        $user = $this->getDbalQueryBuilder()
            ->select('pk_id, password')
            ->from('users')
            ->where('email = ? or username = ?')
            ->setParameter(0, $body->user)
            ->setParameter(1, $body->user)
            ->execute()
            ->fetch();

		if (!$user)
			throw new HttpBadRequestException($request, 'User not found.');

		if (!$user['password'] || !password_verify($body->password, $user['password']))
			throw new HttpBadRequestException($request, 'Password is incorrect.');

		$this->container->get('AuthenticationService')->setUserAuthenticated($user['pk_id'], 'email');
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
