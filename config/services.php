<?php
use Phalcon\DI\FactoryDefault;

use Phalcon\Config;
use Phalcon\Logger;
use Phalcon\Logger\Multiple as MultipleStream;
use Phalcon\Logger\Adapter\File as FileAppender;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter\Stream as ConsoleAppender;

use Phalcon\Mvc\Model;
// use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Db\Adapter\Pdo\Sqlite as DbAdapter;

use Phalcon\Mvc\Model\Metadata\Apc as MetaDataAdapter;

use Phalcon\Events\Manager as EventsManager;

use Phalcon\Mvc\Url as UrlResolver;
use PhalconX\Mvc\Router\Annotations as Router;
use PhalconX\Cli\Router as CliRouter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use PhalconX\Mvc\View\VoltExtension;
use PhalconX\Mvc\ViewHelper;

use PhalconX\Validator;
use PhalconX\Util\Reflection;
use PhalconX\Util\ObjectMapper;
use Phalcon\Annotations\Adapter\Apc as Annotations;

$di = new FactoryDefault();

$di['config'] = $config;
$di['registry'] = new Config;
$di['loader'] = $loader;
$di['logger'] = function () use ($config) {
    $isCli = PHP_SAPI == 'cli';
    $prefix = $config->application->logsDir . ($isCli ? 'cli-' : '');
    
    $logger = new MultipleStream;
    $appender = new FileAppender($prefix . 'error.log');
    $appender->setLogLevel(Logger::ERROR);
    $logger->push($appender);

    $appender = new FileAppender($prefix . 'default.log');
    $appender->setLogLevel(Logger::INFO);
    $logger->push($appender);
    if ($isCli) {
        $appender = new ConsoleAppender('php://stderr');
        $formatter = new LineFormatter("%date% [%type%] %message%\n");
        $appender->setFormatter($formatter);
        $appender->setLogLevel(Logger::ERROR);
        $logger->push($appender);
    }
    return $logger;
};

$di['db'] = function () use ($di, $config) {
    Model::setup(['notNullValidations' => false]);
    $conn = new DbAdapter($config->database->toArray());
    if (isset($config->eventListeners->db)) {
        $em = $di['eventsManager'];
        foreach ($config->eventListeners->db as $listener => $options) {
            if (is_numeric($listener)) {
                $listener = $options;
                $options = null;
            }
            $em->attach('db', $di->get($listener, [$options]));
        }
        $conn->setEventsManager($em);
    }
    return $conn;
};
$di['modelsMetadata'] = MetaDataAdapter::CLASS;

$di['eventsManager'] = function () use ($di, $config) {
    $eventsManager = new EventsManager();
    $di['eventsManager'] = $eventsManager;
    if (isset($config->eventListeners)) {
        $eventListeners = $config->eventListeners;
        foreach ($eventListeners as $event => $listeners) {
            if ($event == 'db') {
                continue;
            } elseif ($event == 'dispatch') {
                $di['dispatcher']->setEventsManager($eventsManager);
            }
            foreach ($listeners as $listener => $options) {
                if (is_numeric($listener)) {
                    $listener = $options;
                    $options = null;
                }
                if (isset($options)) {
                    $listener = $di->get($listener, [$options]);
                } else {
                    $listener = $di->get($listener);
                }
                $eventsManager->attach($event, $listener);
            }
        }
    }
    return $eventsManager;
};

$di['view'] = function () use ($di, $config) {
    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    $view->disableLevel([View::LEVEL_LAYOUT => true]);

    $volt = new VoltEngine($view, $di);
    $volt->setOptions(array(
        'compiledPath' => $config->application->cacheDir,
        'compiledSeparator' => '_'
    ));
    $volt->getCompiler()->addExtension(new VoltExtension);

    $view->registerEngines(array(
        '.volt' => $volt
    ));
    return $view;
};

$di['viewHelper'] = ViewHelper::CLASS;

if (PHP_SAPI == 'cli') {
    $di["router"] = function () use ($config) {
        $router = new CliRouter;
        $router->scan($config->application->tasksDir);
        return $router;
    };
} else {
    $di['url'] = function () use ($config) {
        $url = new UrlResolver();
        $url->setBaseUri($config->application->baseUri);
        $url->setStaticBaseUri($config->application->staticBaseUri);
        return $url;
    };
    $di['router'] = function () use ($config) {
        $router = new Router;
        $router->clear();
        $router->scan($config->application->controllersDir);
        $router->setDefaultNamespace('AdminGen\Controllers');
        return $router;
    };
}

$di['validator'] = Validator::CLASS;
$di['reflection'] = Reflection::CLASS;
$di['objectMapper'] = ObjectMapper::CLASS;
$di['annotations'] = Annotations::CLASS;
return $di;
