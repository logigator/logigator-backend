<?php


namespace Logigator\Api\User;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;

class UpdatePassword extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		$this->container->get('DbalService')->getQueryBuilder()
			->update('users')
			->set('password', ':password')
			->where('pk_id = :id')
			->setParameter('id', $this->getTokenPayload()->sub)
			->setParameter('password', password_hash($body->password, PASSWORD_DEFAULT))
			->execute();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
