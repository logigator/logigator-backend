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
        setcookie('auth-token', $token, $expireTime, '/', '', false, true);
        setcookie('isLoggedIn', 'true', $expireTime, '/', '', false, false);
        //TODO: check for expired tokens in db and delete them
    }

    public function verifyToken(): ?object {
        if (!isset($_COOKIE['auth-token']) || $_COOKIE['auth-token'] == '') {
            return null;
        }
        $token = $_COOKIE['auth-token'];

        //TODO:  check if token is in database

        try {
            return JWT::decode($token, JWT_SECRET_KEY, ['HS512']);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function logoutUser(string $userId, string $loginType, string $token) {
        setcookie('auth-token', '', time() - 3600);
        setcookie('isLoggedIn', 'true', time() - 3600);
        //TODO: remove current token from database
    }
}