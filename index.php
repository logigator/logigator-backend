<?php

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'services.php';
require_once 'routes.php';

$container = new \DI\Container();

\Slim\Factory\AppFactory::setContainer($container);
$app = \Slim\Factory\AppFactory::create();
$authenticationMiddleware = new \Logigator\Middleware\LoginValidationMiddleware($container);

createServices($container, $config);
createRoutes($app, $authenticationMiddleware);

$app->addErrorMiddleware($config['configuration'] == 'debug', false, false)
	->setDefaultErrorHandler(new \Logigator\HttpErrorHandler($app->getCallableResolver(), $app->getResponseFactory()));

$app->add(new \Logigator\Middleware\JsonValidationMiddleware());
$app->add(new \Logigator\Middleware\HeaderMiddleware());

$app->run();
