<?php

namespace Logigator\Middleware;

use DI\Annotation\Inject;
use Logigator\Service\AuthenticationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

class LoginValidationMiddleware
{

	/**
	 * @Inject
	 * @var AuthenticationService
	 */
	private $authService;

	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if($this->authService->verifyToken() !== null)
			return $handler->handle($request);

		throw new HttpUnauthorizedException($request, 'You are not logged in.');
	}
}
