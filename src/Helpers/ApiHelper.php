<?php

namespace Logigator\Helpers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ApiHelper
{
	public static function createJsonResponse(ResponseInterface $response, array $data, bool $resolveSqlNames = false, array $warnings = null): ResponseInterface {
		if($data === null) {
			$data = array();
		}

        $obj = array();
        $obj['status'] = 200;

		if($resolveSqlNames) {
		    $obj['result'] = self::resolveSqlObject($data);
        } else {
            $obj['result'] = $data;
        }

		if($warnings !== null)
		    $obj['warnings'] = $warnings;

		$payload = json_encode($obj);
		$response->getBody()->write($payload);
		return $response;
	}

	public static function resolveSqlObject(array $input) : array {
	    $obj = array();
        foreach ($input as $key => $value) {
            $pos = strpos($key, '.');
            if($pos !== false) {
                $first = substr($key, 0, $pos);
                $last = substr($key, $pos + 1, strlen($key) - $pos);

                if(is_array($value))
                    $obj[$first][$last] = self::resolveSqlObject($value);
                else
                    $obj[$first][$last] = $value;
            } else {
                if(is_array($value))
                    $obj[$key] = self::resolveSqlObject($value);
                else
                    $obj[$key] = $value;
            }
        }
        return $obj;
    }

    public static function removeSpecialCharacters(string $string): string {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-\_]/', '', $string); // Removes special chars.
    }

	public static function generateRandomString(int $length = 8, string $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
		$randomString = '';
		$charactersLength = strlen($charset);
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $charset[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
