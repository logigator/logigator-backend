<?php

namespace Logigator\Api\Auth;


use DI\Annotation\Inject;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\AuthenticationService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Logout
{

	/**
	 * @Inject
	 * @var AuthenticationService
	 */
	private $authService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$token = $this->getUserToken();
		$this->authService->logoutUser($token);
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
