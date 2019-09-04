<?php

function createRoutes(Slim\App $app, $authenticationMiddleware) {
	$app->group('/auth', function (\Slim\Routing\RouteCollectorProxy $group) {
		$group->get('/google-auth-url', \Logigator\Api\Auth\GetGoogleAuthUrl::class);
		$group->post('/verify-google-credentials', \Logigator\Api\Auth\VerifyGoogleCredentials::class);

		$group->get('/twitter-auth-url', \Logigator\Api\Auth\GetTwitterAuthUrl::class);
		$group->post('/verify-twitter-credentials', \Logigator\Api\Auth\VerifyTwitterCredentials::class);

		$group->post('/register-email', \Logigator\Api\Auth\RegisterEmail::class);
		$group->post('/login-email', \Logigator\Api\Auth\LoginEmail::class);

		$group->get('/logout', \Logigator\Api\Auth\Logout::class)->add($authenticationMiddleware);
	});

	$app->group('/project', function(\Slim\Routing\RouteCollectorProxy $group){
		$group->post('/create', \Logigator\Api\Projects\CreateProject::class);
		$group->post('/open', \Logigator\Api\Projects\OpenProject::class);

	})->add($authenticationMiddleware);
}
