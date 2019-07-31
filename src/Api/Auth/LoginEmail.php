<?php

namespace Logigator\Api\Auth;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class LoginEmail extends BaseController
{
    public function __invoke(ServerRequestInterface $request, Response $response, array $args) {
        $body = $request->getParsedBody();

        if(!ApiHelper::checkRequiredArgs($body, ['email', 'password'])) {
            return ApiHelper::createJsonResponse($response, null, 400, 'Not all required args were given');
        }

        // TODO: check if user exists and the password is correct
        $userExists = true;
        $userId = 0;
        $passwordCorrect = true;

        if (!$userExists) {
            return ApiHelper::createJsonResponse($response, null, 404, 'no such user');
        }
        if(!$passwordCorrect) {
            return ApiHelper::createJsonResponse($response, null, 401, 'password is incorrect');
        }


        $this->container->get('AuthenticationService')->setUserAuthenticated($userId, 'email');
        return ApiHelper::createJsonResponse($response, ['loggedIn' => 'true']);
    }
}