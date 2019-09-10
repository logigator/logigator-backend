<?php

namespace Logigator\Service;


use Firebase\JWT\JWT;

class ConfigService extends BaseService
{
	public function getConfig(string $key) {
	    return $this->config[$key];
    }
}
