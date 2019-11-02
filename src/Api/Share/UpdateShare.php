<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class UpdateShare extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		$share = $this->getDbalQueryBuilder()
			->select('links.pk_id as pk_id, projects.pk_id as "projects.pk_id", projects.*, links.*')
			->from('links')
			->join('links', 'projects', 'projects', 'links.fk_project = projects.pk_id')
			->where('projects.fk_user = :user and links.address = :link')
			->setParameter('user', (int)$this->getTokenPayload()->sub)
			->setParameter('link', $args['address'])
			->execute()
			->fetch();

		if (!$share)
			throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if (isset($body->is_public)) {
			$this->getDbalQueryBuilder()
				->update('links')
				->where('pk_id = :id')
				->set('is_public', ':public')
				->setParameter('public', $body->is_public)
				->setParameter('id', $share['pk_id'])
				->execute();
		}

		$warnings = [];
		if (isset($body->users)) {
			$project = $this->container->get('ProjectService')->getProjectInfo($share['fk_project'], (int)$this->getTokenPayload()->sub);
			$user = $this->container->get('UserService')->fetchUser((int)$this->getTokenPayload()->sub);

			$this->getDbalQueryBuilder()
				->delete('link_permits')
				->where('fk_link = :link')
				->setParameter('link', $share['pk_id'])
				->execute();

			$warnings = $this->container->get('ProjectService')->setSharePermits($share['pk_id'],
				$body->users,
				isset($body->invitations) ? $body->invitations : false,
				$user['username'],
				$project['name'],
				$args['address']
			);
		}

		return ApiHelper::createJsonResponse($response, [ 'success' => true ], false, $warnings);
	}
}
