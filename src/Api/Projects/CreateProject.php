<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;

class CreateProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if($body->isComponent && (!isset($body->symbol)))
			throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);

		$description = !isset($body->description) ? '' : $body->description;

		$location = Uuid::uuid4()->toString();

		$query = $this->getDbalQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('description', '?')
			->setParameter(0, $body->name, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(1, $body->isComponent, \Doctrine\DBAL\ParameterType::BOOLEAN)
			->setParameter(2, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(3, $location, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(4, $description, \Doctrine\DBAL\ParameterType::STRING);

		if($body->isComponent) {
			$query = $query->setValue('symbol', '?')->setParameter(5, $body->symbol, \Doctrine\DBAL\ParameterType::STRING);
			$query = $query->setValue('num_inputs', '?')->setParameter(6, 0, \Doctrine\DBAL\ParameterType::INTEGER);
			$query = $query->setValue('num_outputs', '?')->setParameter(7, 0, \Doctrine\DBAL\ParameterType::INTEGER);
			$query = $query->setValue('labels', '?')->setParameter(8, '', \Doctrine\DBAL\ParameterType::STRING);
		}

		$query->execute();
		return ApiHelper::createJsonResponse($response, ['id' => $this->getDbalConnection()->lastInsertId(), 'version' => 0]);
	}
}
