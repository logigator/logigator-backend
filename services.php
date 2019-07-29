<?php
$app->getContainer()['AuthenticationService'] = function ($c) {
  return new \Logigator\Service\AuthenticationService($c);
};