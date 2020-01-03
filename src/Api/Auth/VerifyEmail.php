<?php

namespace Logigator\Api\Auth;

use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpUnauthorizedException;

class VerifyEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$tokenPayload = $this->container->get('AuthenticationService')->verifyEmailToken($args['token']);

		if (!$tokenPayload) {
			throw new HttpUnauthorizedException($request, 'Token is invalid');
		}

		$this->container->get('UserService')->setEmailVerified((int)$tokenPayload->sub);

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
