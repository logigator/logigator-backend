<?php

namespace Logigator\Api;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ApiHelper
{
    public const JSON_BOOL = 10;
    public const JSON_NUMBER = 11;
    public const JSON_STRING = 12;
    public const JSON_ARRAY = 13;
    public const JSON_OBJECT = 14;
    public const JSON_NULL = 15;

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

		$payload = json_encode($obj, JSON_PRETTY_PRINT);
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

    public static function getProjectPath(ContainerInterface $container, string $filename): string {
        return self::getPath($container->get('ConfigService')->getConfig('project_path'), $filename);
    }

    public static function getProjectPreviewPath(ContainerInterface $container, string $filename): string {
        return self::getPath($container->get('ConfigService')->getConfig('project_preview_path'), $filename);
    }

    public static function getProfileImagePath(ContainerInterface $container, string $filename): string {
        return self::getPath($container->get('ConfigService')->getConfig('profile_image_path'), $filename);
    }

    public static function generateRandomString(int $length = 8, string $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
        $randomString = '';
        $charactersLength = strlen($charset);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $charset[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getPath(string $config_path, string $filename): string {
        $absolute = false;

        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:print:]]{2,}://)*)';
        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';
        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';
        $parts = [];
        if (!preg_match($regExp, $config_path, $parts)) {
            $mess = sprintf('Path configured in config is invalid.', $config_path);
            throw new \DomainException($mess);
        }
        if ('' !== $parts['root']) {
            $absolute = true;
        }

        $last = substr($config_path, strlen($config_path) - 1, 1);
        if($last !== '/' && $last !== '\\')
            $config_path .= '/';

        if($absolute)
            return $config_path . $filename;
        else
            return $_SERVER['DOCUMENT_ROOT'] . '/' . $config_path . $filename;
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
