<?php

namespace Logigator\Middleware;


use Logigator\Api\ApiHelper;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class JsonValidationMiddleware
{
	public function __invoke(ServerRequestInterface $request, Response $response, callable $next) {
		if ($request->getMethod() === 'GET') {
			return $next($request, $response);
		}
		if($request->getHeader('Content-Type')[0] !== 'application/json') {
			return ApiHelper::createJsonResponse($response, null, 400, 'Content-Type must be application/json.');
		}
		if($request->getBody()->getContents() !== '' && $request->getParsedBody() == null ) {
			return ApiHelper::createJsonResponse($response, null, 400, 'Error while parsing Json.');
		}
		return $next($request, $response);
	}
}
