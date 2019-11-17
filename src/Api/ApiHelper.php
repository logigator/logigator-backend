<?php

namespace Logigator\Api;

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

    public static function getProjectPath(ContainerInterface $container, string $filename): string {
        return self::getPath($container->get('ConfigService')->getConfig('project_path'), $filename);
    }

    public static function getProjectPreviewPath(ContainerInterface $container, string $filename): string {
        return self::getPath($container->get('ConfigService')->getConfig('project_preview_path'), $filename);
    }

    public static function getProfileImagePath(ContainerInterface $container, string $filename): string {
        return self::getPath($container->get('ConfigService')->getConfig('profile_image_path'), $filename);
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
            throw new \DomainException('Path configured in config is invalid: '. $config_path);
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

	public static function generateRandomString(int $length = 8, string $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
		$randomString = '';
		$charactersLength = strlen($charset);
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $charset[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}
