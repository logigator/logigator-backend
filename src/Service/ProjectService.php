<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 11:07
 */

namespace Logigator\Service;

class ProjectService extends BaseService
{

	public function createProject($name, $isComponent){
		$this->container->get('DbalService')
			->insert('projects')
			->setValue('name', '?')
			->setValue('isComponent', '?')
			->setParameter(0, $name)
			->setParameter(1, $isComponent)
		;
	}

}
