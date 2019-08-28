<?php

namespace Logigator\Service;

use Psr\Container\ContainerInterface;

abstract class BaseService
{

    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container, $config) {
        $this->container = $container;
        $this->config = $config;
    }
}