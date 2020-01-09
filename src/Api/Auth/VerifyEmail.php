<?php

namespace Logigator\Api\Auth;

use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class VerifyEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$tokenPayload = $this->container->get('AuthenticationService')->verifyEmailToken($args['token']);

		if (!$tokenPayload) {
			throw new HttpBadRequestException($request, 'Token is invalid');
		}

		if ($this->container->get('UserService')->setEmailVerified((int)$tokenPayload->sub, $tokenPayload->mail) === false) {
			throw new HttpBadRequestException($request, 'EMAIL_TAKEN');
		};

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
