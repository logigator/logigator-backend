<?php

namespace Logigator\Api\Auth;


use Logigator\Api\BaseController;
use Abraham\TwitterOAuth\TwitterOAuth;
use Logigator\Api\ApiHelper;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class GetTwitterAuthUrl extends BaseController
{
    public function __invoke(ServerRequestInterface $request, Response $response, array $args) {
        $connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => TWITTER_CALLBACK_URL));

        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

        $data = ['url' => $url];

        return ApiHelper::createJsonResponse($response, $data);
    }
}