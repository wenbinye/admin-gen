<?php
error_reporting(E_ALL);

define('APP_PATH', dirname(__DIR__));

$loader = require(APP_PATH.'/vendor/autoload.php');
$config = require(APP_PATH . "/config/config.php");
$di = require(APP_PATH . "/config/services.php");

try {
    $app = new \Phalcon\Mvc\Application($di);
    $app->setEventsManager($di['eventsManager']);
    $app->useImplicitView(false);
    echo $app->handle()->getContent();
} catch (\Exception $e) {
    $di['logger']->error($e->getMessage()."\n".$e->getTraceAsString());
    $di['response']->setStatusCode(500)
        ->setContent("Internal error")
        ->send();
}
