<?php

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ErrorHandler
{

    // Pattern for this singleton from here: http://www.phptherightway.com/pages/Design-Patterns.html
    public static function getHandler()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    public function sendError($status, $friendlyMessage,$config, $details = null)
    {
        global $app;
        if ($details === null) $details = $friendlyMessage;
        $this->getLogger($config)->error($details);
        if ($app !== null) {
            $app->response->header('X-Status-Reason', $friendlyMessage);
            $app->halt($status);
        }
    }

    public function getLogger($config)
    {
        static $logger = null;
        if ($logger === null) {
            // create a log channel
            $logger = new Logger('API');
            $log_path = $config->getLogPath();
            $rHandler = new \Monolog\Handler\RotatingFileHandler($log_path . 'api.log', 14, Logger::INFO);

//            $cHandler = new \Monolog\Handler\StreamHandler($log_path . 'api.log', 14, Logger::DEBUG);

            $logger->pushHandler($rHandler);
        }
        return $logger;
    }
} 