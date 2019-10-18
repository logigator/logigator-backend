<?php

namespace Logigator\Service;

use Logigator\Api\ApiHelper;
use Ramsey\Uuid\Uuid;

class ProjectService extends BaseService
{
	public function fetchLocation(int $projectId, int $userId)
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

	public function deleteProject(int $projectId, int $userId): bool
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->delete('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $projectId)
			->setParameter(1, $userId)
			->execute();
	}

	public function getAllProjectsInfo(int $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, last_edited, created_on')
			->from('projects')
			->where('fk_user = ? and is_component = false')
			->setParameter(0, $userId)
			->execute()
			->fetchAll();
	}

	public function getProjectInfo(int $projectId, int $userId = null)
	{
		$query = $this->container->get('DbalService')->getQueryBuilder()
			->select('*')
			->from('projects');

		if ($userId === null)
			$query = $query
				->where('pk_id = ?')
				->setParameter(0, $projectId);
		else
			$query = $query
				->where('pk_id = ? and fk_user = ?')
				->setParameter(0, $projectId)
				->setParameter(1, $userId);

		return $query->execute()->fetch();
	}

	public function getAllComponentsInfo(int $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, symbol, last_edited, created_on, num_inputs, num_outputs')
			->from('projects')
			->where('fk_user = ? and is_component = true')
			->setParameter(0, $userId)
			->execute()
			->fetchAll();
	}

	public function fetchProjectId($location, $userId): int
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id')
			->from('projects')
			->where('location = ? and fk_user = ?')
			->setParameter(0, $location)
			->setParameter(1, $userId)
			->execute()
			->fetch()["pk_id"];
	}

	public function fetchShare(string $address, int $userId, $anonymous = false) {
		$share = $this->container->get('DbalService')->getQueryBuilder()
			->select('link.address as "link.address", 
                link.is_public as "link.is_public",
                link.pk_id as "link.pk_id",
                project.pk_id as "project.id",
                project.name as "project.name",
                project.description as "project.description",
                project.symbol as "project.symbol", 
                project.last_edited as "project.last_edited",
                project.created_on as "project.created_on", 
                project.is_component as "project.is_component", 
                project.location as "project.location", 
                user.username as "user.username",
                user.profile_image as "user.profile_image"')
			->from('links', 'link')
			->join('link', 'projects', 'project', 'link.fk_project = project.pk_id')
			->join('project', 'users', 'user', 'user.pk_id = project.fk_user')
			->where('link.address = ?')
			->setParameter(0, $address)
			->execute()
			->fetch();

		if(!$share)
			return false;

		if($share['link.is_public'])
			return $share;

		if($anonymous)
			return false;

		if(!$this->container->get('DbalService')->getQueryBuilder()
			->select('fk_user, fk_link')
			->from('link_permits')
			->where('fk_user = ? and fk_link = ?')
			->setParameter(0, $userId)
			->setParameter(1, $share['link.pk_id'])
			->execute()
			->fetch())
			return false;

		return $share;
	}
}
