<?php


namespace Logigator\Api\Auth;


use DI\Annotation\Inject;
use Logigator\Helpers\ApiHelper;
use Logigator\Service\AuthenticationService;
use Logigator\Service\ConfigService;
use Logigator\Service\DbalService;
use Logigator\Service\SmtpService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class ResendVerificationEmail
{

	/**
	 * @Inject
	 * @var SmtpService
	 */
	private $smtpService;

	/**
	 * @Inject
	 * @var AuthenticationService
	 */
	private $authService;

	/**
	 * @Inject
	 * @var DbalService
	 */
	private $dbalService;

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

		$user = $this->dbalService->getQueryBuilder()
			->select('pk_id, password, login_type, email, username')
			->from('users')
			->where('email = ? or username = ?')
			->setParameter(0, $body->user, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(1, $body->user, \Doctrine\DBAL\ParameterType::STRING)
			->execute()
			->fetch();

		if (!$user)
			throw new HttpUnauthorizedException($request, 'NO_USER');

		if (!$user['password'] || !password_verify($body->password, $user['password']))
			throw new HttpUnauthorizedException($request, 'INVALID_PW');

		if ($user['login_type'] != 'local_not_verified') {
			throw new HttpBadRequestException($request, 'EMAIL_ALREADY_VERIFIED');
		}

		$emailVerifyToken = $this->authService->getEmailVerificationToken($user['pk_id'], $user['email']);
		$this->smtpService->sendMail(
			'noreply',
			[$user['email']],
			'Welcome to Logigator!',
			$this->smtpService->loadTemplate('email-verification-register.html', [
				'recipient' => $user['username'],
				'verifyLink' => 'https://logigator.com/verify-email/' . $emailVerifyToken
			])
		);

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
