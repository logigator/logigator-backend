<?php
function createServices($app, $config) {
	$app->getContainer()['AuthenticationService'] = function ($c) use ($config) {
		return new \Logigator\Service\AuthenticationService($c, $config);
	};
	$app->getContainer()['DbalService'] = function ($c) use ($config) {
		return new \Logigator\Service\DbalService($c, $config);
	};
}
