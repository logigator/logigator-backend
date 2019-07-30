<?php

namespace Logigator\Api\Auth;


use Google_Client;
use Google_Service_Oauth2;
use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class VerifyGoogleCredentials extends BaseController
{

    public function __invoke(ServerRequestInterface $request, Response $response, array $args){
        $body = $request->getParsedBody();

        if(!ApiHelper::checkRequiredArgs($body, ['code'])) {
            return ApiHelper::createJsonResponse($response, null, 400, 'Not all required args were given');
        }

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

            // TODO: save user data to db
            $this->container->get('AuthenticationService')->setUserAuthenticated($content->id, 'google');
        } catch (\Exception $e) {
            return ApiHelper::createJsonResponse($response, null, 401, 'Error verifying oauth-tokens');
        }
        return ApiHelper::createJsonResponse($response, $content);
    }
}