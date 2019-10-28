<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class RegisterEmail extends BaseController
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

        if ($this->container->get('UserService')->fetchUserIdPerEmail($body->email) != null) {
			throw new HttpBadRequestException($request, 'Email has already been taken.');
		}

        if ($this->container->get('UserService')->fetchUserIdPerUsername($body->username) != null) {
            throw new HttpBadRequestException($request, 'Username has already been taken.');
        }

        $recaptcha = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
        	'http' => [
        		'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
		        'method' => 'POST',
		        'content' => http_build_query([
		        	'secret' => $this->container->get('ConfigService')->getConfig('google_recaptcha_secret'),
			        'response' => $body->recaptcha,
			        'remoteip' => $_SERVER['REMOTE_ADDR']
		        ])
	        ]
        ])));

        if (!$recaptcha || !isset($recaptcha->success))
        	throw new \Exception('Could not verify ReCaptcha.');

        if($recaptcha->success !== true)
        	throw new HttpForbiddenException($request, 'ReCaptcha is invalid.');

		$this->container->get('UserService')->createUser($body->username, null, $body->email, 'local', $body->password);
		$this->container->get('AuthenticationService')->setUserAuthenticated($this->container->get('UserService')->fetchUserIdPerEmail($body->email), 'local');

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
