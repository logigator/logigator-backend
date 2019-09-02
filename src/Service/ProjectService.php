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

	public function createProject($name, $isComponent, $fk_user)
	{
		$location = $this->generateLocation();
		$this->container->get('DbalService')->getQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('is_component', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('preview_image', '?')
			->setParameter(0, $name)
			->setParameter(1, $isComponent)
			->setParameter(2, $fk_user)
			->setParameter(3, $location)
			->setParameter(4, self::DEFAULT_PREVIEW_IMAGE)
			->execute()
		;
	}

	private function generateLocation(){
		try {
			$uuid1 = Uuid::uuid1();
			return $uuid1->toString();
			//TODO edit to real path
		} catch (UnsatisfiedDependencyException $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
			return "errorFile";
		}
	}

	public function openProject($id){
		$location = $this->container->get('DbalService')->getQueryBuilder()
			->select('location')
			->from('projects')
			->where('pk_id = ?')
			->setParamter(0,$id)
			->getQuery()
			->getResults()
		;

		return file_get_contents($location);
	}

}
