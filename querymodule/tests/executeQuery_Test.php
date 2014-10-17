<?php
require_once dirname(__FILE__) . '/../app/execute_query.php';
require_once dirname(__FILE__) . '/../app/entitySpecs.php';

class executeQuery_Test extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $config;
        $config = Config::getInstance();
    }

    public function testNormalPatent()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_or":[{"patent_number":"8677601"},{"patent_number":"8677602"}]}';
        $expected = '{"patents":[{"patent_number":"8677601"},{"patent_number":"8677602"}],"count":2,"total_found":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testNormalWithFieldList()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_or":[{"patent_number":"8677601"},{"patent_number":"8677602"}]}';
        $fieldList = array("patent_id", "patent_type", "patent_number", "patent_country", "patent_title", "inventor_last_name", "assignee_last_name");
        $expected = '{"patents":[{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventors":[{"inventor_last_name":"Millwee"},{"inventor_last_name":"Shay"},{"inventor_last_name":"Majkrzak"},{"inventor_last_name":"Young"},{"inventor_last_name":"Kupumbati"}],"assignees":[{"assignee_last_name":""}]},{"patent_id":"8677602","patent_type":"utility","patent_number":"8677602","patent_country":"US","patent_title":"Method of making a flexible device shaft with angled spiral wrap","inventors":[{"inventor_last_name":"Dayton"},{"inventor_last_name":"Boutillette"}],"assignees":[{"assignee_last_name":""}]}],"count":2,"total_found":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testNoResults()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"patent_number":"DoesNotExist"}';
        $expected = '{"patents":null,"count":0,"total_found":0}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testTextPhrase()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        //TODO This test will fail when run against a different database
        $queryString = '{"_text_phrase":{"patent_title":"mesh node"}}';
        $expected = '{"patents":[{"patent_title":"Method for notifying about\/avoiding congestion situation of data transmission in wireless mesh network, and mesh node for the same"}],"count":1,"total_found":1}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testTextAny()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        //TODO This test will fail when run against a different database
        $queryString = '{"_text_any":{"patent_title":"mesh node"}}';
        $expected = '{"patents":[{"patent_title":"Surgical mesh maker"},{"patent_title":"Sensor node voltage clamping circuit and method"},{"patent_title":"Method for notifying about\/avoiding congestion situation of data transmission in wireless mesh network, and mesh node for the same"},{"patent_title":"Configuring a wireless mesh network of communication devices with packet message transmission, and routing packet message transmission in such a network"},{"patent_title":"Optical wavelength division node"},{"patent_title":"Distance metric estimating system, coordinate calculating node, distance metric estimating method, and program"},{"patent_title":"Method, system, and node for node interconnection on content delivery network"},{"patent_title":"Intermediary node with distribution capability and communication network with federated metering capability"}],"count":8,"total_found":8}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testTextAll()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        //TODO This test will fail when run against a different database
        $queryString = '{"_text_all":{"patent_title":"mesh network"}}';
        $expected = '{"patents":[{"patent_title":"Method for notifying about\/avoiding congestion situation of data transmission in wireless mesh network, and mesh node for the same"},{"patent_title":"Configuring a wireless mesh network of communication devices with packet message transmission, and routing packet message transmission in such a network"}],"count":2,"total_found":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllGroups()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"patent_number":"8677601"}';
        $fieldList = array("ipc_main_group","appcit_category","inventor_last_name","cited_patent_category","uspc_mainclass_id","uspc_subclass_id","assignee_organization");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['patents']));
        $this->assertArrayHasKey('inventors', $results['patents'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['patents'][0]['inventors'][0]);
        $this->assertArrayHasKey('assignees', $results['patents'][0]);
        $this->assertArrayHasKey('assignee_organization', $results['patents'][0]['assignees'][0]);
        $this->assertArrayHasKey('IPCs', $results['patents'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['patents'][0]['IPCs'][0]);
        $this->assertArrayHasKey('application_citations', $results['patents'][0]);
        $this->assertArrayHasKey('appcit_category', $results['patents'][0]['application_citations'][0]);
        $this->assertArrayHasKey('cited_patents', $results['patents'][0]);
        $this->assertArrayHasKey('cited_patent_category', $results['patents'][0]['cited_patents'][0]);
        $this->assertArrayHasKey('uspcs', $results['patents'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['patents'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['patents'][0]['uspcs'][0]);
    }

    public function testAllFields()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"patent_number":"8677601"}';
        $fieldList = array_keys($PATENT_FIELD_SPECS);
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['patents']));
        $this->assertArrayHasKey('inventors', $results['patents'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['patents'][0]['inventors'][0]);
        $this->assertArrayHasKey('assignees', $results['patents'][0]);
        $this->assertArrayHasKey('assignee_organization', $results['patents'][0]['assignees'][0]);
        $this->assertArrayHasKey('applications', $results['patents'][0]);
        $this->assertArrayHasKey('app_id', $results['patents'][0]['applications'][0]);
        $this->assertArrayHasKey('IPCs', $results['patents'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['patents'][0]['IPCs'][0]);
        $this->assertArrayHasKey('application_citations', $results['patents'][0]);
        $this->assertArrayHasKey('appcit_category', $results['patents'][0]['application_citations'][0]);
        $this->assertArrayHasKey('cited_patents', $results['patents'][0]);
        $this->assertArrayHasKey('cited_patent_category', $results['patents'][0]['cited_patents'][0]);
        $this->assertArrayHasKey('citedby_patents', $results['patents'][0]);
        $this->assertArrayHasKey('citedby_patent_category', $results['patents'][0]['citedby_patents'][0]);
        $this->assertArrayHasKey('uspcs', $results['patents'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['patents'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['patents'][0]['uspcs'][0]);
    }

    public function testStringBegins()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_begins":{"patent_number":"867760"}}';
        $expected = '{"patents":[{"patent_number":"8677600"},{"patent_number":"8677601"},{"patent_number":"8677602"},{"patent_number":"8677603"},{"patent_number":"8677604"},{"patent_number":"8677605"},{"patent_number":"8677606"},{"patent_number":"8677607"},{"patent_number":"8677608"},{"patent_number":"8677609"}],"count":10,"total_found":10}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testLargeReturnSet()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_gte":{"patent_number":"8000000"}}';
        $decodedQueryString = json_decode($queryString, true);
        $decodedFieldString = json_decode('["patent_number","inventor_last_name]"', true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decodedQueryString, $decodedFieldString);
        $this->assertEquals(25, $results['count']);
        $this->assertGreaterThan(5000, $results['total_found']);
    }

    public function testLargeReturnSetLargePage()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_gte":{"patent_number":"8000000"}}';
        $decodedQueryString = json_decode($queryString, true);
        $decodedFieldString = json_decode('["patent_number","inventor_last_name"]', true);
        $decodedOptionString = json_decode('{"per_page":10000}', true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decodedQueryString, $decodedFieldString, null, $decodedOptionString);
        $this->assertGreaterThan(5000, $results['count']);
        $this->assertEquals($results['count'], $results['total_found']);
        $this->assertTrue(isset($results['patents'][0]['inventors']));
    }

    public function testAllFieldsMaxPage()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $query = array();
        $fieldList = array_keys($PATENT_FIELD_SPECS);
        $decodedOptionString = json_decode('{"per_page":10000}', true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $query, $fieldList, null, $decodedOptionString);
        $this->assertGreaterThan(5000, $results['count']);
    }

    public function testNormalInventor()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $queryString = '{"patent_number":"8677523"}';
        $fieldList = array("inventor_id", "inventor_last_name", "patent_number");
        $expected = '{"inventors":[{"inventor_id":"8677523-1","inventor_last_name":"Tsukada","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-2","inventor_last_name":"Kume","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-3","inventor_last_name":"Kawakami","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-4","inventor_last_name":"Nakamura","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-5","inventor_last_name":"Ueda","patents":[{"patent_number":"8677523"}]}],"count":5,"total_found":5}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }
}

