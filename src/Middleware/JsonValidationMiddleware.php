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

		if($request->getBody()->getContents() !== '' && $request->getParsedBody() == null ) {
			throw new HttpBadRequestException($request, 'Invalid JSON received.');
		}

		return $handler->handle($request);
	}
}
