<?php
require_once dirname(__FILE__) . '/../app/executeQuery.php';
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
        $expected = '{"patents":[{"patent_id":"8407900","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings"},{"patent_id":"8407901","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool"}],"count":2,"total_patent_count":2}';
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
        $expected = '{"patents":[{"patent_id":"8407900","patent_type":"utility","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings","inventors":[{"inventor_last_name":"Johnson"}],"assignees":[{"assignee_last_name":null}]},{"patent_id":"8407901","patent_type":"utility","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool","inventors":[{"inventor_last_name":"Oberheim"}],"assignees":[{"assignee_last_name":null}]}],"count":2,"total_patent_count":2}';
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
        $expected = '{"patents":null,"count":0,"total_patent_count":0}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    /**
     * @expectedException Exceptions\ParsingException
     */
    public function testInvalidQueryField()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"inventor_lastknown_latitude":"abc"}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
    }

    public function testTextPhrase()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_text_phrase":{"patent_title":"lead wire"}}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $this->assertGreaterThanOrEqual(180, $results['total_patent_count']);
        $this->assertLessThanOrEqual(400, $results['total_patent_count']);
        foreach ($results['patents'] as $patent)
            $this->assertFalse(stristr($patent['patent_title'], 'lead wire') === false);
    }

    public function testTextAny()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_text_any":{"patent_title":"lead wire"}}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $this->assertGreaterThanOrEqual(24000, $results['total_patent_count']);
        $this->assertLessThanOrEqual(30000, $results['total_patent_count']);
        foreach ($results['patents'] as $patent) {
            $this->assertFalse((stristr($patent['patent_title'], 'lead') === false) and (stristr($patent['patent_title'], 'wire') === false));
        }
    }

    public function testTextAll()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_text_all":{"patent_title":"lead wire"}}';
        $expected = '{"patents":[{"patent_title":"Wire lead straightening device"},{"patent_title":"Small magnet wire to lead wire termination"},{"patent_title":"Lead wire forming apparatus for incandescent filaments"},{"patent_title":"Ceramic envelope plug and lead wire and seal"},{"patent_title":"Heart pacer lead wire with break-away needle"},{"patent_title":"Wire lead bonding tool"},{"patent_title":"Motor lead guide and lead wire attaching means"},{"patent_title":"Hermetic lead wire"},{"patent_title":"Anode and cathode lead wire assembly for solid electrolytic capacitors"},{"patent_title":"Stator coil winding and lead wire connection"},{"patent_title":"Crimping and wire lead insertion machine"},{"patent_title":"Forced air furnace motor lead wire protection"},{"patent_title":"Wire lead and solder removal tool"},{"patent_title":"Crimping and wire lead insertion machine having improved insertion means"},{"patent_title":"Wire straightening mechanism for wire lead production apparatus"},{"patent_title":"Wire gathering mechanism for wire lead production apparatus"},{"patent_title":"Lead wire cutter"},{"patent_title":"Wire lead clamping mechanism for wire lead production apparatus"},{"patent_title":"Anode and cathode lead wire assembly for solid electrolytic capacitors"},{"patent_title":"Lamp lead to wire attachment for integral string sets"},{"patent_title":"Tungsten halogen lamp having lead-in wire comprising tantalum alloy"},{"patent_title":"Component lead wire cutting equipment"},{"patent_title":"Horn loudspeaker with particular suspension and lead wire passage"},{"patent_title":"Lead wire forming apparatus for electric parts"},{"patent_title":"Wire lead forming machine"}],"count":25,"total_patent_count":280}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $this->assertGreaterThanOrEqual(200, $results['total_patent_count']);
        $this->assertLessThanOrEqual(500, $results['total_patent_count']);
        foreach ($results['patents'] as $patent) {
            $this->assertFalse(stristr($patent['patent_title'], 'lead') === false);
            $this->assertFalse(stristr($patent['patent_title'], 'wire') === false);
        }
    }

    public function testAllGroups()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"patent_number":"8407900"}';
        $fieldList = array("ipc_main_group","appcit_category","inventor_last_name","cited_patent_category","uspc_mainclass_id","uspc_subclass_id","assignee_organization","citedby_patent_number");
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
        $this->assertArrayHasKey('citedby_patents', $results['patents'][0]);
        $this->assertArrayHasKey('citedby_patent_number', $results['patents'][0]['citedby_patents'][0]);
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
        $this->assertArrayHasKey('cpcs', $results['patents'][0]);
        $this->assertArrayHasKey('cpc_subsection_id', $results['patents'][0]['cpcs'][0]);
        $this->assertArrayHasKey('nbers', $results['patents'][0]);
        $this->assertArrayHasKey('nber_subcategory_id', $results['patents'][0]['nbers'][0]);
    }

    public function testStringBegins()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_begins":{"patent_number":"840790"}}';
        $expected = '{"patents":[{"patent_id":"8407900","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings"},{"patent_id":"8407901","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool"},{"patent_id":"8407902","patent_number":"8407902","patent_title":"Reciprocating power tool having a counterbalance device"},{"patent_id":"8407903","patent_number":"8407903","patent_title":"Rotating construction laser, in particular a self-compensating rotating construction laser, and method for measuring a tilt of an axis of rotation of a construction laser"},{"patent_id":"8407904","patent_number":"8407904","patent_title":"Rotary laser beam emitter"},{"patent_id":"8407905","patent_number":"8407905","patent_title":"Multiple magneto meters using Lorentz force for integrated systems"},{"patent_id":"8407906","patent_number":"8407906","patent_title":"Window frame deflection measurement device and method of use"},{"patent_id":"8407907","patent_number":"8407907","patent_title":"CMM with modular functionality"},{"patent_id":"8407908","patent_number":"8407908","patent_title":"Profile measurement apparatus"},{"patent_id":"8407909","patent_number":"8407909","patent_title":"Tape measure carrier and gauge"}],"count":10,"total_patent_count":10}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decoded, null);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testLargeReturnSet()
    {
        #Slow when result count limit > 100,000
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_gte":{"patent_number":"8000000"}}';
        $decodedQueryString = json_decode($queryString, true);
        $decodedFieldString = json_decode('["patent_number","inventor_last_name]"', true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decodedQueryString, $decodedFieldString);
        $this->assertEquals(25, $results['count']);
        $this->assertGreaterThan(5000, $results['total_patent_count']);
    }

    public function testLargeReturnSetLargePage()
    {
        #Slow when result count limit > 100,000
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        global $config;
        $queryString = '{"_gte":{"patent_number":"8000000"}}';
        $decodedQueryString = json_decode($queryString, true);
        $decodedFieldString = json_decode('["patent_number","inventor_last_name"]', true);
        $decodedOptionString = json_decode('{"per_page":'.$config->getMaxPageSize().'}', true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decodedQueryString, $decodedFieldString, null, $decodedOptionString);
        $this->assertGreaterThan(5000, $results['count']);
        $this->assertGreaterThanOrEqual($results['count'], $results['total_patent_count']);
        $this->assertTrue(isset($results['patents'][0]['inventors']));
    }

    #Out of memory when per_page is 10000. Ok at 2000: PHP Fatal error:  Allowed memory size of 1073741824 bytes exhausted (tried to allocate 64 bytes) in C:\Greg\SourceCode\PatentsView-API\querymodule\app\convertDBResultsToNestedStructure.php on line 77
    #Still takes 45s
    public function testAllFieldsMaxPage()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $queryString = '{"_gte":{"patent_number":"8000000"}}';
        $decodedQueryString = json_decode($queryString, true);
        $fieldList = array_keys($PATENT_FIELD_SPECS);
        $decodedOptionString = json_decode('{"per_page":2000}', true);
        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $decodedQueryString, $fieldList, null, $decodedOptionString);
        $this->assertGreaterThan(500, $results['count']);
    }

    public function testNormalInventor()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $queryString = '{"patent_number":"8407902"}';
        $fieldList = array("inventor_id", "inventor_last_name", "patent_number");
        $sort = array(array("inventor_last_name" => "asc"));
        $options = array("matched_subentities_only"=>"false");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $decoded, $fieldList, $sort, $options);
        $this->assertEquals(3, count($results['inventors']));
        $this->assertEquals('Limberg', $results['inventors'][0]['inventor_last_name']);
        $this->assertGreaterThanOrEqual(5, count($results['inventors'][0]['patents']));
        $this->assertEquals('Naughton', $results['inventors'][1]['inventor_last_name']);
        $this->assertGreaterThanOrEqual(12, count($results['inventors'][1]['patents']));
        $this->assertEquals('Scott', $results['inventors'][2]['inventor_last_name']);
        $this->assertGreaterThanOrEqual(21, count($results['inventors'][2]['patents']));
    }

    public function testAllFieldsInventor()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $queryString = '{"patent_number":"8407902"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($INVENTOR_FIELD_SPECS);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThan(1, $results['total_inventor_count']);
    }

    public function testAllGroupsInventor()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $queryString = '{"patent_number":"8407902"}';
        $fieldList = array("ipc_main_group","inventor_last_name","patent_number","uspc_mainclass_id","uspc_subclass_id","assignee_organization","location_city","coinventor_id","year_num_patents_for_inventor");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(3, count($results['inventors']));
        $this->assertArrayHasKey('patents', $results['inventors'][0]);
        $this->assertArrayHasKey('patent_number', $results['inventors'][0]['patents'][0]);
        $this->assertArrayHasKey('assignees', $results['inventors'][0]);
        $this->assertArrayHasKey('assignee_organization', $results['inventors'][0]['assignees'][0]);
        $this->assertArrayHasKey('coinventors', $results['inventors'][0]);
        $this->assertArrayHasKey('coinventor_id', $results['inventors'][0]['coinventors'][0]);
        $this->assertArrayHasKey('locations', $results['inventors'][0]);
        $this->assertArrayHasKey('location_city', $results['inventors'][0]['locations'][0]);
        $this->assertArrayHasKey('IPCs', $results['inventors'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['inventors'][0]['IPCs'][0]);
        $this->assertArrayHasKey('uspcs', $results['inventors'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['inventors'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['inventors'][0]['uspcs'][0]);
        $this->assertArrayHasKey('years', $results['inventors'][0]);
        $this->assertArrayHasKey('year_num_patents_for_inventor', $results['inventors'][0]['years'][0]);
    }

    public function testNormalAssignee()
    {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;
        $queryString = '{"_begins":{"patent_number":"840790"}}';
        $fieldList = array("assignee_id", "assignee_organization", "assignee_last_name", "patent_number");
        $sort = array(array("assignee_organization" => "asc", "assignee_last_name" => "asc"));
        $options = array("matched_subentities_only"=>"false");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $decoded, $fieldList, $sort, $options);
        $this->assertEquals(9, count($results['assignees']));
        $this->assertEquals('Cunningham Lindsey U.S., Inc.', $results['assignees'][0]['assignee_organization']);
        $this->assertGreaterThanOrEqual(1, count($results['assignees'][0]['patents']));
        $this->assertEquals(strtolower('Kabushiki Kaisha TOPCON'),strtolower( $results['assignees'][2]['assignee_organization']));

        $this->assertGreaterThanOrEqual(917, count($results['assignees'][2]['patents']));
    }

    public function testAllFieldsAssignee()
    {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;
        $queryString = '{"patent_number":"8407902"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($ASSIGNEE_FIELD_SPECS);
        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_assignee_count']);
    }

    public function testAllGroupsAssignee()
    {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;
        $queryString = '{"patent_number":"8407902"}';
        $fieldList = array("ipc_main_group","inventor_last_name","patent_number","uspc_mainclass_id","uspc_subclass_id","assignee_organization","location_city","year_num_patents_for_assignee");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['assignees']));
        $this->assertArrayHasKey('patents', $results['assignees'][0]);
        $this->assertArrayHasKey('patent_number', $results['assignees'][0]['patents'][0]);
        $this->assertArrayHasKey('inventors', $results['assignees'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['assignees'][0]['inventors'][0]);
        $this->assertArrayHasKey('locations', $results['assignees'][0]);
        $this->assertArrayHasKey('location_city', $results['assignees'][0]['locations'][0]);
        $this->assertArrayHasKey('IPCs', $results['assignees'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['assignees'][0]['IPCs'][0]);
        $this->assertArrayHasKey('uspcs', $results['assignees'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['assignees'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['assignees'][0]['uspcs'][0]);
        $this->assertArrayHasKey('years', $results['assignees'][0]);
        $this->assertArrayHasKey('year_num_patents_for_assignee', $results['assignees'][0]['years'][0]);
    }

    public function testNormalCPCSubsection()
    {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;
        $queryString = '{"cpc_subsection_id":"G12"}';
        $fieldList = array("cpc_subsection_id", "cpc_subsection_title", "cpc_subgroup_id", "assignee_organization", "assignee_last_name", "patent_number");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals('Instrument details', $results['cpc_subsections'][0]['cpc_subsection_title']);
        foreach ($results['cpc_subsections'][0]['cpc_subgroups'] as $subgroup)
            $this->assertStringStartsWith('G12', $subgroup['cpc_subgroup_id']);
    }

    public function testAllFieldsCPCSubsection()
    {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;
        $queryString = '{"cpc_subsection_id":"G12"}'; #This had the least patents using it
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($CPC_FIELD_SPECS);
        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_cpc_subsection_count']);
    }


    #ToDo: Super slow when using B41 rather than G12. Probably due to sheer size of records involved.
    public function testAllGroupsCPCSubsection()
    {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;
        $queryString = '{"cpc_subsection_id":"G12"}';
        $fieldList = array("ipc_main_group","inventor_last_name","patent_number","uspc_mainclass_id","uspc_subclass_id","assignee_organization","cpc_subsection_id","cpc_subgroup_id","year_id","year_num_patents_for_cpc_subsection");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['cpc_subsections']));
        $this->assertArrayHasKey('cpc_subgroups', $results['cpc_subsections'][0]);
        $this->assertArrayHasKey('cpc_subgroup_id', $results['cpc_subsections'][0]['cpc_subgroups'][0]);
        $this->assertArrayHasKey('patents', $results['cpc_subsections'][0]);
        $this->assertArrayHasKey('patent_number', $results['cpc_subsections'][0]['patents'][0]);
        $this->assertArrayHasKey('inventors', $results['cpc_subsections'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['cpc_subsections'][0]['inventors'][0]);
        $this->assertArrayHasKey('IPCs', $results['cpc_subsections'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['cpc_subsections'][0]['IPCs'][0]);
        $this->assertArrayHasKey('uspcs', $results['cpc_subsections'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['cpc_subsections'][0]['uspcs'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['cpc_subsections'][0]['uspcs'][0]);
        $this->assertArrayHasKey('years', $results['cpc_subsections'][0]);
        $this->assertArrayHasKey('year_num_patents_for_cpc_subsection', $results['cpc_subsections'][0]['years'][0]);
    }

    public function testNormalUSPCMainclass()
    {
        global $USPC_ENTITY_SPECS;
        global $USPC_FIELD_SPECS;
        $queryString = '{"uspc_mainclass_id":"292"}';
        $fieldList = array("uspc_mainclass_id", "uspc_mainclass_title", "uspc_subclass_id", "assignee_organization", "assignee_last_name", "patent_number");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals('Closure fasteners', $results['uspc_mainclasses'][0]['uspc_mainclass_title']);
        foreach ($results['uspc_mainclasses'][0]['uspc_subclasses'] as $subclass)
            $this->assertStringStartsWith('292', $subclass['uspc_subclass_id']);
    }

    public function testAllFieldsUSPCMainclass()
    {
        global $USPC_ENTITY_SPECS;
        global $USPC_FIELD_SPECS;
        $queryString = '{"uspc_mainclass_id":"292"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($USPC_FIELD_SPECS);
        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_uspc_mainclass_count']);
    }

    public function testAllGroupsUSPCMainclass()
    {
        global $USPC_ENTITY_SPECS;
        global $USPC_FIELD_SPECS;
        $queryString = '{"uspc_mainclass_id":"292"}';
        $fieldList = array("ipc_main_group","inventor_last_name","patent_number","uspc_mainclass_id","uspc_subclass_id","assignee_organization","cpc_subsection_id","year_num_patents_for_uspc_mainclass");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['uspc_mainclasses']));
        $this->assertArrayHasKey('uspc_mainclass_id', $results['uspc_mainclasses'][0]);
        $this->assertArrayHasKey('uspc_subclass_id', $results['uspc_mainclasses'][0]['uspc_subclasses'][0]);
        $this->assertArrayHasKey('cpcs', $results['uspc_mainclasses'][0]);
        $this->assertArrayHasKey('cpc_subsection_id', $results['uspc_mainclasses'][0]['cpcs'][0]);
        $this->assertArrayHasKey('patents', $results['uspc_mainclasses'][0]);
        $this->assertArrayHasKey('patent_number', $results['uspc_mainclasses'][0]['patents'][0]);
        $this->assertArrayHasKey('inventors', $results['uspc_mainclasses'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['uspc_mainclasses'][0]['inventors'][0]);
        $this->assertArrayHasKey('IPCs', $results['uspc_mainclasses'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['uspc_mainclasses'][0]['IPCs'][0]);
        $this->assertArrayHasKey('years', $results['uspc_mainclasses'][0]);
        $this->assertArrayHasKey('year_num_patents_for_uspc_mainclass', $results['uspc_mainclasses'][0]['years'][0]);
    }

    public function testNormalLocation()
    {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;
        $queryString = '{"location_id":"39.3762|-77.1547"}';
        $fieldList = array("location_id", "location_key_id", "location_city", "uspc_subclass_id", "assignee_last_name", "patent_number", "inventor_last_name");
        $fieldList = array("location_id", "location_key_id", "location_city", "inventor_last_name", "patent_number", "assignee_organization");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals('Mt. Airy', $results['locations'][0]['location_city']);
    }

    public function testAllFieldsLocation()
    {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;
        $queryString = '{"location_id":"39.3762|-77.1547"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($LOCATION_FIELD_SPECS);
        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_location_count']);
    }

    public function testAllGroupsLocation()
    {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;
        $queryString = '{"location_id":"39.3762|-77.1547"}';
        $fieldList = array("location_id","ipc_main_group","inventor_last_name","patent_number","uspc_mainclass_id","assignee_organization","cpc_subsection_id");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['locations']));
        $this->assertArrayHasKey('location_id', $results['locations'][0]);
        $this->assertArrayHasKey('uspcs', $results['locations'][0]);
        $this->assertArrayHasKey('uspc_mainclass_id', $results['locations'][0]['uspcs'][0]);
        $this->assertArrayHasKey('cpcs', $results['locations'][0]);
        $this->assertArrayHasKey('cpc_subsection_id', $results['locations'][0]['cpcs'][0]);
        $this->assertArrayHasKey('patents', $results['locations'][0]);
        $this->assertArrayHasKey('patent_number', $results['locations'][0]['patents'][0]);
        $this->assertArrayHasKey('inventors', $results['locations'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['locations'][0]['inventors'][0]);
        $this->assertArrayHasKey('IPCs', $results['locations'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['locations'][0]['IPCs'][0]);
    }

    public function testNormalNBERSubcategory()
    {
        global $NBER_ENTITY_SPECS;
        global $NBER_FIELD_SPECS;
        $queryString = '{"nber_subcategory_id":"11"}';
        $fieldList = array("nber_subcategory_id", "nber_subcategory_title", "nber_category_id", "assignee_organization", "assignee_last_name", "patent_number");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals('Agriculture,Food,Textiles', $results['nber_subcategories'][0]['nber_subcategory_title']);
    }

    public function testAllFieldsNBERSubcategory()
    {
        global $NBER_ENTITY_SPECS;
        global $NBER_FIELD_SPECS;
        $queryString = '{"_and":[{"nber_subcategory_id":"11"},{"assignee_city":"atlanta"}]}';
        #$queryString = '{"nber_subcategory_id":"11"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($NBER_FIELD_SPECS);
        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_nber_subcategory_count']);
    }

    public function testAllGroupsNBERSubcategory()
    {
        global $NBER_ENTITY_SPECS;
        global $NBER_FIELD_SPECS;
        $queryString = '{"nber_subcategory_id":"11"}';
        $fieldList = array("nber_subcategory_id","inventor_last_name","patent_number","uspc_mainclass_id","assignee_organization","cpc_subsection_id","year_num_patents_for_nber_subcategory","ipc_main_group");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(1, count($results['nber_subcategories']));
        $this->assertArrayHasKey('nber_subcategory_id', $results['nber_subcategories'][0]);
        $this->assertArrayHasKey('cpcs', $results['nber_subcategories'][0]);
        $this->assertArrayHasKey('cpc_subsection_id', $results['nber_subcategories'][0]['cpcs'][0]);
        $this->assertArrayHasKey('patents', $results['nber_subcategories'][0]);
        $this->assertArrayHasKey('patent_number', $results['nber_subcategories'][0]['patents'][0]);
        $this->assertArrayHasKey('inventors', $results['nber_subcategories'][0]);
        $this->assertArrayHasKey('inventor_last_name', $results['nber_subcategories'][0]['inventors'][0]);
        $this->assertArrayHasKey('IPCs', $results['nber_subcategories'][0]);
        $this->assertArrayHasKey('ipc_main_group', $results['nber_subcategories'][0]['IPCs'][0]);
        $this->assertArrayHasKey('years', $results['nber_subcategories'][0]);
        $this->assertArrayHasKey('year_num_patents_for_nber_subcategory', $results['nber_subcategories'][0]['years'][0]);
    }

}

