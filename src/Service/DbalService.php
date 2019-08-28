<?php

namespace Logigator\Service;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Container\ContainerInterface;

class DbalService extends BaseService
{

    private $dbConnection;

    public function __construct(ContainerInterface $container, $config) {
        parent::__construct($container, $config);

        $dbalConfig = new Configuration();
        $this->dbConnection = DriverManager::getConnection($config['doctrine-dbal'], $dbalConfig);
    }

    public function getConnection(): Connection {
        return $this->dbConnection;
    }

    public function getQueryBuilder(): QueryBuilder {
        return $this->dbConnection->createQueryBuilder();
    }
}