<?php

namespace Logigator\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HeaderMiddleware
{
	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		return $handler
			->handle($request)
			->withHeader('Content-Type', 'application/json; charset=UTF-8');
	}
}
