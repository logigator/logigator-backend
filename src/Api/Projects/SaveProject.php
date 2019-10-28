<?php

namespace Logigator\Api\Projects;

use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class SaveProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		$project = $this->getDbalQueryBuilder()
			->select('location, is_component')
			->from('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $args['id'])
			->setParameter(1, $this->getTokenPayload()->sub)
			->execute()
			->fetch();

		if(!$project)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if($project['is_component']) {
			if(!isset($body->num_inputs) || !isset($body->num_outputs))
				throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);

			$this->container->get('DbalService')->getQueryBuilder()
				->update('projects')
				->set('num_inputs', '?')
				->set('num_outputs', '?')
				->where('pk_id = ? and fk_user = ?')
				->setParameter(0, $body->num_inputs)
				->setParameter(1, $body->num_outputs)
				->setParameter(2, $args['id'])
				->setParameter(3, $this->getTokenPayload()->sub)
				->execute();
		}

		// TODO: JSON file check
		if(file_put_contents(ApiHelper::getProjectPath($this->container, $project['location']), json_encode($body->data)) === false)
			throw new \Exception();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
