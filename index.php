<?php

require_once 'vendor/autoload.php';
require_once 'routes.php';

use \DI\ContainerBuilder;
use Logigator\Middleware\RequestValidationMiddleware;
use \Slim\Factory\AppFactory;
use \Logigator\Middleware\LoginValidationMiddleware;
use \Logigator\Middleware\HeaderMiddleware;
use \Logigator\HttpErrorHandler;

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAnnotations(true);
$container = $containerBuilder->build();


$app = AppFactory::createFromContainer($container);

$authenticationMiddleware = new LoginValidationMiddleware();
$container->injectOn($authenticationMiddleware);

createRoutes($app, $authenticationMiddleware);

$requestValidation = new RequestValidationMiddleware();
$container->injectOn($requestValidation);
$app->add($requestValidation);

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false)
	->setDefaultErrorHandler(new HttpErrorHandler($app->getCallableResolver(), $app->getResponseFactory()));
$app->add(new HeaderMiddleware());

$app->run();
