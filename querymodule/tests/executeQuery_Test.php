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
        $queryString = '{"_or":[{"patent_number":"8407900"},{"patent_number":"8407901"}]}';
        $expected = '{"patents":[{"patent_number":"8407900"},{"patent_number":"8407901"}],"count":2,"total_found":2}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testNormalWithFieldList()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_or":[{"patent_number":"8407900"},{"patent_number":"8407901"}]}';
        $fieldList = array("patent_id", "patent_type", "patent_number", "patent_title", "inventor_last_name", "assignee_last_name");
        $expected = '{"patents":[{"patent_id":"8407900","patent_type":"utility","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings","inventors":[{"inventor_last_name":"Johnson"}],"assignees":[{"assignee_last_name":null}]},{"patent_id":"8407901","patent_type":"utility","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool","inventors":[{"inventor_last_name":"Oberheim"}],"assignees":[{"assignee_last_name":null}]}],"count":2,"total_found":2}';
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
        $queryString = '{"_text_phrase":{"patent_title":"lead wire"}}';
        $expected = '{"patents":[{"patent_title":"Protective structure for terminal lead wire of coil"},{"patent_title":"Fused lead wire for ballast protection"},{"patent_title":"Lead wire implanting apparatus"},{"patent_title":"Spindle motor having connecting mechanism connecting lead wire and circuit board, and storage disk drive having the same"},{"patent_title":"Surface heating system and method using heating cables and a single feed cold lead wire"}],"count":5,"total_found":5}';
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
        $queryString = '{"_text_any":{"patent_title":"lead wire"}}';
        $expected = '{"patents":[{"patent_title":"Tandem projectiles connected by a wire"},{"patent_title":"Wire saw"},{"patent_title":"Method of bonding gold or gold alloy wire to lead tin solder"},{"patent_title":"Wire harness mounting structure for motor vehicle door"},{"patent_title":"Lead-in wire for compact fluorescent lamps"},{"patent_title":"Electrical devices command system, single wire bus and smart dual controller arrangement therefor"},{"patent_title":"Apparatus for cutting a lead"},{"patent_title":"Method of forming lead terminals on aluminum or aluminum alloy cables"},{"patent_title":"Method of controlled rod or wire rolling of alloy steel"},{"patent_title":"Heat wire airflow meter"},{"patent_title":"Bonding wire ball formation"},{"patent_title":"Method and apparatus for preparing a bonding wire"},{"patent_title":"Wire pulling guide"},{"patent_title":"Secondary lead production"},{"patent_title":"Lead-oxide paste mix for battery grids and method of preparation"},{"patent_title":"Lead frame"},{"patent_title":"Wire harness apparatus for instrument panel"},{"patent_title":"Method and apparatus for optical fiber\/wire payout simulation"},{"patent_title":"Hybrid lead trim die"},{"patent_title":"Pacemaker wire dressing"},{"patent_title":"Method and apparatus for forming wire mesh cages"},{"patent_title":"Wire connect and disconnect indicator"},{"patent_title":"Method for the refining of lead"},{"patent_title":"Apparatus and method for the production of oxides of lead"},{"patent_title":"Multiple lead probe for integrated circuits in wafer form"}],"count":25,"total_found":588}';
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
        $queryString = '{"_text_all":{"patent_title":"lead wire"}}';
        $expected = '{"patents":[{"patent_title":"Method of bonding gold or gold alloy wire to lead tin solder"},{"patent_title":"Lead-in wire for compact fluorescent lamps"},{"patent_title":"Protective structure for terminal lead wire of coil"},{"patent_title":"Fused lead wire for ballast protection"},{"patent_title":"Lead wire implanting apparatus"},{"patent_title":"Sealing structure for wire lead-out hole"},{"patent_title":"Spindle motor having connecting mechanism connecting lead wire and circuit board, and storage disk drive having the same"},{"patent_title":"Stimulation and sensing lead with non-coiled wire construction"},{"patent_title":"Surface heating system and method using heating cables and a single feed cold lead wire"}],"count":9,"total_found":9}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllGroups()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"patent_number":"8407900"}';
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
        $queryString = '{"patent_number":"8407900"}';
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
        $queryString = '{"_begins":{"patent_number":"840790"}}';
        $expected = '{"patents":[{"patent_number":"8407900"},{"patent_number":"8407901"},{"patent_number":"8407902"},{"patent_number":"8407903"},{"patent_number":"8407904"},{"patent_number":"8407905"},{"patent_number":"8407906"},{"patent_number":"8407907"},{"patent_number":"8407908"},{"patent_number":"8407909"}],"count":10,"total_found":10}';
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
        $queryString = '{"patent_number":"8407900"}';
        $fieldList = array("inventor_id", "inventor_last_name", "patent_number");
        $expected = '{"inventors":[{"inventor_id":"8677523-1","inventor_last_name":"Tsukada","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-2","inventor_last_name":"Kume","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-3","inventor_last_name":"Kawakami","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-4","inventor_last_name":"Nakamura","patents":[{"patent_number":"8677523"},{"patent_number":"8677524"}]},{"inventor_id":"8677523-5","inventor_last_name":"Ueda","patents":[{"patent_number":"8677523"}]}],"count":5,"total_found":5}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }
}

