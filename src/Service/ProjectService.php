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
	private const DEFAULT_PREVIEW_IMAGE = "";

	// TODO: set a default image location

	public function createProject($name, $isComponent, $fk_user, $description, $symbol)
	{
		$location = $this->generateLocation();
		$this->container->get('DbalService')->getQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('preview_image', '?')
			->setValue('description', '?')
			->setValue('symbol', '?')
			->setParameter(0, $name)
			->setParameter(1, $isComponent)
			->setParameter(2, $fk_user)
			->setParameter(3, $location)
			// TODO: remove random
			->setParameter(4, self::DEFAULT_PREVIEW_IMAGE . random_int(0, 1000))
			->setParameter(5, $description)
			->setParameter(6, $symbol)
			->execute();
	}

	private function generateLocation()
	{
		try {
			$uuid1 = Uuid::uuid1();
			return $uuid1->toString();
			//TODO edit to real path
		} catch (UnsatisfiedDependencyException $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
			return "errorFile";
		}
	}

	public function fetchLocation($projectId, $userId)
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

	public function deleteProject($projectId, $userId)
	{
		$this->container->get('DbalService')->getQueryBuilder()
			->delete('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $projectId)
			->setParameter(1, $userId)
			->execute();
	}

	public function getAllProjectsInfo($userId)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, symbol, last_edited, created_on')
			->from('projects')
			->where('fk_user = ?')
			->setParameter(0, $userId)
			->execute()
			->fetchAll();
	}

	public function getAllComponentsInfo($userId)
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
