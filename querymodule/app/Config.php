<?php

$config = null; // We're doing this here so it can be instantiated once but used everywhere
//require_once (dirname(__FILE__)."/PVExceptions/ConfigException.php");
final class Config
{

//    const MODE = 'production';

    private $environment_mode = "production";
    // options are "development", "staging", "production"
    private static $instance;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $database;
    private $supportDatabase;
    private $temp_dir;

    private $page_size;
    private $result_size;

    private function __construct()
    {
        // TODO: Wrap this in Try catch
        $ini_path = getenv("CONFIG_PATH");
        $ini_file_path = $ini_path . "/app.ini";
        //$ini_file_path = $ini_path . "/half-join-app.ini";
        $this->host = getenv("API_DB_HOST");
        $this->port = getenv("API_DB_PORT");
        $this->user = getenv("API_DB_USER");
        $this->pass = getenv("API_DB_PASSWORD");
        $this->database = getenv("API_DB_DATABASE");
        $this->supportDatabase = getenv("API_DB_SUPPORT_DATABASE");

        $this->mongo_user = getenv("CACHE_DB_USER");
        $this->mongo_host = getenv("CACHE_DB_HOST");
        $this->mongo_port = getenv("CACHE_DB_PORT");
        $this->mongo_password = getenv("CACHE_DB_PASSWORD");
        $this->mongo_db = getenv("CACHE_DB_DATABASE");
        $this->mongo_collection = getenv("CACHE_DB_COLLECTION");


        $this->page_size = getenv('API_PAGE_SIZE');
        $this->result_size = getenv('API_RESULT_SIZE');
        $this->temp_dir = getenv("API_TEMP_PATH");
        $env_mode = getenv("ENV");
        $this->environment_mode = ($env_mode === null || trim($env_mode) === '') ? 'production' : $env_mode;
    }


    public
    static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    public function getMode()
    {
        return $this->environment_mode;
    }

    public
    function getDbSettings()
    {

        return array('host' => $this->host, 'port' => $this->port, 'user' => $this->user, 'password' => $this->pass, 'database' => $this->database, 'supportDatabase' => $this->supportDatabase);
    }

    public
    function getMongoSettings()
    {

        return array('mongo_host' => $this->mongo_host, 'mongo_port' => $this->mongo_port, 'mongo_user' => $this->mongo_user, 'mongo_password' => $this->mongo_password, 'mongo_db' => $this->mongo_db, 'mongo_collection' => $this->mongo_collection);
    }

    public
    function getMaxPageSize()
    {
        return $this->page_size;
    }

    public
    function getQueryResultLimit()
    {
        return $this->result_size;
    }

    public
    function getTempPath()
    {
        return $this->temp_dir;
    }

}