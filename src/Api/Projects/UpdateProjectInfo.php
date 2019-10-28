<?php

namespace Logigator\Api\Projects;


use Doctrine\DBAL\DBALException;
use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class UpdateProjectInfo extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		$project = $this->container->get('ProjectService')->getProjectInfo($args['id'], $this->getTokenPayload()->sub);
		if(!$project)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		$query = $this->getDbalQueryBuilder()->update('projects');

		if (isset($body->name))
			$query = $query->set('name', ':name')->setParameter('name', $body->name);

		if (isset($body->description))
			$query = $query->set('description', ':description')->setParameter('description', $body->description);

		if ($project['is_component'] && isset($body->symbol))
			$query = $query->set('symbol', ':symbol')->setParameter('symbol', $body->symbol);

		$query->where('pk_id = :id and fk_user = :fk_user')
			->setParameter('id', $args['id'])
			->setParameter('fk_user', $this->getTokenPayload()->sub)
			->execute();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
