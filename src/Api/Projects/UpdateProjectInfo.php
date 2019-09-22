<?php

namespace Logigator\Api\Projects;


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

		if (!ApiHelper::checkRequiredArgs($body, ['id'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}

		$project = $this->container->get('ProjectService')->getProjectInfo($body['id'], $this->getTokenPayload()->sub);
		if(!$project)
			throw new HttpBadRequestException($request, 'Board not found.');

		$query = $this->getDbalQueryBuilder()->update('projects');

		if (isset($body['name']) && $body['name']) {
			$query = $query->set('name', ':name')->setParameter('name', $body['name']);
		}
		if (isset($body['description']) && $body['description'] !== null) {
			$query = $query->set('description', ':description')->setParameter('description', $body['description']);
		}
		if ($project['is_component'] && isset($body['symbol']) && $body['symbol'] !== null) {
			$query = $query->set('symbol', ':symbol')->setParameter('symbol', $body['symbol']);
		}

		$query->where('pk_id = :id and fk_user = :fk_user')
			->setParameter('id', $body['id'])
			->setParameter('fk_user', $this->getTokenPayload()->sub)
			->execute();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
