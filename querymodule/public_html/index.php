<?php
ini_set('max_execution_time', 400);
// Step 1: Require the Slim Framework


// config.php provides global configuration class instance (db details, solr details etc)
require_once dirname(__FILE__) . '/../app/config.php';
require __DIR__ . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/ErrorHandler.php';
require_once dirname(__FILE__) . '/../app/Exceptions/APIError.php';


$config = Config::getInstance();
$logger = \ErrorHandler::getHandler()->getLogger($config);
$appConfig = [
    'settings' => [
        'displayErrorDetails' => false,

//        'logger' => [
//            'name' => 'slim-app',
//            'level' => 'info',
//            'path' => 'php://stderr',
//        ],
    ],
];

if (Config::MODE == 'development') {
    $appConfig = [
        'settings' => [
            'displayErrorDetails' => false,
        ],
    ];

}
// Step 2: Instantiate a Slim application
$app = new \Slim\App($appConfig);
$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        global $config;
        $status = $exception->getCode() ?: 500;
        $logger = \ErrorHandler::getHandler()->getLogger($config);
        $ip = $request->getServerParam('REMOTE_ADDR');
        $customCode = $exception->getCustomCode();
        $query = $request->getUri();
        $logger->error("$status\t$ip\t$query");
//        $logger->debug("$exception->getMessage()");
//
        return $response->withHeader("X-Status-Reason", $exception->getMessage())->withJson(array("status" => "error", "payload" => array("error" => $exception->getMessage(), "code" => $customCode)), $status);
    };
};

// Step 3: Define the Slim application routes

// GET route

require_once '../app/routes/query.php';


// Step 4: Run the Slim application
$app->run();
