<?php

function createServices(Slim\App $app, $config) {
	$app->getContainer()['AuthenticationService'] = function ($c) use ($config) {
		return new \Logigator\Service\AuthenticationService($c, $config);
	};
	$app->getContainer()['DbalService'] = function ($c) use ($config) {
		return new \Logigator\Service\DbalService($c, $config);
	};
	$app->getContainer()['ProjectService'] = function ($c) use ($config) {
		return new \Logigator\Service\ProjectService($c, $config);
  };
	$app->getContainer()['SmtpService'] = function ($c) use ($config) {
		return new \Logigator\Service\SmtpService($c, $config);
	};
	$app->getContainer()['UserService'] = function ($c) use ($config) {
		return new \Logigator\Service\UserService($c, $config);
	};
}
