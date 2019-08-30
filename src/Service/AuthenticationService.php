<?php

namespace Logigator\Service;


use Firebase\JWT\JWT;

class AuthenticationService extends BaseService
{
	public function setUserAuthenticated(string $userId, string $loginType) {
		$expireTime = time() + (60 * 60 * 24 * 7);
		$keyPayload = [
			'iss' => 'logigator',
			'iat' => time(),
			'exp' => $expireTime,
			'sub' => $userId,
			'login_type' => $loginType
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
			return JWT::decode($token, JWT_SECRET_KEY, ['HS512']);
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
}
