<?php

namespace Logigator\Api;

use Psr\Container\ContainerInterface;

abstract class BaseController
{

    protected $container;

    private $tokenPayload;
    private $checkedToken;

    public function __construct(ContainerInterface $container) {
        $this->checkedToken = false;
        $this->container = $container;
    }

    protected function isUserAuthenticated(): bool {
        if(!$this->checkedToken) {
            $this->tokenPayload = $this->container->get('AuthenticationService')->verifyToken();
            $this->checkedToken = true;
        }
        return $this->tokenPayload != null;
    }

    protected function getTokenPayload(): ?object {
        $this->isUserAuthenticated();
        return $this->tokenPayload;
    }

    protected function getUserToken(): ?string {
        return $this->container->get('AuthenticationService')->getUserToken();
    }
}