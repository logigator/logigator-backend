<?php
function createMiddleware($app) {
    $app->add(new \Logigator\Middleware\JsonValidationMiddleware());
}