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
		$route = RouteContext::fromRequest($request)->getRoute();
		if(!$route)
			throw new \Exception();

		if ($request->getMethod() !== 'GET')  {
			if(strpos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
				$request = $request->withParsedBody(json_decode($request->getBody()->getContents()));
				if ($request->getParsedBody() === null)
					throw new HttpBadRequestException($request, 'Invalid JSON received.');
			} else {
				$request = $request->withParsedBody(json_decode(json_encode($request->getParsedBody())));
				if ($request->getParsedBody() === null)
					throw new HttpBadRequestException($request, 'Request was malformed.');
			}
        }

		$pattern = trim(explode('[', explode('{', $route->getPattern())[0])[0], ' /');

		$path = ApiHelper::getPath($this->container->get('ConfigService')->getConfig('json_schemas'), $pattern . '.json');
		if(!file_exists($path))
			throw new HttpBadRequestException($request, 'Invalid data received: Failed to validate input');

		$parsedBody = (object)[ 'arguments' => (object)$route->getArguments(), 'body' => $request->getParsedBody() ];

		$validator = new \JsonSchema\Validator();
		$validator->validate($parsedBody, [ '$ref' => $path ]);
		if($validator->isValid())
			return $handler->handle($request);
		else {
			throw new HttpBadRequestException($request, "Invalid data received: " . $validator->getErrors()[0]['message']);
		}
	}
}
