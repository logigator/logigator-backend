<?php

namespace Logigator\Api;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Container\ContainerInterface;

abstract class BaseController
{
	protected $container;

	private $tokenPayload;
	private $checkedToken;

	protected const ERROR_RESOURCE_NOT_FOUND = 'Resource not found.';
	protected const ERROR_MISSING_ARGUMENTS = 'Not all required args were given.';

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

	protected function getDbalQueryBuilder(): QueryBuilder {
		return $this->container->get('DbalService')->getQueryBuilder();
	}

	protected function getDbalConnection(): Connection {
		return $this->container->get('DbalService')->getConnection();
	}
}
