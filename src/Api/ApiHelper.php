<?php

namespace Logigator\Api;

use Psr\Http\Message\ResponseInterface;

class ApiHelper
{
    public const JSON_BOOL = 10;
    public const JSON_NUMBER = 11;
    public const JSON_STRING = 12;
    public const JSON_ARRAY = 13;
    public const JSON_OBJECT = 14;
    public const JSON_NULL = 15;

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
		    if(is_array($arg)) {
		        if(!isset($arg['key'])) {
		            return false;
                }

                if (!isset($body[$arg['key']])) {
                    return false;
                }

                if(isset($arg['type'])) {
                    switch ($arg['type']) {
                        case self::JSON_BOOL:
                            if(!is_bool($body[$arg['key']])) return false;
                            break;
                        case self::JSON_NUMBER:
                            if(!is_numeric($body[$arg['key']])) return false;
                            break;
                        case self::JSON_STRING:
                            if(!is_string($body[$arg['key']])) return false;
                            break;
                        case self::JSON_ARRAY:
                            if(!is_array($body[$arg['key']])) return false;
                            break;
                        case self::JSON_OBJECT:
                            if(!is_array($body[$arg['key']])) return false;
                            break;
                        case self::JSON_NULL:
                            if($body[$arg['key']] !== null) return false;
                            break;
                    }
                }
            } else {
                if (!isset($body[$arg])) {
                    return false;
                }
            }
		}
		return true;
	}

	public static function checkArgumentFormat(string $regex, string $input): bool {
        return preg_match($regex, $input) !== false;
    }
}
