<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

$container = new \Slim\Container($config);
$container['notFoundHandler'] = function ($c) {
	return function ($request, $response) {
		return \Logigator\Api\ApiHelper::createJsonResponse($response, null, 404, 'Path not found');
	};
};
$container['notAllowedHandler'] = function ($c) {
	return function ($request, $response) {
		return \Logigator\Api\ApiHelper::createJsonResponse($response, null, 405, 'Method not allowed');
	};
};

$app = new Slim\App($container);

require_once 'routes.php';
require_once 'services.php';
require_once 'middleware.php';

createRoutes($app);
createServices($app, $config);
createMiddleware($app);

$app->run();
