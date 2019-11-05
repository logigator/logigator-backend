<?php

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class DeleteProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$project = $this->getDbalQueryBuilder()
			->select('*')
			->from('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $args['id'], \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(1, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch();

		if(!$project)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if(file_exists(ApiHelper::getProjectPath($this->container, $project['location'])))
			unlink(ApiHelper::getProjectPath($this->container, $project['location']));

		$this->getDbalQueryBuilder()
			->delete('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $project['pk_id'], \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(1, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
