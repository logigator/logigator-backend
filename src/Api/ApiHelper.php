<?php

namespace Logigator\Api;

use Psr\Http\Message\ResponseInterface;

class ApiHelper
{
	public static function createJsonResponse(ResponseInterface $response, array $data): ResponseInterface {
		if($data === null) {
			$data = array();
		}

		$data['status'] = 200;
		$payload = json_encode($data, JSON_PRETTY_PRINT);
		$response->getBody()->write($payload);
		return $response;
	}

	public static function checkRequiredArgs($body, array $args): bool {
		foreach ($args as $arg) {
			if(!isset($body[$arg])) {
				return false;
			}
		}
		return true;
	}
}
