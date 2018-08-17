<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 8/16/18
 * Time: 1:22 PM
 */

require_once dirname(__FILE__) . '/../app/config.php';

class ConfigTest extends PHPUnit_Framework_TestCase
{

    public function testDBSettings()
    {
        $ini_path = "/Users/smadhavan/Universe/Projects/webapps/current/querymodule/tests/inis/correct-db";
        putenv("CONFIG_PATH=$ini_path");
        $c = Config::getInstance();
        $dbs = $c->getDbSettings();
        $this->assertEquals(6, count($dbs));
        $this->assertArrayHasKey("host", $dbs);
        $this->assertArrayHasKey("port", $dbs);
        $this->assertArrayHasKey("user", $dbs);
        $this->assertArrayHasKey("password", $dbs);
        $this->assertArrayHasKey("database", $dbs);
        $this->assertArrayHasKey("supportDatabase", $dbs);
    }

    public function getDBSettings()
    {
        return array(array("/Users/smadhavan/Universe/Projects/webapps/current/querymodule/tests/inis/incorrect-syntax", "/Users/smadhavan/Universe/Projects/webapps/current/querymodule/app/specs"));
    }
}
