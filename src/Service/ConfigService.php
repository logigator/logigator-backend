<?php

namespace Logigator\Service;

use Logigator\Helpers\PathHelper;

class ConfigService
{

	private $config;

	public function __construct()
	{
		$configJson = file_get_contents(PathHelper::getPath('.', 'config.json'));
		$this->config = json_decode($configJson);
	}

	public function getConfig(string $key) {
	    return $this->config->{$key};
    }
}
