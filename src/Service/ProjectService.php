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
	private const DEFAULT_IMAGE_LOCATION = "";

	// TODO: set a default image location

	public function createProject($name, $isComponent, $fk_user)
	{
		$location = $this->generateLocation($name);
		$this->container->get('DbalService')
			->insert('projects')
			->setValue('name', '?')
			->setValue('isComponent', '?')
			->setValue('fk_user', '?')
			->setValue('location', '?')
			->setValue('preview_image', '?')
			->setParameter(0, $name)
			->setParameter(1, $isComponent)
			->setParameter(2, $fk_user)
			->setParameter(3, $location)
			->setParameter(4, self::DEFAULT_IMAGE_LOCATION);
		;
	}

	private function generateLocation($name){
		try {
			$uuid1 = Uuid::uuid1();
			return $name.$uuid1->toString();
		} catch (UnsatisfiedDependencyException $e) {
			echo 'Caught exception: ' . $e->getMessage() . "\n";
		}
		return "errorFile";
	}

}
