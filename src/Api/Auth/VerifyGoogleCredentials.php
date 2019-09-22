<?php

namespace Logigator\Api\Auth;


use Exception;
use Google_Client;
use Google_Service_Oauth2;
use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class VerifyGoogleCredentials extends BaseController
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args){
		$body = $request->getParsedBody();

		if(!ApiHelper::checkRequiredArgs($body, ['code']))
			throw new HttpBadRequestException($request, 'Not all required args were given');

		$client = new Google_Client();
		$client->setApplicationName(GOOGLE_APPLICATION_NAME);
		$client->setClientId(GOOGLE_CLIENT_ID);
		$client->setClientSecret(GOOGLE_CLIENT_SECRET);
		$client->setRedirectUri(GOOGLE_CALLBACK_URL);

		try {
			$serviceOAuth = new Google_Service_Oauth2($client);
			$token = $client->fetchAccessTokenWithAuthCode($body['code']);
			$client->setAccessToken($token);
			$content = $serviceOAuth->userinfo->get();

            if ($this->container->get('UserService')->fetchUserIdPerKey($content['id']) == null) {
                $profile_url = Uuid::uuid4()->toString();
                $this->container->get('UserService')->createUser(ApiHelper::removeSpecialCharacters($content['name']) . '_' . ApiHelper::generateRandomString(3), $content['id'], $content['email'], 'google', null, $profile_url);
                file_put_contents(ApiHelper::getProfileImagePath($this->container, $profile_url), fopen($content['picture'], 'r'));
            }

			$this->container->get('AuthenticationService')->setUserAuthenticated($this->container->get('UserService')->fetchUserIdPerKey($content['id']), 'google');

		} catch (Exception $e) {
			throw new HttpUnauthorizedException($request, 'Error verifying oauth-tokens');
		}
		return ApiHelper::createJsonResponse($response, ['loggedIn' => true]);
	}
}
