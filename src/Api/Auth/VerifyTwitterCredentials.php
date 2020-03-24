<?php

namespace Logigator\Api\Auth;


use Abraham\TwitterOAuth\TwitterOAuth;
use DI\Annotation\Inject;
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

class VerifyTwitterCredentials
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

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

		try {
			$connection = new TwitterOAuth($this->configService->getConfig('twitter_consumer_key'), $this->configService->getConfig('twitter_consumer_secret'));
			$access_token = $connection->oauth("oauth/access_token", [ 'oauth_verifier' => $body->oauth_verifier, 'oauth_token' => $body->oauth_token ]);
			$connection = new TwitterOAuth($this->configService->getConfig('twitter_consumer_key'), $this->configService->getConfig('twitter_consumer_secret'), $access_token['oauth_token'], $access_token['oauth_token_secret']);
			$content = $connection->get("account/verify_credentials", [ 'include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true' ]);
		} catch (\Exception $e) {
			throw new HttpUnauthorizedException($request, 'Error verifying oauth-tokens');
		}

		$id = $this->userService->fetchUserIdPerKey($content->id_str, 'twitter');
		if (!$id) {
			$username = ApiHelper::removeSpecialCharacters($content->screen_name);

			if($this->userService->fetchUserIdPerEmail($content->email))
				throw new HttpBadRequestException($request, "EMAIL_TAKEN");

			if($this->userService->fetchUserIdPerUsername($username))
				$username = $username . '_' . ApiHelper::generateRandomString(4);

			$profile_url = Uuid::uuid4()->toString();
			$id = $this->userService->createUser($username, $content->id_str, $content->email, 'twitter', null, $profile_url);
			file_put_contents(PathHelper::getProfileImagePath($this->configService, $profile_url), fopen($content->profile_image_url_https, 'r'));
		}

		$this->authService->setUserAuthenticated($id, 'twitter');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
