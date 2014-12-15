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
        $queryString = '{"patent_number":"8407902"}';
        $fieldList = array("inventor_id", "inventor_last_name", "patent_number");
        $expected = '{"inventors":[{"inventor_id":"8407902-1","inventor_last_name":"Limberg","patents":[{"patent_number":"8407902"}]},{"inventor_id":"8407902-2","inventor_last_name":"Naughton","patents":[{"patent_number":"8407902"}]},{"inventor_id":"8407902-3","inventor_last_name":"Scott","patents":[{"patent_number":"8407902"}]}],"count":3,"total_found":3}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllFieldsInventor()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $query = array();
        $fieldList = array_keys($INVENTOR_FIELD_SPECS);
        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $query, $fieldList);
        $this->assertGreaterThan(10000, $results['total_found']);
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
        $expected = '{"assignees":[{"assignee_id":"362bac12b862ebe59e75015098c476f1","assignee_organization":"Milwaukee Electric Tool Corporation","assignee_last_name":null,"patents":[{"patent_number":"6588112"},{"patent_number":"6605926"},{"patent_number":"6609924"},{"patent_number":"7868590"},{"patent_number":"7900661"},{"patent_number":"8407902"},{"patent_number":"8418587"},{"patent_number":"8436584"},{"patent_number":"8444353"},{"patent_number":"8450971"},{"patent_number":"D631723"}]},{"assignee_id":"7420460c2fef08a859401886242aa678","assignee_organization":"Hexagon Metrology, Inc.","assignee_last_name":null,"patents":[{"patent_number":"7895761"},{"patent_number":"8407907"},{"patent_number":"8417370"},{"patent_number":"8429828"},{"patent_number":"8438747"},{"patent_number":"8452564"},{"patent_number":"8453338"}]},{"assignee_id":"898097d556fe7cb6556ffa4d8e63d4c9","assignee_organization":"Kabushiki Kaisha TOPCON","assignee_last_name":null,"patents":[{"patent_number":"4909629"},{"patent_number":"4917480"},{"patent_number":"6587192"},{"patent_number":"6587244"},{"patent_number":"6588898"},{"patent_number":"6611328"},{"patent_number":"6611664"},{"patent_number":"7861423"},{"patent_number":"7865011"},{"patent_number":"7870676"},{"patent_number":"7884923"},{"patent_number":"7895758"},{"patent_number":"7902504"},{"patent_number":"8407904"},{"patent_number":"8408704"},{"patent_number":"8412418"}]},{"assignee_id":"9800d659117ecce62403f0cb0cc2413c","assignee_organization":"Cunningham Lindsey U.S., Inc.","assignee_last_name":null,"patents":[{"patent_number":"8407906"}]},{"assignee_id":"b81ca8df5e96c09f69b56e48ab369c3d","assignee_organization":"Leica Geosystems AG","assignee_last_name":null,"patents":[{"patent_number":"6603539"},{"patent_number":"7864303"},{"patent_number":"7876090"},{"patent_number":"7899598"},{"patent_number":"8407903"},{"patent_number":"8422032"},{"patent_number":"8422035"},{"patent_number":"8442080"},{"patent_number":"8456471"}]},{"assignee_id":"bc7bea81dbec8115b8c915a33c36feaa","assignee_organization":"Mitutoyo Corporation","assignee_last_name":null,"patents":[{"patent_number":"6593757"},{"patent_number":"6594915"},{"patent_number":"6597167"},{"patent_number":"6600231"},{"patent_number":"6600808"},{"patent_number":"6604295"},{"patent_number":"6611786"},{"patent_number":"6614596"},{"patent_number":"7869060"},{"patent_number":"7869622"},{"patent_number":"7869970"},{"patent_number":"7870935"},{"patent_number":"7872733"},{"patent_number":"7873488"},{"patent_number":"7876449"},{"patent_number":"7876456"},{"patent_number":"7877894"},{"patent_number":"7882644"},{"patent_number":"7882723"},{"patent_number":"7882891"},{"patent_number":"7885480"},{"patent_number":"7894079"},{"patent_number":"7895764"},{"patent_number":"8407908"},{"patent_number":"8413348"},{"patent_number":"8416426"},{"patent_number":"8427644"},{"patent_number":"8438746"},{"patent_number":"8441253"},{"patent_number":"8441650"},{"patent_number":"8451334"},{"patent_number":"8456637"}]},{"assignee_id":"d77c7a8f7eebd4d78bea8993f856b734","assignee_organization":"MCube, Inc.","assignee_last_name":null,"patents":[{"patent_number":"8407905"},{"patent_number":"8421082"},{"patent_number":"8432005"},{"patent_number":"8437784"}]},{"assignee_id":"dd84539d029e9c45a9de61bbb2954c6e","assignee_organization":"Robert Bosch GmbH","assignee_last_name":null,"patents":[{"patent_number":"4907560"},{"patent_number":"4907745"},{"patent_number":"4907746"},{"patent_number":"4908535"},{"patent_number":"4908792"},{"patent_number":"4909213"},{"patent_number":"4909446"},{"patent_number":"4911116"},{"patent_number":"4911660"},{"patent_number":"4912601"},{"patent_number":"4912968"},{"patent_number":"4913114"},{"patent_number":"4913115"},{"patent_number":"4913633"},{"patent_number":"4914661"},{"patent_number":"4915350"},{"patent_number":"4916494"},{"patent_number":"4917293"},{"patent_number":"4918389"},{"patent_number":"4918535"},{"patent_number":"4918694"},{"patent_number":"4918868"},{"patent_number":"4919105"},{"patent_number":"4919492"},{"patent_number":"4919497"},{"patent_number":"4920602"},{"patent_number":"4920796"},{"patent_number":"4920938"},{"patent_number":"4921007"},{"patent_number":"4921124"},{"patent_number":"4921288"},{"patent_number":"4921314"},{"patent_number":"4922143"},{"patent_number":"4922714"},{"patent_number":"4922966"},{"patent_number":"4923365"},{"patent_number":"4924359"},{"patent_number":"4924399"},{"patent_number":"4924833"},{"patent_number":"4924835"},{"patent_number":"4924943"},{"patent_number":"4925111"},{"patent_number":"4925499"},{"patent_number":"4926150"},{"patent_number":"4926425"},{"patent_number":"6584834"},{"patent_number":"6584955"},{"patent_number":"6585070"},{"patent_number":"6585171"},{"patent_number":"6586928"},{"patent_number":"6587030"},{"patent_number":"6587039"},{"patent_number":"6587071"},{"patent_number":"6587074"},{"patent_number":"6587138"},{"patent_number":"6587769"},{"patent_number":"6587774"},{"patent_number":"6587776"},{"patent_number":"6588047"},{"patent_number":"6588252"},{"patent_number":"6588257"},{"patent_number":"6588261"},{"patent_number":"6588263"},{"patent_number":"6588380"},{"patent_number":"6588397"},{"patent_number":"6588401"},{"patent_number":"6588405"},{"patent_number":"6588678"},{"patent_number":"6588868"},{"patent_number":"6590460"},{"patent_number":"6590541"},{"patent_number":"6590780"},{"patent_number":"6590838"},{"patent_number":"6591023"},{"patent_number":"6591181"},{"patent_number":"6591612"},{"patent_number":"6591660"},{"patent_number":"6591675"},{"patent_number":"6591811"},{"patent_number":"6591933"},{"patent_number":"6592050"},{"patent_number":"6592440"},{"patent_number":"6592493"},{"patent_number":"6592664"},{"patent_number":"6593018"},{"patent_number":"6593889"},{"patent_number":"6594282"},{"patent_number":"6594558"},{"patent_number":"6594564"},{"patent_number":"6595185"},{"patent_number":"6595192"},{"patent_number":"6595238"},{"patent_number":"6595601"},{"patent_number":"6597086"},{"patent_number":"6597367"},{"patent_number":"6597556"},{"patent_number":"6597974"},{"patent_number":"6597982"},{"patent_number":"6598512"},{"patent_number":"6598590"},{"patent_number":"6598804"},{"patent_number":"6598809"},{"patent_number":"6598811"},{"patent_number":"6598944"},{"patent_number":"6599207"},{"patent_number":"6600103"},{"patent_number":"6600284"},{"patent_number":"6600666"},{"patent_number":"6600901"},{"patent_number":"6600974"},{"patent_number":"6601466"},{"patent_number":"6601545"},{"patent_number":"6601546"},{"patent_number":"6601573"},{"patent_number":"6602470"},{"patent_number":"6603222"},{"patent_number":"6603374"},{"patent_number":"6603392"},{"patent_number":"6603749"},{"patent_number":"6603824"},{"patent_number":"6604024"},{"patent_number":"6604025"},{"patent_number":"6604035"},{"patent_number":"6604041"},{"patent_number":"6604516"},{"patent_number":"6605804"},{"patent_number":"6606547"},{"patent_number":"6606550"},{"patent_number":"6607288"},{"patent_number":"6608458"},{"patent_number":"6608473"},{"patent_number":"6609502"},{"patent_number":"6609860"},{"patent_number":"6611193"},{"patent_number":"6611741"},{"patent_number":"6611759"},{"patent_number":"6611781"},{"patent_number":"6611790"},{"patent_number":"6611988"},{"patent_number":"6612096"},{"patent_number":"6612283"},{"patent_number":"6612289"},{"patent_number":"6612539"},{"patent_number":"6613206"},{"patent_number":"6613207"},{"patent_number":"6613393"},{"patent_number":"6614129"},{"patent_number":"6614230"},{"patent_number":"6614346"},{"patent_number":"6614388"},{"patent_number":"6614390"},{"patent_number":"6614404"},{"patent_number":"6615127"},{"patent_number":"7861519"},{"patent_number":"7861586"},{"patent_number":"7861633"},{"patent_number":"7861796"},{"patent_number":"7863072"},{"patent_number":"7864078"},{"patent_number":"7864889"},{"patent_number":"7865273"},{"patent_number":"7865327"},{"patent_number":"7865356"},{"patent_number":"7866225"},{"patent_number":"7866300"},{"patent_number":"7867065"},{"patent_number":"7867120"},{"patent_number":"7868298"},{"patent_number":"7870657"},{"patent_number":"7870658"},{"patent_number":"7870781"},{"patent_number":"7870846"},{"patent_number":"7870847"},{"patent_number":"7870987"},{"patent_number":"7871018"},{"patent_number":"7871056"},{"patent_number":"7871080"},{"patent_number":"7871224"},{"patent_number":"7871227"},{"patent_number":"7871251"},{"patent_number":"7871313"},{"patent_number":"7872333"},{"patent_number":"7872382"},{"patent_number":"7872388"},{"patent_number":"7872398"},{"patent_number":"7872466"},{"patent_number":"7872487"},{"patent_number":"7872666"},{"patent_number":"7873462"},{"patent_number":"7873463"},{"patent_number":"7873493"},{"patent_number":"7874812"},{"patent_number":"7875094"},{"patent_number":"7875482"},{"patent_number":"7876004"},{"patent_number":"7877177"},{"patent_number":"7877193"},{"patent_number":"7877877"},{"patent_number":"7877883"},{"patent_number":"7877982"},{"patent_number":"7877990"},{"patent_number":"7878061"},{"patent_number":"7878090"},{"patent_number":"7878427"},{"patent_number":"7878481"},{"patent_number":"7878779"},{"patent_number":"7878851"},{"patent_number":"7879479"},{"patent_number":"7879482"},{"patent_number":"7880603"},{"patent_number":"7880679"},{"patent_number":"7880886"},{"patent_number":"7881585"},{"patent_number":"7881842"},{"patent_number":"7881852"},{"patent_number":"7881855"},{"patent_number":"7881857"},{"patent_number":"7881858"},{"patent_number":"7882298"},{"patent_number":"7882759"},{"patent_number":"7882945"},{"patent_number":"7883158"},{"patent_number":"7883355"},{"patent_number":"7883360"},{"patent_number":"7884313"},{"patent_number":"7884616"},{"patent_number":"7886191"},{"patent_number":"7886401"},{"patent_number":"7886578"},{"patent_number":"7886587"},{"patent_number":"7886595"},{"patent_number":"7886712"},{"patent_number":"7886717"},{"patent_number":"7886839"},{"patent_number":"7886880"},{"patent_number":"7887269"},{"patent_number":"7887367"},{"patent_number":"7887942"},{"patent_number":"7888838"},{"patent_number":"7889150"},{"patent_number":"7889231"},{"patent_number":"7889354"},{"patent_number":"7890229"},{"patent_number":"7890245"},{"patent_number":"7890650"},{"patent_number":"7890800"},{"patent_number":"7891043"},{"patent_number":"7891169"},{"patent_number":"7891438"},{"patent_number":"7891530"},{"patent_number":"7891587"},{"patent_number":"7892075"},{"patent_number":"7893592"},{"patent_number":"7893649"},{"patent_number":"7893666"},{"patent_number":"7894043"},{"patent_number":"7894973"},{"patent_number":"7895478"},{"patent_number":"7895702"},{"patent_number":"7895987"},{"patent_number":"7896448"},{"patent_number":"7897913"},{"patent_number":"7898046"},{"patent_number":"7898141"},{"patent_number":"7898412"},{"patent_number":"7898671"},{"patent_number":"7898874"},{"patent_number":"7898987"},{"patent_number":"7899596"},{"patent_number":"7899963"},{"patent_number":"7900596"},{"patent_number":"7900599"},{"patent_number":"7902615"},{"patent_number":"7902626"},{"patent_number":"7902793"},{"patent_number":"7903774"},{"patent_number":"7904232"},{"patent_number":"7904297"},{"patent_number":"8407860"},{"patent_number":"8407901"},{"patent_number":"8407992"},{"patent_number":"8408059"},{"patent_number":"8408259"},{"patent_number":"8408367"},{"patent_number":"8410651"},{"patent_number":"8410900"},{"patent_number":"8412411"},{"patent_number":"8412416"},{"patent_number":"8412789"},{"patent_number":"8412921"},{"patent_number":"8413017"},{"patent_number":"8413430"},{"patent_number":"8413496"},{"patent_number":"8413514"},{"patent_number":"8413637"},{"patent_number":"8413950"},{"patent_number":"8414276"},{"patent_number":"8415040"},{"patent_number":"8415047"},{"patent_number":"8415760"},{"patent_number":"8415924"},{"patent_number":"8416399"},{"patent_number":"8417484"},{"patent_number":"8418525"},{"patent_number":"8418527"},{"patent_number":"8418544"},{"patent_number":"8418548"},{"patent_number":"8418556"},{"patent_number":"8418559"},{"patent_number":"8418591"},{"patent_number":"8418599"},{"patent_number":"8418644"},{"patent_number":"8418675"},{"patent_number":"8418779"},{"patent_number":"8418941"},{"patent_number":"8419590"},{"patent_number":"8419957"},{"patent_number":"8420250"},{"patent_number":"8421167"},{"patent_number":"8421394"},{"patent_number":"8421453"},{"patent_number":"8421487"},{"patent_number":"8421551"},{"patent_number":"8422460"},{"patent_number":"8422534"},{"patent_number":"8422737"},{"patent_number":"8423236"},{"patent_number":"8423248"},{"patent_number":"8423251"},{"patent_number":"8423258"},{"patent_number":"8423325"},{"patent_number":"8423836"},{"patent_number":"8424149"},{"patent_number":"8424186"},{"patent_number":"8424288"},{"patent_number":"8424294"},{"patent_number":"8424366"},{"patent_number":"8424375"},{"patent_number":"8424434"},{"patent_number":"8424615"},{"patent_number":"8424840"},{"patent_number":"8424978"},{"patent_number":"8426046"},{"patent_number":"8426052"},{"patent_number":"8426289"},{"patent_number":"8426930"},{"patent_number":"8427031"},{"patent_number":"8427278"},{"patent_number":"8427801"},{"patent_number":"8428236"},{"patent_number":"8428903"},{"patent_number":"8428906"},{"patent_number":"8429786"},{"patent_number":"8429909"},{"patent_number":"8429966"},{"patent_number":"8429971"},{"patent_number":"8429977"},{"patent_number":"8430078"},{"patent_number":"8430079"},{"patent_number":"8430084"},{"patent_number":"8430183"},{"patent_number":"8430217"},{"patent_number":"8430255"},{"patent_number":"8430568"},{"patent_number":"8432123"},{"patent_number":"8432292"},{"patent_number":"8432444"},{"patent_number":"8432450"},{"patent_number":"8432770"},{"patent_number":"8433464"},{"patent_number":"8433480"},{"patent_number":"8433831"},{"patent_number":"8434456"},{"patent_number":"8434565"},{"patent_number":"8434584"},{"patent_number":"8434591"},{"patent_number":"8435618"},{"patent_number":"8435659"},{"patent_number":"8435660"},{"patent_number":"8435807"},{"patent_number":"8436434"},{"patent_number":"8436561"},{"patent_number":"8436710"},{"patent_number":"8436741"},{"patent_number":"8436764"},{"patent_number":"8437897"},{"patent_number":"8438435"},{"patent_number":"8438740"},{"patent_number":"8439006"},{"patent_number":"8439023"},{"patent_number":"8439126"},{"patent_number":"8439170"},{"patent_number":"8440334"},{"patent_number":"8440336"},{"patent_number":"8441397"},{"patent_number":"8441818"},{"patent_number":"8442723"},{"patent_number":"8442762"},{"patent_number":"8443666"},{"patent_number":"8443668"},{"patent_number":"8443671"},{"patent_number":"8443783"},{"patent_number":"8443949"},{"patent_number":"8443955"},{"patent_number":"8443972"},{"patent_number":"8444406"},{"patent_number":"8445368"},{"patent_number":"8446021"},{"patent_number":"8446073"},{"patent_number":"8446571"},{"patent_number":"8447454"},{"patent_number":"8447457"},{"patent_number":"8447462"},{"patent_number":"8447488"},{"patent_number":"8447511"},{"patent_number":"8447515"},{"patent_number":"8447952"},{"patent_number":"8448042"},{"patent_number":"8448289"},{"patent_number":"8448290"},{"patent_number":"8448342"},{"patent_number":"8448414"},{"patent_number":"8448503"},{"patent_number":"8448508"},{"patent_number":"8448512"},{"patent_number":"8448755"},{"patent_number":"8448757"},{"patent_number":"8448916"},{"patent_number":"8449767"},{"patent_number":"8450002"},{"patent_number":"8450008"},{"patent_number":"8450860"},{"patent_number":"8451135"},{"patent_number":"8452552"},{"patent_number":"8453502"},{"patent_number":"8453687"},{"patent_number":"8453757"},{"patent_number":"8454232"},{"patent_number":"8454411"},{"patent_number":"8454412"},{"patent_number":"8454467"},{"patent_number":"8455134"},{"patent_number":"8456052"},{"patent_number":"8456151"},{"patent_number":"8456315"},{"patent_number":"8457163"},{"patent_number":"8457823"},{"patent_number":"8457843"},{"patent_number":"8457851"},{"patent_number":"8457852"},{"patent_number":"8457879"},{"patent_number":"8457886"},{"patent_number":"D630483"},{"patent_number":"D631717"},{"patent_number":"D631718"},{"patent_number":"D631719"},{"patent_number":"D631720"},{"patent_number":"D631721"},{"patent_number":"D679162"},{"patent_number":"D679234"},{"patent_number":"D679235"},{"patent_number":"D679565"},{"patent_number":"D679566"},{"patent_number":"D679967"},{"patent_number":"D680014"},{"patent_number":"D680451"},{"patent_number":"D680458"},{"patent_number":"D680459"},{"patent_number":"D680460"},{"patent_number":"D682195"},{"patent_number":"D682649"},{"patent_number":"D683202"}]},{"assignee_id":"f43133d0f9241f8ba1af5b409c78de7c","assignee_organization":"The Gillette Company","assignee_last_name":null,"patents":[{"patent_number":"4914817"},{"patent_number":"4916812"},{"patent_number":"4916817"},{"patent_number":"4916989"},{"patent_number":"4917519"},{"patent_number":"6585881"},{"patent_number":"6589612"},{"patent_number":"6593023"},{"patent_number":"6594904"},{"patent_number":"6596438"},{"patent_number":"6598303"},{"patent_number":"6601303"},{"patent_number":"6612040"},{"patent_number":"7862926"},{"patent_number":"7863535"},{"patent_number":"7867553"},{"patent_number":"7877880"},{"patent_number":"7882640"},{"patent_number":"7892627"},{"patent_number":"7895754"},{"patent_number":"7900359"},{"patent_number":"7900360"},{"patent_number":"7902518"},{"patent_number":"8407900"},{"patent_number":"8413334"},{"patent_number":"8429826"},{"patent_number":"8435433"},{"patent_number":"8435670"},{"patent_number":"8438736"},{"patent_number":"8443519"},{"patent_number":"8448338"},{"patent_number":"8448339"},{"patent_number":"D307443"},{"patent_number":"D307444"},{"patent_number":"D307601"},{"patent_number":"D307919"},{"patent_number":"D476887"},{"patent_number":"D476888"},{"patent_number":"D477158"},{"patent_number":"D477220"},{"patent_number":"D477221"},{"patent_number":"D477465"},{"patent_number":"D478186"},{"patent_number":"D478284"},{"patent_number":"D478285"},{"patent_number":"D478507"},{"patent_number":"D478687"},{"patent_number":"D630377"},{"patent_number":"D630782"},{"patent_number":"D630783"},{"patent_number":"D630797"},{"patent_number":"D631198"},{"patent_number":"D631363"},{"patent_number":"D632516"},{"patent_number":"D633254"},{"patent_number":"D679989"},{"patent_number":"D681938"},{"patent_number":"D681956"},{"patent_number":"D681957"},{"patent_number":"D681958"},{"patent_number":"D683221"},{"patent_number":"D683561"}]}],"count":9,"total_found":9}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllFieldsAssignee()
    {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;
        $query = array();
        $fieldList = array_keys($ASSIGNEE_FIELD_SPECS);
        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $query, $fieldList);
        $this->assertGreaterThan(10000, $results['total_found']);
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
        $fieldList = array("cpc_subsection_id", "cpc_subsection_title", "assignee_organization", "assignee_last_name", "patent_number");
        $expected = '{"cpc_subsections":[{"cpc_subsection_id":"G12","cpc_subsection_title":"Instrument details","assignees":[{"assignee_organization":"Wirth Gallo Messtechnik AG","assignee_last_name":null},{"assignee_organization":"LITEF GmbH","assignee_last_name":null},{"assignee_organization":"Mannesmann AG","assignee_last_name":null},{"assignee_organization":"Electro Scientific Industries, Inc.","assignee_last_name":null}],"patents":[{"patent_number":"4914961"},{"patent_number":"4924749"},{"patent_number":"6601532"},{"patent_number":"6606961"},{"patent_number":"7886449"}]}],"count":1,"total_found":1}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllFieldsCPCSubsection()
    {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;
        $queryString = '{"cpc_subsection_id":"B41"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($CPC_FIELD_SPECS);
        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_found']);
    }

    public function testAllGroupsCPCSubsection()
    {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;
        $queryString = '{"cpc_subsection_id":"B41"}';
        $fieldList = array("ipc_main_group","inventor_last_name","patent_number","uspc_mainclass_id","uspc_subclass_id","assignee_organization","cpc_subsection_id","cpc_subgroup_id");
        $decoded = json_decode($queryString, true);
        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertEquals(2, count($results['cpc_subsections']));
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
        $fieldList = array("uspc_mainclass_id", "uspc_mainclass_title", "assignee_organization", "assignee_last_name", "patent_number");
        $expected = '{"uspc_mainclasses":[{"uspc_mainclass_id":"292","uspc_mainclass_title":"Closure fasteners","assignees":[{"assignee_organization":null,"assignee_last_name":null},{"assignee_organization":"Fiat Auto S.p.A.","assignee_last_name":null},{"assignee_organization":"Truth Incorporated","assignee_last_name":null},{"assignee_organization":"Aisin Seiki Kabushiki Kaisha","assignee_last_name":null},{"assignee_organization":"Mita Industrial Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Trippensee Corporation","assignee_last_name":null},{"assignee_organization":"Liberty Telephone Communications, Inc.","assignee_last_name":null},{"assignee_organization":"Square D Company","assignee_last_name":null},{"assignee_organization":"E. J. Brooks Company","assignee_last_name":null},{"assignee_organization":"Chrysler Corporation","assignee_last_name":null},{"assignee_organization":"Huron\/St. Clair Company","assignee_last_name":null},{"assignee_organization":"The Hartwell Corporation","assignee_last_name":null},{"assignee_organization":"Cleveland Hardware & Forging Co.","assignee_last_name":null},{"assignee_organization":"Kiekert GMBH & Co. Kommanditgesellschaft","assignee_last_name":null},{"assignee_organization":"A. L. Hansen Manufacturing Co.","assignee_last_name":null},{"assignee_organization":"Whirlpool Corporation","assignee_last_name":null},{"assignee_organization":"Grass AG","assignee_last_name":null},{"assignee_organization":"Illinois Tool Works Inc.","assignee_last_name":null},{"assignee_organization":"Phelps-Tointon, Inc.","assignee_last_name":null},{"assignee_organization":"Crenlo, Inc.","assignee_last_name":null},{"assignee_organization":"Nissan Motor Co., Ltd.","assignee_last_name":null},{"assignee_organization":"CTB, Inc.","assignee_last_name":null},{"assignee_organization":"ASC Incorporated","assignee_last_name":null},{"assignee_organization":"BSD Enterprises, Inc.","assignee_last_name":null},{"assignee_organization":"Polyplastics Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Wilkhahn Wilkening & Hahne GmbH & Co. KG","assignee_last_name":null},{"assignee_organization":"The Boeing Company","assignee_last_name":null},{"assignee_organization":"Ohi Seisakusho Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Mobil Service Systems, Inc.","assignee_last_name":null},{"assignee_organization":"Rixson-Firemark Inc.","assignee_last_name":null},{"assignee_organization":"Adams Rite Manufacturing Company","assignee_last_name":null},{"assignee_organization":"Dr. Ing. h.c. F. Porsche AG","assignee_last_name":null},{"assignee_organization":"Rexnord Holdings Inc.","assignee_last_name":null},{"assignee_organization":"Genesis Medical Corporation","assignee_last_name":null},{"assignee_organization":"APIC Corporation","assignee_last_name":null},{"assignee_organization":"The Eastern Company","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"Jason"},{"assignee_organization":"Texas Instruments Incorporated","assignee_last_name":null},{"assignee_organization":"Porsche Aktiengesellschaft","assignee_last_name":null},{"assignee_organization":"CertainTeed Corporation","assignee_last_name":null},{"assignee_organization":"General Motors Corporation","assignee_last_name":null},{"assignee_organization":"Babcock Industries Inc.","assignee_last_name":null},{"assignee_organization":"Federal-Hoffman, Inc.","assignee_last_name":null},{"assignee_organization":"Siemens Aktiengesellschaft","assignee_last_name":null},{"assignee_organization":"Chrysler Motor Corp.","assignee_last_name":null},{"assignee_organization":"Yale Security Inc.","assignee_last_name":null},{"assignee_organization":"Metal Box Corporation","assignee_last_name":null},{"assignee_organization":"Siegenia-Frank KG","assignee_last_name":null},{"assignee_organization":"Robert Bosch GmbH","assignee_last_name":null},{"assignee_organization":"Masco Building Products Corp.","assignee_last_name":null},{"assignee_organization":"Pitney Bowes Inc.","assignee_last_name":null},{"assignee_organization":"Ashland Products Company","assignee_last_name":null},{"assignee_organization":"The Grigoleit Company","assignee_last_name":null},{"assignee_organization":"Won-Door Corporation","assignee_last_name":null},{"assignee_organization":"ITW-Ateco GmbH","assignee_last_name":null},{"assignee_organization":"VSI Corp.","assignee_last_name":null},{"assignee_organization":"Batesville Casket Company, Inc.","assignee_last_name":null},{"assignee_organization":"Taiwan Fu Hsing Industrial Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Waffer Technology Corp.","assignee_last_name":null},{"assignee_organization":"Ferco International Ferrures et Serrures de Batiment","assignee_last_name":null},{"assignee_organization":"Tung Lung Metal Industry Co., Ltd.","assignee_last_name":null},{"assignee_organization":"R.R. Brink Locking Systems, Inc.","assignee_last_name":null},{"assignee_organization":"Steris Inc.","assignee_last_name":null},{"assignee_organization":"Peguform France","assignee_last_name":null},{"assignee_organization":"Norinco","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"Ferguson"},{"assignee_organization":"The Regents of the University of California","assignee_last_name":null},{"assignee_organization":"Valeo Sicurezza Abitacolo S.p.A","assignee_last_name":null},{"assignee_organization":"Modular Systems, Inc.","assignee_last_name":null},{"assignee_organization":"Steelcase Development Corporation","assignee_last_name":null},{"assignee_organization":"Freight Securities, Inc.","assignee_last_name":null},{"assignee_organization":"Paragon Medical, Inc.","assignee_last_name":null},{"assignee_organization":"Mobile Mini, Inc.","assignee_last_name":null},{"assignee_organization":"Meritor Light Vehicle Sytems ( UK) Limited","assignee_last_name":null},{"assignee_organization":"Atom Medical Corporation","assignee_last_name":null},{"assignee_organization":"Strattec Security Corporation","assignee_last_name":null},{"assignee_organization":"Taiwan Semiconductor Manufacturing Co., Ltd","assignee_last_name":null},{"assignee_organization":"Huf Hulsbeck & Furst GmbH & Co. KG","assignee_last_name":null},{"assignee_organization":"Von Duprin, Inc.","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"Glaser"},{"assignee_organization":"International Business Machines Corporation","assignee_last_name":null},{"assignee_organization":"Intel Corporation","assignee_last_name":null},{"assignee_organization":"Volvo Car Corporation","assignee_last_name":null},{"assignee_organization":"Kia Motors Corporation","assignee_last_name":null},{"assignee_organization":"Newfrey LLC","assignee_last_name":null},{"assignee_organization":"EZ Trend Technology Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Interlock Group Limited","assignee_last_name":null},{"assignee_organization":"ArvinMeritor Light Vehicle Systems-France","assignee_last_name":null},{"assignee_organization":"Dr. Ing. h.c. F. Porsche Aktiengesellschaft","assignee_last_name":null},{"assignee_organization":"Adac Plastics Inc.","assignee_last_name":null},{"assignee_organization":"Vinylbilt Shutter Systems Inc.","assignee_last_name":null},{"assignee_organization":"Emerson Electric Co.","assignee_last_name":null},{"assignee_organization":"Fisher Hamilton L.L.C.","assignee_last_name":null},{"assignee_organization":"Atoma International Corp.","assignee_last_name":null},{"assignee_organization":"Wilhelm Karmann GmbH","assignee_last_name":null},{"assignee_organization":"Dell Products L.P.","assignee_last_name":null},{"assignee_organization":"Spacesaver Corporation","assignee_last_name":null},{"assignee_organization":"Hewlett-Packard Development Company, L.P.","assignee_last_name":null},{"assignee_organization":"Fastec Industrial Corp.","assignee_last_name":null},{"assignee_organization":"Mitsui Kinzoku Kogyo Kabushiki Kaisha","assignee_last_name":null},{"assignee_organization":"Xerox Corporation","assignee_last_name":null},{"assignee_organization":"Foster Refrigerator (UK) Limited","assignee_last_name":null},{"assignee_organization":"Harada Industry Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Tenn-Tex Plastics, Inc.","assignee_last_name":null},{"assignee_organization":"Dofasco Inc.","assignee_last_name":null},{"assignee_organization":"Kinyo Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Securitron Magnalock Corporation","assignee_last_name":null},{"assignee_organization":"Genesis Technical Marketing, Inc.","assignee_last_name":null},{"assignee_organization":"Ervos, Inc.","assignee_last_name":null},{"assignee_organization":"Weiser Lock Corporation","assignee_last_name":null},{"assignee_organization":"Richard Fritz GmbH & Co. KG","assignee_last_name":null},{"assignee_organization":"C.R. Laurence Company, Inc.","assignee_last_name":null},{"assignee_organization":"Southco, Inc.","assignee_last_name":null},{"assignee_organization":"Planetary Systems Corporation","assignee_last_name":null},{"assignee_organization":"Command Access Technology, LLC","assignee_last_name":null},{"assignee_organization":"SAAB AB","assignee_last_name":null},{"assignee_organization":"Michelin Recherche et Technique S.A.","assignee_last_name":null},{"assignee_organization":"Hon Hai Precision Ind. Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Valinge Innovation AB","assignee_last_name":null},{"assignee_organization":"Armor Concepts, LLC","assignee_last_name":null},{"assignee_organization":"Samsung Electronics Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Tumble Weed Products, LLC","assignee_last_name":null},{"assignee_organization":"Daktronics, Inc.","assignee_last_name":null},{"assignee_organization":"General Electric Company","assignee_last_name":null},{"assignee_organization":"Assa Abloy Australia Pty Limited","assignee_last_name":null},{"assignee_organization":"Eversafety Precision Industry (Tianjin) Co., Ltd","assignee_last_name":null},{"assignee_organization":"Hardware Specialties, Inc.","assignee_last_name":null},{"assignee_organization":"Sanyo Electric Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Honda Giken Kogyo Kabushiki Kaisha","assignee_last_name":null},{"assignee_organization":"Hitachi Koki Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Checkpoint Systems, Inc.","assignee_last_name":null},{"assignee_organization":"HOPPE Holding AG","assignee_last_name":null},{"assignee_organization":"Toyoda Gosei Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Quantum Corporation","assignee_last_name":null},{"assignee_organization":"Hanchett Entry Systems","assignee_last_name":null},{"assignee_organization":"Smartrac IP B.V.","assignee_last_name":null},{"assignee_organization":"Sargent Manufacturing Company","assignee_last_name":null},{"assignee_organization":"National Manufacturing Co.","assignee_last_name":null},{"assignee_organization":"Smiths Group PLC","assignee_last_name":null},{"assignee_organization":"Terahop Networks, Inc.","assignee_last_name":null},{"assignee_organization":"Cessna Aircraft Company","assignee_last_name":null},{"assignee_organization":"Incyte Corporation","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"Anastasiadis"},{"assignee_organization":"Knorr-Bremse Ges.m.b.H.","assignee_last_name":null},{"assignee_organization":"I-Tek Metal Mfg. Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Fujitsu Limited","assignee_last_name":null},{"assignee_organization":"HIP Innovations, LLC","assignee_last_name":null},{"assignee_organization":"Poong Won Industry Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Savio S.p.A.","assignee_last_name":null},{"assignee_organization":"Assa Abloy Sicherheitstechnik GmbH","assignee_last_name":null},{"assignee_organization":"Asustek Computer Inc.","assignee_last_name":null},{"assignee_organization":"Lockheed Martin Corporation","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"Jones"},{"assignee_organization":"Northland Products, Inc.","assignee_last_name":null},{"assignee_organization":"Paccar Inc.","assignee_last_name":null},{"assignee_organization":"Master Lock Company LLC","assignee_last_name":null},{"assignee_organization":"Honda Motor Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Intier Automotive Closures Inc","assignee_last_name":null},{"assignee_organization":"FIH (Hong Kong) Limited","assignee_last_name":null},{"assignee_organization":"JPM SAS","assignee_last_name":null},{"assignee_organization":"Quanta Computer Inc.","assignee_last_name":null},{"assignee_organization":"Nissan North America, Inc.","assignee_last_name":null},{"assignee_organization":"Vision Industries Group, Inc.","assignee_last_name":null},{"assignee_organization":"Kimberly-Clark Worldwide, Inc.","assignee_last_name":null},{"assignee_organization":"Cmech (Guangzhou) Industrial, Ltd.","assignee_last_name":null},{"assignee_organization":"Fu Tai Hua Industry (Shenzhen) Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Humax Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Townsteel, Inc.","assignee_last_name":null},{"assignee_organization":"Avibank Manufacturing, Inc.","assignee_last_name":null},{"assignee_organization":"Bauer Products, Inc.","assignee_last_name":null},{"assignee_organization":"GM Global Technology Operations LLC","assignee_last_name":null},{"assignee_organization":"Casio Hitachi Mobile Communications Co., Ltd.","assignee_last_name":null},{"assignee_organization":"GemTek Technology Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Thase Enterprise Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Hong Fu Jin Precision Industry (ShenZhen) Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Julius Blum GmbH","assignee_last_name":null},{"assignee_organization":"Ricoh Company, Ltd.","assignee_last_name":null},{"assignee_organization":"Fidlock GmbH","assignee_last_name":null},{"assignee_organization":"A.L. Hansen Manufacturing Co.","assignee_last_name":null},{"assignee_organization":"Schlage Lock Company","assignee_last_name":null},{"assignee_organization":"Samsonite IP Holdings S.a.r.l.","assignee_last_name":null},{"assignee_organization":"Saf-T-Cab, Inc.","assignee_last_name":null},{"assignee_organization":"Kiekert Aktiengesellschaft","assignee_last_name":null},{"assignee_organization":"NSCore, Inc.","assignee_last_name":null},{"assignee_organization":"Caterpillar SARL","assignee_last_name":null},{"assignee_organization":"VSI, LLC","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"David"},{"assignee_organization":"Toyota Auto Body Co., Ltd.","assignee_last_name":null},{"assignee_organization":"Yazaki Corporation","assignee_last_name":null},{"assignee_organization":"Harting Electronics GmbH & Co. KG","assignee_last_name":null},{"assignee_organization":"Magna Car Top Systems GmbH","assignee_last_name":null},{"assignee_organization":"Innovative Labs LLC","assignee_last_name":null},{"assignee_organization":"Newell Operating Company","assignee_last_name":null},{"assignee_organization":"Stanley Black & Decker, Inc.","assignee_last_name":null},{"assignee_organization":"Chi Mei Communication Systems, Inc.","assignee_last_name":null},{"assignee_organization":"Tubsa Automocion, S.L.","assignee_last_name":null},{"assignee_organization":"Ford Global Technologies, LLC","assignee_last_name":null},{"assignee_organization":"Multitest Elektronische Systeme GmbH","assignee_last_name":null},{"assignee_organization":"S.P.E.P. Acquisition Corp.","assignee_last_name":null},{"assignee_organization":"Electrolux Home Products, Inc.","assignee_last_name":null},{"assignee_organization":null,"assignee_last_name":"Bare"},{"assignee_organization":"The Mason Company, LLC","assignee_last_name":null},{"assignee_organization":"Inteva Products LLC.","assignee_last_name":null}],"patents":[{"patent_number":"4907303"},{"patent_number":"4907429"},{"patent_number":"4907831"},{"patent_number":"4907832"},{"patent_number":"4907833"},{"patent_number":"4908659"},{"patent_number":"4908728"},{"patent_number":"4908966"},{"patent_number":"4909053"},{"patent_number":"4909551"},{"patent_number":"4909552"},{"patent_number":"4910831"},{"patent_number":"4910915"},{"patent_number":"4911312"},{"patent_number":"4911348"},{"patent_number":"4911485"},{"patent_number":"4911486"},{"patent_number":"4911487"},{"patent_number":"4911488"},{"patent_number":"4911489"},{"patent_number":"4911508"},{"patent_number":"4912727"},{"patent_number":"4912950"},{"patent_number":"4913475"},{"patent_number":"4913476"},{"patent_number":"4913477"},{"patent_number":"4913478"},{"patent_number":"4913479"},{"patent_number":"4913486"},{"patent_number":"4914525"},{"patent_number":"4914554"},{"patent_number":"4914779"},{"patent_number":"4915034"},{"patent_number":"4915326"},{"patent_number":"4915428"},{"patent_number":"4915429"},{"patent_number":"4915430"},{"patent_number":"4915431"},{"patent_number":"4915432"},{"patent_number":"4915443"},{"patent_number":"4915557"},{"patent_number":"4915913"},{"patent_number":"4916926"},{"patent_number":"4917244"},{"patent_number":"4917412"},{"patent_number":"4917413"},{"patent_number":"4917414"},{"patent_number":"4917415"},{"patent_number":"4917416"},{"patent_number":"4917417"},{"patent_number":"4917418"},{"patent_number":"4917419"},{"patent_number":"4917420"},{"patent_number":"4917421"},{"patent_number":"4917422"},{"patent_number":"4917423"},{"patent_number":"4917424"},{"patent_number":"4917425"},{"patent_number":"4918866"},{"patent_number":"4918954"},{"patent_number":"4919277"},{"patent_number":"4919463"},{"patent_number":"4919464"},{"patent_number":"4920773"},{"patent_number":"4921033"},{"patent_number":"4921122"},{"patent_number":"4921285"},{"patent_number":"4921286"},{"patent_number":"4921287"},{"patent_number":"4921288"},{"patent_number":"4921289"},{"patent_number":"4921290"},{"patent_number":"4922734"},{"patent_number":"4922817"},{"patent_number":"4923230"},{"patent_number":"4923231"},{"patent_number":"4923232"},{"patent_number":"4923233"},{"patent_number":"4923325"},{"patent_number":"4924929"},{"patent_number":"4925041"},{"patent_number":"4925072"},{"patent_number":"4925221"},{"patent_number":"4925222"},{"patent_number":"4925223"},{"patent_number":"4925230"},{"patent_number":"6584642"},{"patent_number":"6584816"},{"patent_number":"6584817"},{"patent_number":"6585301"},{"patent_number":"6585302"},{"patent_number":"6585303"},{"patent_number":"6585943"},{"patent_number":"6588242"},{"patent_number":"6588483"},{"patent_number":"6588525"},{"patent_number":"6588809"},{"patent_number":"6588810"},{"patent_number":"6588811"},{"patent_number":"6588812"},{"patent_number":"6588813"},{"patent_number":"6588971"},{"patent_number":"6591453"},{"patent_number":"6591555"},{"patent_number":"6591641"},{"patent_number":"6591643"},{"patent_number":"6592000"},{"patent_number":"6592155"},{"patent_number":"6592156"},{"patent_number":"6592157"},{"patent_number":"6592365"},{"patent_number":"6592511"},{"patent_number":"6594861"},{"patent_number":"6594871"},{"patent_number":"6595075"},{"patent_number":"6595561"},{"patent_number":"6595562"},{"patent_number":"6595563"},{"patent_number":"6595564"},{"patent_number":"6595582"},{"patent_number":"6595593"},{"patent_number":"6595605"},{"patent_number":"6597566"},{"patent_number":"6598330"},{"patent_number":"6598350"},{"patent_number":"6598436"},{"patent_number":"6598437"},{"patent_number":"6598440"},{"patent_number":"6598896"},{"patent_number":"6598909"},{"patent_number":"6598910"},{"patent_number":"6598911"},{"patent_number":"6598912"},{"patent_number":"6598913"},{"patent_number":"6599183"},{"patent_number":"6601353"},{"patent_number":"6601418"},{"patent_number":"6601726"},{"patent_number":"6601881"},{"patent_number":"6601882"},{"patent_number":"6601883"},{"patent_number":"6601884"},{"patent_number":"6601885"},{"patent_number":"6601906"},{"patent_number":"6603655"},{"patent_number":"6604764"},{"patent_number":"6604797"},{"patent_number":"6606241"},{"patent_number":"6606889"},{"patent_number":"6607221"},{"patent_number":"6607222"},{"patent_number":"6607223"},{"patent_number":"6607224"},{"patent_number":"6608265"},{"patent_number":"6609338"},{"patent_number":"6609400"},{"patent_number":"6609583"},{"patent_number":"6609736"},{"patent_number":"6609737"},{"patent_number":"6609738"},{"patent_number":"6609739"},{"patent_number":"6612139"},{"patent_number":"6612625"},{"patent_number":"6612626"},{"patent_number":"6612627"},{"patent_number":"6612628"},{"patent_number":"6612629"},{"patent_number":"6612630"},{"patent_number":"7861475"},{"patent_number":"7861563"},{"patent_number":"7861976"},{"patent_number":"7862091"},{"patent_number":"7862092"},{"patent_number":"7862317"},{"patent_number":"7864518"},{"patent_number":"7866110"},{"patent_number":"7866113"},{"patent_number":"7866181"},{"patent_number":"7866712"},{"patent_number":"7866713"},{"patent_number":"7866714"},{"patent_number":"7869198"},{"patent_number":"7870647"},{"patent_number":"7870754"},{"patent_number":"7870770"},{"patent_number":"7870772"},{"patent_number":"7871112"},{"patent_number":"7871113"},{"patent_number":"7874188"},{"patent_number":"7874597"},{"patent_number":"7874598"},{"patent_number":"7874599"},{"patent_number":"7874609"},{"patent_number":"7874972"},{"patent_number":"7878032"},{"patent_number":"7878033"},{"patent_number":"7878034"},{"patent_number":"7878035"},{"patent_number":"7878558"},{"patent_number":"7878559"},{"patent_number":"7878560"},{"patent_number":"7878561"},{"patent_number":"7883123"},{"patent_number":"7883124"},{"patent_number":"7883125"},{"patent_number":"7883126"},{"patent_number":"7883127"},{"patent_number":"7883128"},{"patent_number":"7883306"},{"patent_number":"7885064"},{"patent_number":"7887105"},{"patent_number":"7887106"},{"patent_number":"7887107"},{"patent_number":"7889036"},{"patent_number":"7889488"},{"patent_number":"7891136"},{"patent_number":"7895998"},{"patent_number":"7896407"},{"patent_number":"7900978"},{"patent_number":"7900979"},{"patent_number":"7900980"},{"patent_number":"7900981"},{"patent_number":"7903423"},{"patent_number":"8407840"},{"patent_number":"8407942"},{"patent_number":"8408607"},{"patent_number":"8408608"},{"patent_number":"8408609"},{"patent_number":"8408610"},{"patent_number":"8408611"},{"patent_number":"8408612"},{"patent_number":"8409741"},{"patent_number":"8414036"},{"patent_number":"8414037"},{"patent_number":"8414038"},{"patent_number":"8414039"},{"patent_number":"8414040"},{"patent_number":"8416092"},{"patent_number":"8418872"},{"patent_number":"8419079"},{"patent_number":"8419080"},{"patent_number":"8419081"},{"patent_number":"8419082"},{"patent_number":"8419083"},{"patent_number":"8419084"},{"patent_number":"8419085"},{"patent_number":"8419086"},{"patent_number":"8419087"},{"patent_number":"8419088"},{"patent_number":"8419089"},{"patent_number":"8419114"},{"patent_number":"8424926"},{"patent_number":"8424927"},{"patent_number":"8424928"},{"patent_number":"8424929"},{"patent_number":"8424930"},{"patent_number":"8424931"},{"patent_number":"8424932"},{"patent_number":"8424933"},{"patent_number":"8424934"},{"patent_number":"8424935"},{"patent_number":"8424936"},{"patent_number":"8424984"},{"patent_number":"8428506"},{"patent_number":"8430434"},{"patent_number":"8430435"},{"patent_number":"8430436"},{"patent_number":"8434201"},{"patent_number":"8434251"},{"patent_number":"8434335"},{"patent_number":"8434794"},{"patent_number":"8434795"},{"patent_number":"8434796"},{"patent_number":"8434797"},{"patent_number":"8434798"},{"patent_number":"8438887"},{"patent_number":"8438888"},{"patent_number":"8439409"},{"patent_number":"8439410"},{"patent_number":"8443553"},{"patent_number":"8443638"},{"patent_number":"8443640"},{"patent_number":"8443737"},{"patent_number":"8444189"},{"patent_number":"8444190"},{"patent_number":"8444207"},{"patent_number":"8446278"},{"patent_number":"8448298"},{"patent_number":"8448483"},{"patent_number":"8448996"},{"patent_number":"8448997"},{"patent_number":"8448998"},{"patent_number":"8448999"},{"patent_number":"8449000"},{"patent_number":"8449001"},{"patent_number":"8449002"},{"patent_number":"8449003"},{"patent_number":"8449004"},{"patent_number":"8449005"},{"patent_number":"8449006"},{"patent_number":"8449400"},{"patent_number":"8451087"},{"patent_number":"8453606"},{"patent_number":"8453835"},{"patent_number":"8454060"},{"patent_number":"8454061"},{"patent_number":"8454062"},{"patent_number":"8454063"},{"patent_number":"8456825"}]}],"count":1,"total_found":1}';
        $decoded = json_decode($queryString, true);
        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $decoded, $fieldList);
        $encoded = json_encode($results);
        $this->assertEquals($expected, $encoded);
    }

    public function testAllFieldsUSPCMainclass()
    {
        global $USPC_ENTITY_SPECS;
        global $USPC_FIELD_SPECS;
        $queryString = '{"uspc_mainclass_id":"292"}';
        $decoded = json_decode($queryString, true);
        $fieldList = array_keys($USPC_FIELD_SPECS);
        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $decoded, $fieldList);
        $this->assertGreaterThanOrEqual(1, $results['total_found']);
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

}

