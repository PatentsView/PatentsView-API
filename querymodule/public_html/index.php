<?php
// Step 1: Require the Slim Framework
require_once dirname(__FILE__) . '/../thirdparty/Slim/Slim/Slim.php';
require_once dirname(__FILE__) . '/../app/config.php';

\Slim\Slim::registerAutoloader();


$config = Config::getInstance();

// Step 2: Instantiate a Slim application
$app = new \Slim\Slim(array(
    'mode' => $config::MODE
));

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
