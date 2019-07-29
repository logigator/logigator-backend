<?php

namespace Logigator\Service;

use Psr\Container\ContainerInterface;

abstract class BaseService
{

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
}