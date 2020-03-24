<?php

namespace Logigator\Middleware;

use DI\Annotation\Inject;
use JsonSchema\Constraints\Constraint;
use Logigator\Helpers\PathHelper;
use Logigator\Service\ConfigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteContext;

class RequestValidationMiddleware
{

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

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

		$path = PathHelper::getPath($this->configService->getConfig('json_schemas'), $pattern . '.json');
		if(!file_exists($path))
			throw new HttpBadRequestException($request, 'Invalid data received: Failed to validate input');

		$parsedBody = (object)[ 'arguments' => (object)$route->getArguments(), 'body' => (object)$request->getParsedBody() ];

		$validator = new \JsonSchema\Validator();
		$validator->validate($parsedBody, [ '$ref' => 'file://' . realpath($path) ], Constraint::CHECK_MODE_COERCE_TYPES);
		if($validator->isValid())
			return $handler->handle($request);
		else {
			throw new HttpBadRequestException($request, "Invalid data received: " . $validator->getErrors()[0]['message']);
		}
	}
}
