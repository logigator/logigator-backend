<?php


namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class ResendVerificationEmail extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$body = $request->getParsedBody();

		$user = $this->getDbalQueryBuilder()
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

		$emailVerifyToken = $this->container->get('AuthenticationService')->getEmailVerificationToken($user['pk_id'], $user['email']);
		$this->container->get('SmtpService')->sendMail(
			'noreply',
			[$user['email']],
			'Welcome to Logigator!',
			$this->container->get('SmtpService')->loadTemplate('email-verification-register.html', [
				'recipient' => $user['username'],
				'verifyLink' => 'https://logigator.com/verify-email/' . $emailVerifyToken
			])
		);

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
