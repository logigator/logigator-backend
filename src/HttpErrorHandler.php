<?php
namespace Logigator;

use Slim\Handlers\ErrorHandler;
use Psr\Http\Message\ResponseInterface;

class HttpErrorHandler extends ErrorHandler
{
	public const BAD_REQUEST = 'BAD_REQUEST';
	public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
	public const NOT_ALLOWED = 'NOT_ALLOWED';
	public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
	public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
	public const SERVER_ERROR = 'SERVER_ERROR';
	public const UNAUTHENTICATED = 'UNAUTHENTICATED';


	protected function respond(): ResponseInterface
	{
		$exception = $this->exception;
		$statusCode = 500;
		$type = self::SERVER_ERROR;
		$description = 'An internal error has occurred while processing your request.';

		if ($exception instanceof \Slim\Exception\HttpException) {
			$statusCode = $exception->getCode();
			$description = $exception->getMessage();

			if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
				$type = self::RESOURCE_NOT_FOUND;
			} elseif ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
				$type = self::NOT_ALLOWED;
			} elseif ($exception instanceof \Slim\Exception\HttpUnauthorizedException) {
				$type = self::UNAUTHENTICATED;
			} elseif ($exception instanceof \Slim\Exception\HttpForbiddenException) {
				$type = self::UNAUTHENTICATED;
			} elseif ($exception instanceof \Slim\Exception\HttpBadRequestException) {
				$type = self::BAD_REQUEST;
			} elseif ($exception instanceof \Slim\Exception\HttpNotImplementedException) {
				$type = self::NOT_IMPLEMENTED;
			}
		}

		if (
			!($exception instanceof \Slim\Exception\HttpException)
			&& ($exception instanceof \Exception || $exception instanceof \Throwable)
			&& $this->displayErrorDetails
		) {
			$description = $exception->getMessage();
		}

		$error = [
			'statusCode' => $statusCode,
			'error' => [
				'type' => $type,
				'description' => $description,
			],
		];

		$payload = json_encode($error, JSON_PRETTY_PRINT);
		$response = $this->responseFactory->createResponse($statusCode);
		$response->getBody()->write($payload);

		return $response;
	}
}
