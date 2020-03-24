<?php

namespace Logigator\Api\Auth;

use DI\Annotation\Inject;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\AuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpUnauthorizedException;

class LoginEmail
{

	/**
	 * @Inject
	 * @var AuthenticationService
	 */
	private $authService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

        $user = $this->getDbalQueryBuilder()
            ->select('pk_id, password, login_type')
            ->from('users')
            ->where('email = ? or username = ?')
            ->setParameter(0, $body->user, \Doctrine\DBAL\ParameterType::STRING)
            ->setParameter(1, $body->user, \Doctrine\DBAL\ParameterType::STRING)
            ->execute()
            ->fetch();

		if (!$user)
			throw new HttpUnauthorizedException($request, 'NO_USER');

		if ($user['login_type'] == 'local_not_verified') {
			throw new HttpUnauthorizedException($request, 'EMAIL_NOT_VERIFIED');
		}

		if (!$user['password'] || !password_verify($body->password, $user['password']))
			throw new HttpUnauthorizedException($request, 'INVALID_PW');

		$this->authService->setUserAuthenticated($user['pk_id'], 'email');
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
