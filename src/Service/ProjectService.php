<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 11:07
 */

namespace Logigator\Service;

use Ramsey\Uuid\Uuid;

class ProjectService extends BaseService
{
	public function createProject(string $name, bool $isComponent, int $fk_user, string $description, string $symbol, int $fk_originates_from = null): string
	{
		$location = Uuid::uuid4()->toString();

		$query = $this->container->get('DbalService')->getQueryBuilder()
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
			->setParameter(5, $symbol);

		if (!is_null($fk_originates_from))
			$query = $query->setValue('fk_originates_from', '?')
				->setParameter(6, $fk_originates_from);

		$query->execute();

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

	public function getProjectInfo(int $projectId, int $userId = null): array
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
			->select('pk_id, name, description, symbol, last_edited, created_on')
			->from('projects')
			->where('fk_user = ? and is_component = true')
			->setParameter(0, $userId)
			->execute()
			->fetchAll();
	}

	public function cloneProject($projectId, $userIdOrigin, $userId, $counter): int
	{
		if ($counter >= 128) {
			// TODO: stop and throw error
			return 0;
		}
		$newLocation = $this->copyData($projectId, $userIdOrigin, $userId);
		$newProjectId = $this->fetchProjectId($newLocation, $userId);
		$jsonString = file_get_contents($newLocation);
		$data = json_decode($jsonString, true);

		foreach ($data['mapping'] as $key => $value) {
			$key[$value] = $this->cloneProject($value, $userIdOrigin, $userId, $counter++);
		}

		return $newProjectId;
	}

	public function copyData($projectId, $userIdOrigin, $userId): String
	{
		$location = Uuid::uuid4()->toString();
		$projectData = $this->fetchProjectData($projectId, $userIdOrigin);
		$this->container->get('DbalService')->getQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('preview_image', '?')
			->setValue('description', '?')
			->setValue('symbol', '?')
			->setValue('originates_from', '?')
			->setParameter(0, $projectData['name'] . "_Copy")
			->setParameter(1, $projectData['isComponent'])
			->setParameter(2, $userId)
			->setParameter(3, $location)
			->setParameter(4, $projectData['preview_image'])
			->setParameter(5, $projectData['description'])
			->setParameter(6, $projectData['symbol'])
			->setParameter(7, $projectData['fk_user'])
			->execute();
		$jsonString = file_get_contents($projectData['location']);
		file_put_contents($location, $jsonString);
		return $location;
	}

	public function fetchProjectData($projectId, $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('*')
			->from('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $projectId)
			->setParameter(1, $userId)
			->getQuery()
			->getResults();
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


}
