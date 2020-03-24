<?php

namespace Logigator\Api\Auth;


use DI\Annotation\Inject;
use Google_Client;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\ConfigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class GetGoogleAuthUrl
{

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$client = new Google_Client();
		$client->setApplicationName($this->configService->getConfig('google_application_name'));
		$client->setClientId($this->configService->getConfig('google_client_id'));
		$client->setClientSecret($this->configService->getConfig('google_application_name'));
		$client->setRedirectUri($this->configService->getConfig('google_callback_url'));
		$client->setApprovalPrompt('force');

		$url = $client->createAuthUrl(['email', 'profile']);

		$data = ['url' => $url];

		return ApiHelper::createJsonResponse($response, $data);
	}
}
