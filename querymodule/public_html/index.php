<?php
ini_set('max_execution_time', 360);
// Step 1: Require the Slim Framework
//require_once dirname(__FILE__) . '/../thirdparty/Slim/Slim/Slim.php';

// config.php provides global configuration class instance (db details, solr details etc)
require_once dirname(__FILE__) . '/../app/config.php';

// Loads thirdparty libraries used (see composer.json)
require_once dirname(__FILE__) . '/../vendor/autoload.php';
$config=Config::getInstance();

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

if (Config::MODE == 'development'){
    $appConfig = [
        'settings' => [
            'displayErrorDetails' => true,
        ],
    ];

}
// Step 2: Instantiate a Slim application
$app = new \Slim\App($appConfig);




// Step 3: Define the Slim application routes

// GET route

require_once '../app/routes/query.php';


// Step 4: Run the Slim application
$app->run();
