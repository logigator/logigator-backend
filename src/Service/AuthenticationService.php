<?php

namespace Logigator\Service;


use Firebase\JWT\JWT;

class AuthenticationService extends BaseService
{
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
		$token = JWT::encode($keyPayload, JWT_SECRET_KEY, 'HS512');
		setcookie('auth-token', $token, $expireTime, '/', ROOT_DOMAIN, false, true);
		setcookie('isLoggedIn', 'true', $expireTime, '/', ROOT_DOMAIN, false, false);
	}

	public function verifyToken(): ?object {
		$token = $this->getUserToken();
		if($token == null) {
			return null;
		}

		try {
			$decoded = JWT::decode($token, JWT_SECRET_KEY, ['HS512']);
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
		setcookie('auth-token', '', time() - 3600, '/', ROOT_DOMAIN, false, true);
		setcookie('isLoggedIn', '', time() - 3600, '/', ROOT_DOMAIN, false, false);
	}

	public function getEmailVerificationToken(int $userId): string {
		$expireTime = time() + (60 * 60); // 1h
		$keyPayload = [
			'iss' => 'logigator',
			'iat' => time(),
			'exp' => $expireTime,
			'sub' => $userId,
			'type' => 'email-verify'
		];
		return JWT::encode($keyPayload, JWT_SECRET_KEY, 'HS512');
	}

	public function verifyEmailToken(string $token): ?object {
		if($token == null) {
			return null;
		}

		try {
			$decoded = JWT::decode($token, JWT_SECRET_KEY, ['HS512']);
			if ($decoded->type !== 'email-verify') return null;
			return $decoded;
		} catch (\Exception $exception) {
			return null;
		}
	}
}
