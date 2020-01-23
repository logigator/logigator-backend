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
			throw new HttpBadRequestException($request, 'EMAIL_TAKEN');
		}

        if ($this->container->get('UserService')->fetchUserIdPerUsername($body->username) != null) {
            throw new HttpBadRequestException($request, 'USERNAME_TAKEN');
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

        if (!$recaptcha || !isset($recaptcha->success) || !isset($recaptcha->action))
        	throw new \Exception('Could not verify ReCaptcha.');

        if($recaptcha->success !== true || $recaptcha->action !== 'register')
        	throw new HttpForbiddenException($request, 'ReCaptcha is invalid.');

        if($recaptcha->score < 0.5)
        	throw new HttpForbiddenException($request, 'Trust score is not high enough.');

		$this->container->get('UserService')->createUser($body->username, null, $body->email, 'local_not_verified', $body->password);

		$userId = $this->container->get('UserService')->fetchUserIdPerEmail($body->email);
		$emailVerifyToken = $this->container->get('AuthenticationService')->getEmailVerificationToken($userId, $body->email);
		$user =  $this->container->get('UserService')->fetchUser($userId);
		$this->container->get('SmtpService')->sendMail(
			'noreply',
			[$body->email],
			'Welcome to Logigator!',
			$this->container->get('SmtpService')->loadTemplate('email-verification-register.html', [
				'recipient' => $user['username'],
				'verifyLink' => 'https://logigator.com/verify-email/' . $emailVerifyToken
			])
		);

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
