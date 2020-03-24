<?php

namespace Logigator\Api\Auth;


use DI\Annotation\Inject;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\AuthenticationService;
use Logigator\Service\ConfigService;
use Logigator\Service\SmtpService;
use Logigator\Service\UserService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class RegisterEmail
{

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

	/**
	 * @Inject
	 * @var SmtpService
	 */
	private $smtpService;

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

        if ($this->userService->fetchUserIdPerEmail($body->email) != null) {
			throw new HttpBadRequestException($request, 'EMAIL_TAKEN');
		}

        if ($this->userService->fetchUserIdPerUsername($body->username) != null) {
            throw new HttpBadRequestException($request, 'USERNAME_TAKEN');
        }

        $recaptcha = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
        	'http' => [
        		'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
		        'method' => 'POST',
		        'content' => http_build_query([
		        	'secret' => $this->configService->getConfig('google_recaptcha_secret'),
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

		$this->userService->createUser($body->username, null, $body->email, 'local_not_verified', $body->password);

		$userId = $this->userService->fetchUserIdPerEmail($body->email);
		$emailVerifyToken = $this->authService->getEmailVerificationToken($userId, $body->email);
		$user =  $this->userService->fetchUser($userId);
		$this->smtpService->sendMail(
			'noreply',
			[$body->email],
			'Welcome to Logigator!',
			$this->smtpService->loadTemplate('email-verification-register.html', [
				'recipient' => $user['username'],
				'verifyLink' => 'https://logigator.com/verify-email/' . $emailVerifyToken
			])
		);

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
