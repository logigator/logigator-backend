<?php

namespace Logigator\Api\Auth;

use DI\Annotation\Inject;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\AuthenticationService;
use Logigator\Service\UserService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class VerifyEmail
{

	/**
	 * @Inject
	 * @var UserService
	 */
	private $userService;

	/**
	 * @Inject
	 * @var AuthenticationService
	 */
	private $authService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$tokenPayload = $this->authService->verifyEmailToken($args['token']);

		if (!$tokenPayload) {
			throw new HttpBadRequestException($request, 'Token is invalid');
		}

		if ($this->userService->setEmailVerified((int)$tokenPayload->sub, $tokenPayload->mail) === false) {
			throw new HttpBadRequestException($request, 'EMAIL_TAKEN');
		};

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
