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

		if(strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        }

		if($request->getBody()->getContents() !== '' && $request->getParsedBody() == null ) {
			throw new HttpBadRequestException($request, 'Invalid JSON received.');
		}

		return $handler->handle($request);
	}
}
