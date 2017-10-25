<?php
require_once dirname(__FILE__) . '/../thirdparty/apache-log4php-2.3.0/Logger.php';

class ErrorHandler {

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
    protected function __construct() {}

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup() {}

    public function sendError($status, $friendlyMessage, $details=null)
    {
        global $app;
        if ($details === null) $details = $friendlyMessage;
        $this->getLogger()->error($details);
        if ($app !== null) {
            $app->response->header('X-Status-Reason', $friendlyMessage);
            $app->halt($status);
        }
    }

    public function getLogger()
    {
        static $logger = null;
        if ($logger === null) {
            Logger::configure(dirname(__FILE__) . '/logger_config.xml');
            $logger = Logger::getLogger('myLogger');
        }
        return $logger;
    }
} 