<?php
function createRoutes($app) {
	$app->group('/auth', function (\Slim\App $app) {
		$app->get('/google-auth-url', Logigator\Api\Auth\GetGoogleAuthUrl::class);
		$app->post('/verify-google-credentials', \Logigator\Api\Auth\VerifyGoogleCredentials::class);

		$app->get('/twitter-auth-url', \Logigator\Api\Auth\GetTwitterAuthUrl::class);
		$app->post('/verify-twitter-credentials', \Logigator\Api\Auth\VerifyTwitterCredentials::class);

		$app->post('/register-email', \Logigator\Api\Auth\RegisterEmail::class);
		$app->post('/login-email', \Logigator\Api\Auth\LoginEmail::class);


		$app->get('/logout', \Logigator\Api\Auth\Logout::class);
	});
	$app->group('/project', function(\Slim\App $app){
		$app->post('/create', \Logigator\Api\Projects\CreateProject::class);
		$app->post('/open', \Logigator\Api\Projects\OpenProject::class);
		$app->post('/delete', \Logigator\Api\Projects\DeleteProject::class);
		$app->post('/get-all-projects-info', \Logigator\Api\Projects\GetAllProjectsInfo::class);
		$app->post('/get-all-components-info', \Logigator\Api\Projects\GetAllComponentsInfo::class);

	});
}
