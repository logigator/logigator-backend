<?php

namespace Logigator\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

class LoginValidationMiddleware
{
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if($this->container->get('AuthenticationService')->verifyToken() !== null)
			return $handler->handle($request);

		throw new HttpUnauthorizedException($request, 'You are not logged in.');
	}
}
