<?php

$config = null; // We're doing this here so it can be instantiated once but used everywhere

final class Config
{

    const MODE = 'staging'; // options are "development", "staging", "production"

    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }

        return self::$instance;
    }


    public function getDbSettings()
    {
        $host = 'MySQLHostStringHere';
        $user = 'UserName';
        $pass = 'TheSecretPassword';
        $database = 'TheDatabaseToUse';
        $supportDatabase = 'PVSupport_dev';

        return array('host' => $host, 'user' => $user, 'password' => $pass, 'database' => $database, 'supportDatabase' => $supportDatabase);
    }

    public function getMaxPageSize()
    {
        return 10000;
    }

    public function getQueryResultLimit()
    {
        return 100000;
    }


}