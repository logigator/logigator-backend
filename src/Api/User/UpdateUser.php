<?php


namespace Logigator\Api\User;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class UpdateUser extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if(isset($body->username) && $this->container->get('UserService')->fetchUserIdPerUsername($body->username))
				throw new HttpBadRequestException($request, 'Username has already been taken.');

		if(isset($body->email) && $this->container->get('UserService')->fetchUserIdPerEmail($body->email))
			throw new HttpBadRequestException($request, 'Email has already been taken.');

		$query = $this->getDbalQueryBuilder()->update('users');

		if(isset($body->username))
			$query = $query->set('username', ':username')->setParameter('username', $body->username);

		if(isset($body->email))
			$query = $query->set('email', ':email')->setParameter('email', $body->email);

		if(isset($body->password))
			$query = $query->set('password', ':password')->setParameter('password', password_hash($body->password, PASSWORD_DEFAULT));

		$query->where('pk_id = :pk_id')->setParameter('pk_id', $this->getTokenPayload()->sub)->execute();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
