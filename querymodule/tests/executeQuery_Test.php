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
        $queryString = '{"_or":[{"patent_number":"8202600"},{"patent_number":"8202601"}]}';
        $expected = '{"patents":[{"patent_number":"8202600"},{"patent_number":"8202601"}],"count":2,"total_found":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testNormalWithFieldList()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_or":[{"patent_number":"8202600"},{"patent_number":"8202601"}]}';
        $fieldList = array("patent_id", "patent_type", "patent_number", "patent_title", "inventor_last_name", "assignee_last_name");
        $expected = '{"patents":[{"patent_id":"8202600","patent_type":"utility","patent_number":"8202600","patent_title":"Artificial leather, base to be used in the leather, and processes for production of both","inventors":[{"inventor_last_name":"Okada"},{"inventor_last_name":"Ichihashi"}],"assignees":[{"assignee_last_name":null}]},{"patent_id":"8202601","patent_type":"utility","patent_number":"8202601","patent_title":"Honeycomb structure and manufacturing method of the honeycomb structure","inventors":[{"inventor_last_name":"Ohno"},{"inventor_last_name":"Kunieda"},{"inventor_last_name":"Ido"}],"assignees":[{"assignee_last_name":null}]}],"count":2,"total_found":2}';
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
        $queryString = '{"_text_phrase":{"patent_title":"cement composite"}}';
        $expected = '{"patents":[{"patent_title":"Treatment for cement composite articles"}],"count":1,"total_found":1}';
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
        $queryString = '{"_text_any":{"patent_title":"cement composite"}}';
        $expected = '{"patents":[{"patent_title":"Composite building panel"},{"patent_title":"Composite hollow fiber type separation membranes processes for the preparation thereof and their use"},{"patent_title":"Method of making a composite panel of a foam material"},{"patent_title":"Method for making a composite component using a transverse tape"},{"patent_title":"Preparation of a silicone rubber-polyester composite products"},{"patent_title":"Composite membrane"},{"patent_title":"Composite film"},{"patent_title":"Composite polymer\/desiccant coatings for IC encapsulation"},{"patent_title":"Composite materials having improved fracture toughness"},{"patent_title":"Composite sign post"},{"patent_title":"Light metallic composite material and method for producing thereof"},{"patent_title":"Process of producing a composite membrane"},{"patent_title":"High strength cured cement article and process for manufacturing the same"},{"patent_title":"Unsaturated copolymer resin composite"},{"patent_title":"Composite vacuum evaporation coil"},{"patent_title":"Polymer composite bat"},{"patent_title":"Composite bone marrow graft material with method and kit"},{"patent_title":"Concrete comprising organic fibres dispersed in a cement matrix, concrete cement matrix and premixes"},{"patent_title":"Composite membrane and method for making the same"},{"patent_title":"Production of composite mouldings"},{"patent_title":"Method and apparatus for making composite parts"},{"patent_title":"Curable liquid sealant used as vacuum bag in composite manufacturing"},{"patent_title":"Method and apparatus for manufacturing ceramic-based composite member"},{"patent_title":"Method for fabricating ceramic matrix composite"},{"patent_title":"Light scattering sheet, light scattering composite sheet, and liquid crystal display"}],"count":25,"total_found":79}';
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
        $queryString = '{"_text_all":{"patent_title":"cement composite"}}';
        $expected = '{"patents":[{"patent_title":"Treatment for cement composite articles"}],"count":1,"total_found":1}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllGroups()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"patent_number":"8202600"}';
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
        $queryString = '{"patent_number":"8202600"}';
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
        $queryString = '{"_begins":{"patent_number":"820260"}}';
        $expected = '{"patents":[{"patent_number":"8202600"},{"patent_number":"8202601"},{"patent_number":"8202602"},{"patent_number":"8202603"},{"patent_number":"8202604"},{"patent_number":"8202605"},{"patent_number":"8202606"},{"patent_number":"8202607"},{"patent_number":"8202608"},{"patent_number":"8202609"}],"count":10,"total_found":10}';
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
        $this->assertGreaterThanOrEqual($results['count'], $results['total_found']);
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

