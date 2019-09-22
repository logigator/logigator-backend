<?php

namespace Logigator\Api\Auth;


use Abraham\TwitterOAuth\TwitterOAuth;
use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class VerifyTwitterCredentials extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

		if(!ApiHelper::checkRequiredArgs($body, ['oauth_verifier', 'oauth_token']))
			throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);

		try {
			$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
			$access_token = $connection->oauth("oauth/access_token", ['oauth_verifier' => $body['oauth_verifier'], 'oauth_token' => $body['oauth_token']]);
			$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
			$content = $connection->get("account/verify_credentials", ['include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true']);

			$id = $this->container->get('UserService')->fetchUserIdPerKey($content->id_str, 'twitter');
            if (!$id) {
	            $username = ApiHelper::removeSpecialCharacters($content->screen_name);

	            if($this->container->get('UserService')->fetchUserIdPerEmail($content->email))
		            throw new HttpBadRequestException($request, "Email has already been taken.");

	            if($this->container->get('UserService')->fetchUserIdPerUsername($username))
		            $username = $username . '_' . ApiHelper::generateRandomString(4);

                $profile_url = Uuid::uuid4()->toString();
                $id = $this->container->get('UserService')->createUser($username, $content->id_str, $content->email, 'twitter', null, $profile_url);
                file_put_contents(ApiHelper::getProfileImagePath($this->container, $profile_url), fopen($content->profile_image_url_https, 'r'));
            }

			$this->container->get('AuthenticationService')->setUserAuthenticated($id, 'twitter');
		} catch (\Exception $e) {
			throw new HttpUnauthorizedException($request, 'Error verifying oauth-tokens');
		}
		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
