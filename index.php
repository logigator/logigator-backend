<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

$container = new \DI\Container();

\Slim\Factory\AppFactory::setContainer($container);
$app = \Slim\Factory\AppFactory::create();

$app->addErrorMiddleware($config['configuration'] == 'debug', false, false)
	->setDefaultErrorHandler(new \Logigator\HttpErrorHandler($app->getCallableResolver(), $app->getResponseFactory()));

$app->add(new \Logigator\Middleware\JsonValidationMiddleware());

$app->group('/auth', function (\Slim\Routing\RouteCollectorProxy $group) {
	$group->get('/google-auth-url', Logigator\Api\Auth\GetGoogleAuthUrl::class);
	$group->post('/verify-google-credentials', \Logigator\Api\Auth\VerifyGoogleCredentials::class);

	$group->get('/twitter-auth-url', \Logigator\Api\Auth\GetTwitterAuthUrl::class);
	$group->post('/verify-twitter-credentials', \Logigator\Api\Auth\VerifyTwitterCredentials::class);

	$group->post('/register-email', \Logigator\Api\Auth\RegisterEmail::class);
	$group->post('/login-email', \Logigator\Api\Auth\LoginEmail::class);

	$group->get('/logout', \Logigator\Api\Auth\Logout::class);
});

$app->group('/project', function(\Slim\Routing\RouteCollectorProxy $group){
	$group->post('/create', \Logigator\Api\Projects\CreateProject::class);
	$group->post('/open', \Logigator\Api\Projects\OpenProject::class);

});

$app->run();
