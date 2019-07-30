<?php

namespace Logigator\Api\Auth;


use Google_Client;
use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class GetGoogleAuthUrl extends BaseController
{
    public function __invoke(ServerRequestInterface $request, Response $response, array $args) {
        $client = new Google_Client();
        $client->setApplicationName(GOOGLE_APPLICATION_NAME);
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_CALLBACK_URL);

        $url = $client->createAuthUrl(['email', 'profile']);

        $data = ['url' => $url];

        return ApiHelper::createJsonResponse($response, $data);
    }
}