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
			->setParameter(0, $args['id'], \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(1, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch();

		if(!$project)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if ($project['is_component']) {
			if(!isset($body->num_inputs) || !isset($body->num_outputs) || !isset($body->labels))
				throw new HttpBadRequestException($request, self::ERROR_MISSING_ARGUMENTS);

			$this->getDbalQueryBuilder()
				->update('projects')
				->set('num_inputs', '?')
				->set('num_outputs', '?')
				->set('labels', '?')
				->where('pk_id = ? and fk_user = ?')
				->setParameter(0, $body->num_inputs, \Doctrine\DBAL\ParameterType::INTEGER)
				->setParameter(1, $body->num_outputs, \Doctrine\DBAL\ParameterType::INTEGER)
				->setParameter(2, implode(';', $body->labels), \Doctrine\DBAL\ParameterType::STRING)
				->setParameter(3, $args['id'], \Doctrine\DBAL\ParameterType::INTEGER)
				->setParameter(4, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
				->execute();
		}

		if (file_put_contents(ApiHelper::getProjectPath($this->container, $project['location']), json_encode($body->data)) === false)
			throw new \Exception();

		$this->getDbalQueryBuilder()
			->update('projects')
			->set('last_edited', 'now()')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $args['id'], \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(1, (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute();

		$preview = $this->container->get('ImageService')->generateProjectImage(ApiHelper::getProjectPath($this->container, $project['location']), 512, 512);
		if ($preview)
			imagepng($preview, ApiHelper::getProjectPreviewPath($this->container, $project['location']), 9);

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
