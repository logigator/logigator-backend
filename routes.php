<?php

function createRoutes(Slim\App $app, $authenticationMiddleware) {
	$app->group('/auth', function (\Slim\Routing\RouteCollectorProxy $group) use ($authenticationMiddleware) {
		$group->get('/google-auth-url', \Logigator\Api\Auth\GetGoogleAuthUrl::class);
		$group->post('/verify-google-credentials', \Logigator\Api\Auth\VerifyGoogleCredentials::class);

		$group->get('/twitter-auth-url', \Logigator\Api\Auth\GetTwitterAuthUrl::class);
		$group->post('/verify-twitter-credentials', \Logigator\Api\Auth\VerifyTwitterCredentials::class);

		$group->post('/register-email', \Logigator\Api\Auth\RegisterEmail::class);
		$group->post('/login-email', \Logigator\Api\Auth\LoginEmail::class);

		$group->get('/logout', \Logigator\Api\Auth\Logout::class)->add($authenticationMiddleware);
	});

	$app->group('/project', function(\Slim\Routing\RouteCollectorProxy $group) {
		$group->post('/create', \Logigator\Api\Projects\CreateProject::class);
		$group->get('/open/{id}', \Logigator\Api\Projects\OpenProject::class);
		$group->get('/delete/{id}', \Logigator\Api\Projects\DeleteProject::class);
		$group->post('/save', \Logigator\Api\Projects\SaveProject::class);
		$group->post('/update', \Logigator\Api\Projects\UpdateProjectInfo::class);
		$group->get('/clone/{address}', \Logigator\Api\Projects\CloneProject::class);
		$group->get('/get-all-projects-info', \Logigator\Api\Projects\GetAllProjectsInfo::class);
		$group->get('/get-all-components-info', \Logigator\Api\Projects\GetAllComponentsInfo::class);
	})->add($authenticationMiddleware);

	$app->group('/share', function(\Slim\Routing\RouteCollectorProxy $group) use ($authenticationMiddleware) {
        $group->get('/get/{id}', \Logigator\Api\Share\GetShare::class);
        $group->post('/create', \Logigator\Api\Share\CreateShare::class)->add($authenticationMiddleware);
    });

	$app->group('/user', function(\Slim\Routing\RouteCollectorProxy $group) use ($authenticationMiddleware) {
		$group->get('/get', \Logigator\Api\User\GetUserInfo::class);
	})->add($authenticationMiddleware);
}
