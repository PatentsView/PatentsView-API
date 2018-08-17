<?php
ini_set('max_execution_time', 400);
// Step 1: Require the Slim Framework

require_once dirname(__FILE__) . '/../app/config.php';
require __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/ErrorHandler.php';

$logger = Logger::getLogger("base");


try {
    $config = Config::getInstance();
} catch (\Exceptions\ConfigException $configException) {
    ErrorHandler::getHandler()->sendError(500, "Server Error","This is a server failure, trying different query is unlikely to work. Administrators have been notified");
throw $configException;
}

// Step 2: Instantiate a Slim application
$app = new \Slim\Slim(array(
    'mode' => $config::MODE
));

$app->contentType('application/json; charset=utf-8');

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => false,
        'debug' => true
    ));
});

// Step 3: Define the Slim application routes

// GET route

require_once '../app/routes/query.php';


// Step 4: Run the Slim application
$app->run();
