<?php

namespace Logigator\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

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

		$payload = [
			'status' => 401,
			'error' => [
				'type' => 'UNAUTHENTICATED',
				'description' => 'You are not logged in.',
			]
		];
		$response = new Response(401, null);
		$response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT));
		return $response;
	}
}
