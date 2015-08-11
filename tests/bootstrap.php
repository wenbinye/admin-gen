<?php
use Phalcon\DI\FactoryDefault;

use Phalcon\Cache;
use Phalcon\Logger\Adapter\Stream as ConsoleLogger;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Mvc\Model\Metadata\Memory as MetadataAdapter;

function bootstrap_test()
{
    define('APP_PATH', realpath(__DIR__.'/..'));
    $loader = require(APP_PATH . '/vendor/autoload.php');
    $loader->add("AdminGen", __DIR__);
    $config = require(APP_PATH.'/config/config.php');
    $config->fixturesDir = __DIR__ . '/fixtures';
    $di = require(APP_PATH.'/config/services.php');
}
bootstrap_test();
