<?php

function createServices(\DI\Container $container, array $config) {
	$container->set('AuthenticationService', function ($c) use ($config) {
		return new \Logigator\Service\AuthenticationService($c, $config);
	});

	$container->set('DbalService', function ($c) use ($config) {
		return new \Logigator\Service\DbalService($c, $config);
	});

	$container->set('ProjectService', function ($c) use ($config) {
		return new \Logigator\Service\ProjectService($c, $config);
	});

	$container->set('SmtpService', function ($c) use ($config) {
		return new \Logigator\Service\SmtpService($c, $config);
	});

	$container->set('UserService', function ($c) use ($config) {
		return new \Logigator\Service\UserService($c, $config);
	});
}
