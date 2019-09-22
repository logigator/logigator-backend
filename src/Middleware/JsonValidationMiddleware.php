<?php

namespace Logigator\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use \Slim\Exception;
use Slim\Psr7\Response;

class JsonValidationMiddleware
{
	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if ($request->getMethod() === 'GET') {
			return $handler->handle($request);
		}

		if(strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));

			if($request->getParsedBody() === null ) {
				$payload = [
					'status' => 400,
					'error' => [
						'type' => 'BAD_REQUEST',
						'description' => 'Invalid JSON received.',
					]
				];
				$response = new Response(400, null);
				$response->getBody()->write(json_encode($payload, JSON_PRETTY_PRINT));
				return $response;
			}
        }

		return $handler->handle($request);
	}
}
