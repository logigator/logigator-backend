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

		$client = new Google_Client();
		$client->setApplicationName(GOOGLE_APPLICATION_NAME);
		$client->setClientId(GOOGLE_CLIENT_ID);
		$client->setClientSecret(GOOGLE_CLIENT_SECRET);
		$client->setRedirectUri(GOOGLE_CALLBACK_URL);

		try {
			$serviceOAuth = new Google_Service_Oauth2($client);
			$token = $client->fetchAccessTokenWithAuthCode($body->code);
			$client->setAccessToken($token);
			$content = $serviceOAuth->userinfo->get();

			$id = $this->container->get('UserService')->fetchUserIdPerKey($content['id'], 'google');
            if (!$id) {
                $username = ApiHelper::removeSpecialCharacters($content['name']);

                if($this->container->get('UserService')->fetchUserIdPerEmail($content['email']))
                	throw new HttpBadRequestException($request, "Email has already been taken.");

	            if($this->container->get('UserService')->fetchUserIdPerUsername($username))
	            	$username = $username . '_' . ApiHelper::generateRandomString(4);

	            $profile_url = Uuid::uuid4()->toString();
                $id = $this->container->get('UserService')->createUser($username, $content['id'], $content['email'], 'google', null, $profile_url);
                file_put_contents(ApiHelper::getProfileImagePath($this->container, $profile_url), fopen($content['picture'], 'r'));
            }

			$this->container->get('AuthenticationService')->setUserAuthenticated($id, 'google');
		} catch (Exception $e) {
			throw new HttpUnauthorizedException($request, 'Error verifying oauth-tokens');
		}
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
