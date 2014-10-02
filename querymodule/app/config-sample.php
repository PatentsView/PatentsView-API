<?php

$config = null; // We're doing this here so it can be instantiated once but used everywhere

final class Config
{

    const MODE = 'development'; // options are "development", "staging", "production"

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

        return array('host' => $host, 'user' => $user, 'password' => $pass, 'database' => $database);
    }


}