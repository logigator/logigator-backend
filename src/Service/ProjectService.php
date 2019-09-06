<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 11:07
 */

namespace Logigator\Service;

use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;

class ProjectService extends BaseService
{
	public function createProject(string $name, bool $isComponent, int $fk_user, string $description, string $symbol): string
	{
		$location = Uuid::uuid1()->toString();

		$this->container->get('DbalService')->getQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('description', '?')
			->setValue('symbol', '?')
			->setParameter(0, $name)
			->setParameter(1, $isComponent)
			->setParameter(2, $fk_user)
			->setParameter(3, $location)
			->setParameter(4, $description)
			->setParameter(5, $symbol)
			->execute();

		return $location;
	}

	public function fetchLocation(int $projectId, int $userId): string
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('location')
			->from('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $projectId)
			->setParameter(1, $userId)
			->execute()
			->fetch()["location"];
	}

	public function deleteProject(int $projectId, int $userId): void
	{
		$this->container->get('DbalService')->getQueryBuilder()
			->delete('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $projectId)
			->setParameter(1, $userId)
			->execute();
	}

	public function getAllProjectsInfo(int $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, symbol, last_edited, created_on')
			->from('projects')
			->where('fk_user = ? and is_component = false')
			->setParameter(0, $userId)
			->execute()
			->fetchAll();
	}

	public function getAllComponentsInfo(int $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, symbol, last_edited, created_on')
			->from('projects')
			->where('fk_user = ? and is_component = true')
			->setParameter(0, $userId)
			->execute()
			->fetchAll();
	}

}
