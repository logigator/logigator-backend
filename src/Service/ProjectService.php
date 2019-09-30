<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 11:07
 */

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

	public function cloneProject($projectId, $userIdOrigin, $userId, $counter): int
	{
		if ($counter >= 64)
			return -1;

		$newLocation = $this->copyData($projectId, $userIdOrigin, $userId);
		$newProjectId = $this->fetchProjectId($newLocation, $userId);

		if (file_exists(ApiHelper::getProjectPath($this->container, $newLocation))) {
			$jsonString = file_get_contents(ApiHelper::getProjectPath($this->container, $newLocation));
			$data = json_decode($jsonString, true);

			foreach ($data['mapping'] as $key => $value) {
				$key[$value] = $this->cloneProject($value, $userIdOrigin, $userId, $counter + 1);
			}
		}

		return $newProjectId;
	}

	public function copyData($projectId, $userIdOrigin, $userId): String
	{
		$location = Uuid::uuid4()->toString();

		$projectData = $this->getProjectInfo($projectId, $userIdOrigin);
		$this->container->get('DbalService')->getQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('description', '?')
			->setValue('symbol', '?')
			->setValue('fk_originates_from', '?')
			->setParameter(0, $projectData['name'] . "_Copy")
			->setParameter(1, $projectData['is_component'])
			->setParameter(2, $userId)
			->setParameter(3, $location)
			->setParameter(4, $projectData['description'])
			->setParameter(5, $projectData['symbol'])
			->setParameter(6, $projectId)
			->execute();

		$path = ApiHelper::getProjectPath($this->container, $projectData['location']);

		if (file_exists($path))
			file_put_contents(ApiHelper::getProjectPath($this->container, $location), file_get_contents($path));

		$path = ApiHelper::getProjectPreviewPath($this->container, $projectData['location']);

		if (file_exists($path))
			file_put_contents(ApiHelper::getProjectPreviewPath($this->container, $location), file_get_contents($path));

		return $location;
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
