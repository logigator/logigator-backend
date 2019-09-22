<?php

namespace Logigator\Api\Projects;


use Doctrine\DBAL\DBALException;
use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;

class CreateProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['isComponent', 'name'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$body['isComponent'] = filter_var($body['isComponent'], FILTER_VALIDATE_BOOLEAN);

		if($body['isComponent'] && !ApiHelper::checkRequiredArgs($body, ['symbol']))
			throw new HttpBadRequestException($request, 'Not all required args were given');

		$description = !isset($body['description']) ? '' : $body['description'];

		$location = Uuid::uuid4()->toString();

		try {
			$this->getDbalQueryBuilder()
				->insert('projects')
				->setValue('name', '?')
				->setValue('is_component', '?')
				->setValue('fk_user', '?')
				->setValue('location', '?')
				->setValue('description', '?')
				->setValue('symbol', '?')
				->setParameter(0, $body['name'])
				->setParameter(1, $body['isComponent'])
				->setParameter(2, $this->getTokenPayload()->sub)
				->setParameter(3, $location)
				->setParameter(4, $description)
				->setParameter(5, $body['symbol'])
				->execute();
		} catch (DBALException $e) {
			throw new HttpInternalServerErrorException($request);
		}

		return ApiHelper::createJsonResponse($response, ['id' => $this->getDbalConnection()->lastInsertId()]);
	}
}
