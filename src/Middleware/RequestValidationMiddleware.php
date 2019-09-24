<?php

namespace Logigator\Middleware;

use Logigator\Api\ApiHelper;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteContext;

class RequestValidationMiddleware
{
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if ($request->getMethod() === 'GET') {
			return $handler->handle($request);
		}

		if (strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
			if($request->getParsedBody() === null)
				throw new HttpBadRequestException($request, 'Invalid JSON received.');
        }

		$route = RouteContext::fromRequest($request)->getRoute()->getPattern();
		if(!$route)
			throw new \Exception();

		$path = ApiHelper::getPath($this->container->get('ConfigService')->getConfig('json_schemas'), trim($route, ' /') . '.json');
		if(!file_exists($path))
			throw new HttpBadRequestException($request, 'Invalid data received: Failed to validate input');

		$parsedBody = (object)$request->getParsedBody();
		$validator = new \JsonSchema\Validator();
		$validator->validate($parsedBody, [ '$ref' => $path ]);
		if($validator->isValid())
			return $handler->handle($request);
		else {
			throw new HttpBadRequestException($request, "Invalid data received: " . $validator->getErrors()[0]['message']);
		}
	}
}
