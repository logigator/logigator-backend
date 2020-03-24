<?php


namespace Logigator\Helpers;


use Logigator\Service\ConfigService;

class PathHelper
{

	public static function getProjectPath(ConfigService $config, string $filename): string {
		return self::getPath($config->getConfig('project_path'), $filename);
	}

	public static function getProjectPreviewPath(ConfigService $config, string $filename): string {
		return self::getPath($config->getConfig('project_preview_path'), $filename);
	}

	public static function getProfileImagePath(ConfigService $config, string $filename): string {
		return self::getPath($config->getConfig('profile_image_path'), $filename);
	}

	public static function getPath(string $path, string $filename): string {
		$absolute = false;

		// Optional wrapper(s).
		$regExp = '%^(?<wrappers>(?:[[:print:]]{2,}://)*)';
		// Optional root prefix.
		$regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';
		// Actual path.
		$regExp .= '(?<path>(?:[[:print:]]*))$%';
		$parts = [];
		if (!preg_match($regExp, $path, $parts)) {
			throw new \DomainException('Path configured in config is invalid: '. $path);
		}
		if ('' !== $parts['root']) {
			$absolute = true;
		}

		$last = substr($path, strlen($path) - 1, 1);
		if($last !== '/' && $last !== '\\')
			$path .= '/';

		if($absolute)
			return $path . $filename;
		else
			return ($_SERVER['DOCUMENT_ROOT'] . '/' . $path . $filename);
	}

}
