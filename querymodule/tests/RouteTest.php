<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 1:36 PM
 */
require_once("../app/routes/routeUtilites.php");

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function test_QParam()
    {
        $request = array();
        $processed_array = array();
        $var_check = null;
        try {
            $processed_array = CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RQ1", $var_check);


        $request = array("q" => "random_text. Not valid json");
        $var_check = null;
        try {
            $processed_array = CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RQ2", $var_check);


        $request = array("q" => "{\"key\":\"pair\"}");
        $processed_array = CheckGetParameters($request);
        $this->assertGreaterThan(0, count($processed_array));
        $this->assertEquals(5, count($processed_array));
        $this->assertNotNull($processed_array[0]);
        $this->assertEquals("json", $processed_array[4]);

        $request = array("q" => "{\"key\":\"pair\",\"second_key\":\"second_pair\"}");
        $var_check = null;
        try {
            $processed_array = CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RQ3", $var_check);

    }

    public function test_fParam()
    {


        $request = array("q" => "{\"key\":\"pair\"}", "f" => "random_text. Not valid json");
        $var_check = null;
        try {
            $processed_array = CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RF2", $var_check);


        $request = array("q" => "{\"key\":\"pair\"}", "f" => "{\"key\":\"pair\"}");
        $processed_array = CheckGetParameters($request);
        $this->assertGreaterThan(0, count($processed_array));
        $this->assertEquals(5, count($processed_array));
        $this->assertNotNull($processed_array[1]);
    }

    public function test_sParam()
    {
        $request = array("q" => "{\"key\":\"pair\"}", "s" => "random_text. Not valid json");
        $var_check = null;
        try {
            CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RS2", $var_check);


        $request = array("q" => "{\"key\":\"pair\"}", "s" => "{\"key\":\"pair\"}");
        $processed_array = CheckGetParameters($request);
        $this->assertGreaterThan(0, count($processed_array));
        $this->assertEquals(5, count($processed_array));
        $this->assertNotNull($processed_array[2]);
    }

    public function test_oParam()
    {
        $request = array("q" => "{\"key\":\"pair\"}", "o" => "random_text. Not valid json");
        $var_check = null;
        try {
            CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RO2", $var_check);


        $request = array("q" => "{\"key\":\"pair\"}", "o" => "{\"key\":\"pair\"}");
        $processed_array = CheckGetParameters($request);
        $this->assertGreaterThan(0, count($processed_array));
        $this->assertEquals(5, count($processed_array));
        $this->assertNotNull($processed_array[3]);
    }

    public function test_formatParam()
    {
        $request = array("q" => "{\"key\":\"pair\"}", "format" => "random_text. Not valid json. Not valid anything, Except english sentence, if you ignore character case and punctuation");
        $var_check = null;
        try {
            CheckGetParameters($request);
        } catch (\Exceptions\RequestException $e) {
            $var_check = $e->getCustomCode();
        }
        $this->assertEquals("RFO4", $var_check);

        $request = array("q" => "{\"key\":\"pair\"}", "format" => "xml");
        $processed_array = CheckGetParameters($request);
        $this->assertGreaterThan(0, count($processed_array));
        $this->assertEquals(5, count($processed_array));
        $this->assertEquals("xml", $processed_array[4]);

        $request = array("q" => "{\"key\":\"pair\"}", "format" => "json");
        $processed_array = CheckGetParameters($request);
        $this->assertGreaterThan(0, count($processed_array));
        $this->assertEquals(5, count($processed_array));
        $this->assertEquals("json", $processed_array[4]);
    }


}
