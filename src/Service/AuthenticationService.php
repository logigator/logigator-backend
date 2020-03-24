<?php

namespace Logigator\Service;


use DI\Annotation\Inject;
use Firebase\JWT\JWT;

class AuthenticationService
{

	/**
	 * @Inject
	 * @var ConfigService
	 */
	private $configService;

	private $tokenPayload;
	private $checkedToken;

	public function isUserAuthenticated(): bool {
		if(!$this->checkedToken) {
			$this->tokenPayload = $this->verifyToken();
			$this->checkedToken = true;
		}
		return $this->tokenPayload != null;
	}

	public function getTokenPayload(): ?object {
		$this->isUserAuthenticated();
		return $this->tokenPayload;
	}

	public function setUserAuthenticated(int $userId, string $loginType) {
		$expireTime = time() + (60 * 60 * 24 * 7); // 7 days
		$keyPayload = [
			'iss' => 'logigator',
			'iat' => time(),
			'exp' => $expireTime,
			'sub' => $userId,
			'login_type' => $loginType,
			'type' => 'auth'
		];
		$token = JWT::encode($keyPayload, $this->configService->getConfig('jwt_secret_key'), 'HS512');
		setcookie('auth-token', $token, $expireTime, '/', $this->configService->getConfig('root_domain'), false, true);
		setcookie('isLoggedIn', 'true', $expireTime, '/', $this->configService->getConfig('root_domain'), false, false);
	}

	public function verifyToken(): ?object {
		$token = $this->getUserToken();
		if($token == null) {
			return null;
		}

		try {
			$decoded = JWT::decode($token, $this->configService->getConfig('jwt_secret_key'), ['HS512']);
			if ($decoded->type !== 'auth') return null;
			return $decoded;
		} catch (\Exception $exception) {
			return null;
		}
	}

	public function getUserToken(): ?string {
		if (!isset($_COOKIE['auth-token']) || $_COOKIE['auth-token'] == '') {
			return null;
		}
		return $_COOKIE['auth-token'];
	}

	public function logoutUser(string $token) {
		setcookie('auth-token', '', time() - 3600, '/', $this->configService->getConfig('root_domain'), false, true);
		setcookie('isLoggedIn', '', time() - 3600, '/', $this->configService->getConfig('root_domain'), false, false);
	}

	public function getEmailVerificationToken(int $userId, string $mail): string {
		$expireTime = time() + (60 * 60); // 1h
		$keyPayload = [
			'iss' => 'logigator',
			'iat' => time(),
			'exp' => $expireTime,
			'sub' => $userId,
			'mail' => $mail,
			'type' => 'email-verify'
		];
		return JWT::encode($keyPayload, $this->configService->getConfig('jwt_secret_key'), 'HS512');
	}

	public function verifyEmailToken(string $token): ?object {
		if($token == null) {
			return null;
		}

		try {
			$decoded = JWT::decode($token, $this->configService->getConfig('jwt_secret_key'), ['HS512']);
			if ($decoded->type !== 'email-verify') return null;
			return $decoded;
		} catch (\Exception $exception) {
			return null;
		}
	}
}
