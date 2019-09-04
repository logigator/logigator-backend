<?php

namespace Logigator\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use \Slim\Exception;

class JsonValidationMiddleware
{
	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if ($request->getMethod() === 'GET') {
			return $handler->handle($request);
		}

		if($request->getHeader('Content-Type')[0] !== 'application/json') {
			throw new Exception\HttpBadRequestException($request, 'Content-Type must be application/json.');
		}

		if($request->getBody()->getContents() !== '' && $request->getParsedBody() == null ) {
			throw new HttpBadRequestException($request, 'Invalid JSON received.');
		}

		return $handler->handle($request);
	}
}
