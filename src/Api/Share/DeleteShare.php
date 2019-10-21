<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class DeleteShare extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$id = $this->getDbalQueryBuilder()
			->select('links.pk_id')
			->from('links')
			->join('links', 'projects', 'projects', 'links.fk_project = projects.pk_id')
			->where('links.address = :address and projects.fk_user = :user')
			->setParameter('address', $args['address'])
			->setParameter('user', $this->getTokenPayload()->sub)
			->execute()
			->fetch()['pk_id'];

		if(!$id)
			throw new HttpBadRequestException($request, $this::ERROR_RESOURCE_NOT_FOUND);

		$this->getDbalQueryBuilder()
			->delete('links')
			->where('links.pk_id = :id')
			->setParameter('id', $id)
			->execute();

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
