<?php

$app->group('/auth', function (\Slim\App $app) {
    $app->get('/google-auth-url', Logigator\Api\Auth\GetGoogleAuthUrl::class);
    $app->post('/verify-google-credentials', \Logigator\Api\Auth\VerifyGoogleCredentials::class);

    $app->get('/twitter-auth-url', \Logigator\Api\Auth\GetTwitterAuthUrl::class);
    $app->post('/verify-twitter-credentials', \Logigator\Api\Auth\VerifyTwitterCredentials::class);
});