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
		$group->post('/save/{id}', \Logigator\Api\Projects\SaveProject::class);
		$group->post('/update/{id}', \Logigator\Api\Projects\UpdateProjectInfo::class);
		$group->get('/clone/{address}', \Logigator\Api\Projects\CloneProject::class);
		$group->get('/get-all-projects-info', \Logigator\Api\Projects\GetAllProjectsInfo::class);
		$group->get('/get-all-components-info', \Logigator\Api\Projects\GetAllComponentsInfo::class);
	})->add($authenticationMiddleware);

	$app->group('/share', function(\Slim\Routing\RouteCollectorProxy $group) use ($authenticationMiddleware) {
        $group->get('/get/{address}', \Logigator\Api\Share\GetShare::class);
        $group->post('/create', \Logigator\Api\Share\CreateShare::class)->add($authenticationMiddleware);
        $group->get('/get', \Logigator\Api\Share\ListShares::class)->add($authenticationMiddleware);
        $group->post('/update/{address}', \Logigator\Api\Share\UpdateShare::class)->add($authenticationMiddleware);
		$group->get('/delete/{address}', \Logigator\Api\Share\DeleteShare::class)->add($authenticationMiddleware);
	});

	$app->group('/user', function(\Slim\Routing\RouteCollectorProxy $group) {
		$group->get('/get', \Logigator\Api\User\GetUserInfo::class);
		$group->post('/upload-picture', \Logigator\Api\User\UploadPicture::class);
		$group->post('/update', \Logigator\Api\User\UpdateUser::class);
	})->add($authenticationMiddleware);
}
