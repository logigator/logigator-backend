<?php

namespace Logigator\Api\Auth;


use DI\Annotation\Inject;
use Exception;
use Google_Client;
use Google_Service_Oauth2;
use Logigator\Helpers\ApiHelper;
use Logigator\Helpers\PathHelper;
use Logigator\Service\AuthenticationService;
use Logigator\Service\ConfigService;
use Logigator\Service\UserService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class VerifyGoogleCredentials
{

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

	/**
	 * @Inject
	 * @var UserService
	 */
	private $userService;

	/**
	 * @Inject
	 * @var AuthenticationService
	 */
	private $authService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		$client = new Google_Client();
		$client->setApplicationName($this->configService->getConfig('google_application_name'));
		$client->setClientId($this->configService->getConfig('google_client_id'));
		$client->setClientSecret($this->configService->getConfig('google_application_name'));
		$client->setRedirectUri($this->configService->getConfig('google_callback_url'));

		try {
			$serviceOAuth = new Google_Service_Oauth2($client);
			$token = $client->fetchAccessTokenWithAuthCode($body->code);
			$client->setAccessToken($token);
			$content = $serviceOAuth->userinfo->get();
		} catch (Exception $e) {
			throw new HttpUnauthorizedException($request, 'Error verifying oauth-tokens');
		}

		$id = $this->userService->fetchUserIdPerKey($content['id'], 'google');
		if (!$id) {
			$username = ApiHelper::removeSpecialCharacters($content['name']);

			if ($this->userService->fetchUserIdPerEmail($content['email']))
				throw new HttpBadRequestException($request, "EMAIL_TAKEN");

			if ($this->userService->fetchUserIdPerUsername($username))
				$username = $username . '_' . ApiHelper::generateRandomString(4);

			$profile_url = Uuid::uuid4()->toString();
			$id = $this->userService->createUser($username, $content['id'], $content['email'], 'google', null, $profile_url);
			file_put_contents(PathHelper::getProfileImagePath($this->configService, $profile_url), fopen($content['picture'], 'r'));
		}

		$this->authService->setUserAuthenticated($id, 'google');
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
