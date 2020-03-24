<?php

namespace Logigator\Service;


use DI\Annotation\Inject;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;

class DbalService
{

	private $dbConnection;

	/**
	 * @Inject
	 * @param ConfigService $configService
	 */
	public function __construct(ConfigService $configService) {

		$dbalConfig = new Configuration();
		$this->dbConnection = DriverManager::getConnection($configService->getConfig('doctrine-dbal'), $dbalConfig);
	}

	public function getConnection(): Connection {
		return $this->dbConnection;
	}

	public function getQueryBuilder(): QueryBuilder {
		return $this->dbConnection->createQueryBuilder();
	}
}
