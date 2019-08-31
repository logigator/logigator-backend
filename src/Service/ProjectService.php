<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 11:07
 */

namespace Logigator\Service;


class ProjectService extends DbalService
{

	private $dbConnection;

	public function __construct(ContainerInterface $container, $config) {
		parent::__construct($container, $config);

		$this->dbConnection = parent::getConnection();
	}

	public function createProject($name, $isComponent){
		parent::getQueryBuilder()
			->insert('projects')
			->setValue('name', '?')
			->setValue('isComponent', '?')
			->setParameter(0, $name)
			->setParameter(1, $isComponent)
		;
	}

}
