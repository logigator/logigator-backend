<?php

namespace Logigator\Api\Auth;


use DI\Annotation\Inject;
use Abraham\TwitterOAuth\TwitterOAuth;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\ConfigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class GetTwitterAuthUrl
{

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$connection = new TwitterOAuth($this->configService->getConfig('twitter_consumer_key'), $this->configService->getConfig('twitter_consumer_secret'));
		$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $this->configService->getConfig('twitter_callback_url')));

		$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

		$data = ['url' => $url];

		return ApiHelper::createJsonResponse($response, $data);
	}
}
