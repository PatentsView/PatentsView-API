<?php
ini_set('max_execution_time', 400);
// Step 1: Require the Slim Framework



// config.php provides global configuration class instance (db details, solr details etc)
require_once dirname(__FILE__) . '/../app/config.php';
require __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/ErrorHandler.php';
require_once dirname(__FILE__) . '/../app/Exceptions/APIError.php';


$logger = Logger::getLogger("base");


try {
    $config = Config::getInstance();
} catch (\Exceptions\ConfigException $configException) {
    ErrorHandler::getHandler()->sendError(500, "Server Error","This is a server failure, trying different query is unlikely to work. Administrators have been notified");
    throw $configException;
}

$appConfig = [
    'settings' => [
        'displayErrorDetails' => false,

        'logger' => [
            'name' => 'slim-app',
            'level' => 'debug',
            'path' => 'php://stderr',
        ],
    ],
];

if (Config::MODE == 'development') {
    $appConfig = [
        'settings' => [
            'displayErrorDetails' => true,
        ],
    ];

}
// Step 2: Instantiate a Slim application
$app = new \Slim\App($appConfig);
$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return new Slim\Handlers\APIError();
};

// Step 3: Define the Slim application routes

// GET route

require_once '../app/routes/query.php';


// Step 4: Run the Slim application
$app->run();
