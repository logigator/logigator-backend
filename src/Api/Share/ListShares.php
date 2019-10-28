<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class ListShares extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$shares = $this->getDbalQueryBuilder()
			->select('projects.pk_id as project_id',
				'links.address', 'links.is_public',
				'users.pk_id as users',
				'users.username as "users.username"',
				'users.email as "users.email"')
			->from('projects', 'projects')
			->join('projects', 'links', 'links', 'links.fk_project = projects.pk_id')
			->leftJoin('links', 'link_permits', 'permits', 'permits.fk_link = links.pk_id')
			->leftJoin('permits', 'users', 'users', 'users.pk_id = permits.fk_user')
			->where('projects.fk_user = :user')
			->setParameter('user', $this->getTokenPayload()->sub)
			->execute()
			->fetchAll();

		$shares_grouped = [];
		for ($i = 0; $i < count($shares); $i++) {
			$shares_grouped[] = $shares[$i];
			$share = &$shares_grouped[count($shares_grouped) - 1];
			if (!$shares[$i]['is_public']) {
				$share['users'] = [];

				for (; $i < count($shares); $i++) {
					if($shares[$i]['address'] !== $share['address']) {
						$i--;
						break;
					}
					$share['users'][] = [
						'email' => $shares[$i]['users.email'],
						'username' => $shares[$i]['users.username']
					];
				}
			} else {
				$share['users'] = [];
			}
			unset($share['users.username']);
			unset($share['users.email']);
		}

		return ApiHelper::createJsonResponse($response, $shares_grouped);
	}
}
