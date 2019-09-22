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

		if (!ApiHelper::checkRequiredArgs($body, ['isComponent', 'name'])) {
			throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);
		}

		$body['isComponent'] = filter_var($body['isComponent'], FILTER_VALIDATE_BOOLEAN);

		if($body['isComponent'] && !ApiHelper::checkRequiredArgs($body, ['symbol']))
			throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);

		$description = !isset($body['description']) ? '' : $body['description'];

		$location = Uuid::uuid4()->toString();

		$query = $this->getDbalQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('description', '?')
			->setParameter(0, $body['name'])
			->setParameter(1, $body['isComponent'])
			->setParameter(2, $this->getTokenPayload()->sub)
			->setParameter(3, $location)
			->setParameter(4, $description);

		if($body['isComponent'])
			$query = $query->setValue('symbol', '?')->setParameter(5, $body['symbol']);

		$query->execute();
		return ApiHelper::createJsonResponse($response, ['id' => $this->getDbalConnection()->lastInsertId()]);
	}
}
