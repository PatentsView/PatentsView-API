<?php
require_once dirname(__FILE__) . '/../app/execute_query.php';
require_once dirname(__FILE__) . '/../app/fieldSpecs.php';

class executeQuery_Test extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $config;
        $config = Config::getInstance();
    }

    public function testNormal()
    {
        $queryString = '{"_or":[{"patent_number":"8677601"},{"patent_number":"8677602"}]}';
        $expected = '{"patents":[{"patent_number":"8677601","patent_id":"8677601"},{"patent_number":"8677602","patent_id":"8677602"}],"count":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testNormalWithFieldList()
    {
        $queryString = '{"_or":[{"patent_number":"8677601"},{"patent_number":"8677602"}]}';
        $fieldList = array("patent_id", "patent_type", "patent_number", "patent_country", "patent_title", "inventor_last_name", "assignee_last_name");
        $expected = '{"patents":[{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventors":[{"inventor_last_name":"Millwee","inventor_id":"8677601-1"},{"inventor_last_name":"Shay","inventor_id":"8677601-2"},{"inventor_last_name":"Majkrzak","inventor_id":"8677601-3"},{"inventor_last_name":"Young","inventor_id":"8677601-4"},{"inventor_last_name":"Kupumbati","inventor_id":"8677601-5"}],"assignees":[{"assignee_last_name":"","assignee_id":"ba63daad7af208753ccc1e7383c11281"}]},{"patent_id":"8677602","patent_type":"utility","patent_number":"8677602","patent_country":"US","patent_title":"Method of making a flexible device shaft with angled spiral wrap","inventors":[{"inventor_last_name":"Dayton","inventor_id":"8677602-1"},{"inventor_last_name":"Boutillette","inventor_id":"8677602-2"}],"assignees":[{"assignee_last_name":"","assignee_id":"60ab17c614bc80bfbddd5b3c4bae3ea3"}]}],"count":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testNoResults()
    {
        $queryString = '{"patent_number":"DoesNotExist"}';
        $expected = '{"patents":null,"count":0}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testTextPhrase()
    {
        //TODO This test will fail when run against a different database
        $queryString = '{"_text_phrase":{"patent_title":"mesh node"}}';
        $expected = '{"patents":[{"patent_title":"Method for notifying about\/avoiding congestion situation of data transmission in wireless mesh network, and mesh node for the same","patent_id":"8681620"}],"count":1}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testTextAny()
    {
        //TODO This test will fail when run against a different database
        $queryString = '{"_text_any":{"patent_title":"mesh node"}}';
        $expected = '{"patents":[{"patent_title":"Surgical mesh maker","patent_id":"8679152"},{"patent_title":"Sensor node voltage clamping circuit and method","patent_id":"8680818"},{"patent_title":"Method for notifying about\/avoiding congestion situation of data transmission in wireless mesh network, and mesh node for the same","patent_id":"8681620"},{"patent_title":"Configuring a wireless mesh network of communication devices with packet message transmission, and routing packet message transmission in such a network","patent_id":"8681656"},{"patent_title":"Optical wavelength division node","patent_id":"8682164"},{"patent_title":"Distance metric estimating system, coordinate calculating node, distance metric estimating method, and program","patent_id":"8682611"},{"patent_title":"Method, system, and node for node interconnection on content delivery network","patent_id":"8682968"},{"patent_title":"Intermediary node with distribution capability and communication network with federated metering capability","patent_id":"8683040"}],"count":8}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testTextAll()
    {
        //TODO This test will fail when run against a different database
        $queryString = '{"_text_all":{"patent_title":"mesh network"}}';
        $expected = '{"patents":[{"patent_title":"Method for notifying about\/avoiding congestion situation of data transmission in wireless mesh network, and mesh node for the same","patent_id":"8681620"},{"patent_title":"Configuring a wireless mesh network of communication devices with packet message transmission, and routing packet message transmission in such a network","patent_id":"8681656"}],"count":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllGroups()
    {
        $queryString = '{"patent_number":"8677601"}';
        $fieldList = array("ipc_main_group","applicationcitation_category","inventor_last_name","patentcitation_category","uspc_mainclass_id","uspc_subclass_id","assignee_organization");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, $fieldList);
        $this->assertEquals(1, count($results['patents']));
        $this->assertArrayHasKey('inventors', $results['patents'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['patents'][0]['inventors'][0]);
        $this->assertArrayHasKey('assignees', $results['patents'][0]);
        $this->assertArrayHasKey('assignee_organization', $results['patents'][0]['assignees'][0]);
        $this->assertArrayHasKey('IPCs', $results['patents'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['patents'][0]['IPCs'][0]);
        $this->assertArrayHasKey('application_citations', $results['patents'][0]);
        $this->assertArrayHasKey('applicationcitation_category', $results['patents'][0]['application_citations'][0]);
        $this->assertArrayHasKey('patent_citations', $results['patents'][0]);
        $this->assertArrayHasKey('patentcitation_category', $results['patents'][0]['patent_citations'][0]);
        $this->assertArrayHasKey('uspcs', $results['patents'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['patents'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['patents'][0]['uspcs'][0]);
    }

    public function testAllFields()
    {
        global $FIELD_SPECS;
        $queryString = '{"patent_number":"8677601"}';
        $fieldList = array_keys($FIELD_SPECS);
        $decoded = json_decode($queryString, true);
        $results = executeQuery($decoded, $fieldList);
        $this->assertEquals(1, count($results['patents']));
        $this->assertArrayHasKey('inventors', $results['patents'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['patents'][0]['inventors'][0]);
        $this->assertArrayHasKey('assignees', $results['patents'][0]);
        $this->assertArrayHasKey('assignee_organization', $results['patents'][0]['assignees'][0]);
        $this->assertArrayHasKey('applications', $results['patents'][0]);
        $this->assertArrayHasKey('application_id', $results['patents'][0]['applications'][0]);
        $this->assertArrayHasKey('IPCs', $results['patents'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['patents'][0]['IPCs'][0]);
        $this->assertArrayHasKey('application_citations', $results['patents'][0]);
        $this->assertArrayHasKey('applicationcitation_category', $results['patents'][0]['application_citations'][0]);
        $this->assertArrayHasKey('patent_citations', $results['patents'][0]);
        $this->assertArrayHasKey('patentcitation_category', $results['patents'][0]['patent_citations'][0]);
        $this->assertArrayHasKey('uspcs', $results['patents'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['patents'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['patents'][0]['uspcs'][0]);
    }
}