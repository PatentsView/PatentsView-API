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
        $queryString = '{"patent_number":"8407902"}';
        $fieldList = array("inventor_id", "inventor_last_name", "patent_number");
        $expected = '{"inventors":[{"inventor_id":"195402","inventor_last_name":"Naughton","patents":[{"patent_number":"8407902"}]},{"inventor_id":"195403","inventor_last_name":"Limberg","patents":[{"patent_number":"8407902"}]},{"inventor_id":"195404","inventor_last_name":"Scott","patents":[{"patent_number":"8407902"}]}],"count":3,"total_found":3}';
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

    public function testNormalAssignee()
    {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;
        $queryString = '{"_begins":{"patent_number":"840790"}}';
        $fieldList = array("assignee_id", "assignee_organization", "assignee_last_name", "patent_number");
        $expected = '{"assignees":[{"assignee_id":"7010","assignee_organization":"Milwaukee Electric Tool Corporation","assignee_last_name":null,"patents":[{"patent_number":"6588112"},{"patent_number":"6605926"},{"patent_number":"6609924"},{"patent_number":"7868590"},{"patent_number":"7900661"},{"patent_number":"8407902"},{"patent_number":"8418587"},{"patent_number":"8436584"},{"patent_number":"8444353"},{"patent_number":"8450971"},{"patent_number":"D631723"}]},{"assignee_id":"10348","assignee_organization":"Kabushiki Kaisha Topcon","assignee_last_name":null,"patents":[{"patent_number":"4909629"},{"patent_number":"4917480"},{"patent_number":"6587192"},{"patent_number":"6587244"},{"patent_number":"6588898"},{"patent_number":"6611328"},{"patent_number":"6611664"},{"patent_number":"7861423"},{"patent_number":"7865011"},{"patent_number":"7870676"},{"patent_number":"7884923"},{"patent_number":"7895758"},{"patent_number":"7902504"},{"patent_number":"8407904"},{"patent_number":"8408704"},{"patent_number":"8412418"}]},{"assignee_id":"15034","assignee_organization":"Hexagon Metrology, Inc.","assignee_last_name":null,"patents":[{"patent_number":"7895761"},{"patent_number":"8407907"},{"patent_number":"8417370"},{"patent_number":"8429828"},{"patent_number":"8438747"},{"patent_number":"8452564"},{"patent_number":"8453338"}]},{"assignee_id":"15742","assignee_organization":"Mcube, Inc.","assignee_last_name":null,"patents":[{"patent_number":"8407905"},{"patent_number":"8421082"},{"patent_number":"8432005"},{"patent_number":"8437784"}]},{"assignee_id":"19592","assignee_organization":"Cunningham Lindsey U.S., Inc.","assignee_last_name":null,"patents":[{"patent_number":"8407906"}]},{"assignee_id":"23791","assignee_organization":"Leica Geosystems AG","assignee_last_name":null,"patents":[{"patent_number":"6603539"},{"patent_number":"7864303"},{"patent_number":"7876090"},{"patent_number":"7899598"},{"patent_number":"8407903"},{"patent_number":"8422032"},{"patent_number":"8422035"},{"patent_number":"8442080"},{"patent_number":"8456471"}]},{"assignee_id":"24299","assignee_organization":"Mitutoyo Corporation","assignee_last_name":null,"patents":[{"patent_number":"6593757"},{"patent_number":"6594915"},{"patent_number":"6597167"},{"patent_number":"6600231"},{"patent_number":"6600808"},{"patent_number":"6604295"},{"patent_number":"6611786"},{"patent_number":"6614596"},{"patent_number":"7869060"},{"patent_number":"7869622"},{"patent_number":"7869970"},{"patent_number":"7870935"},{"patent_number":"7872733"},{"patent_number":"7873488"},{"patent_number":"7876449"},{"patent_number":"7876456"},{"patent_number":"7877894"},{"patent_number":"7882644"},{"patent_number":"7882723"},{"patent_number":"7882891"},{"patent_number":"7885480"},{"patent_number":"7894079"},{"patent_number":"7895764"},{"patent_number":"8407908"},{"patent_number":"8413348"},{"patent_number":"8416426"},{"patent_number":"8427644"},{"patent_number":"8438746"},{"patent_number":"8441253"},{"patent_number":"8441650"},{"patent_number":"8451334"},{"patent_number":"8456637"}]},{"assignee_id":"28657","assignee_organization":"Robert Bosch GmbH","assignee_last_name":null,"patents":[{"patent_number":"4907560"},{"patent_number":"4907745"},{"patent_number":"4907746"},{"patent_number":"4908535"},{"patent_number":"4908792"},{"patent_number":"4909213"},{"patent_number":"4909446"},{"patent_number":"4911116"},{"patent_number":"4911660"},{"patent_number":"4912601"},{"patent_number":"4912968"},{"patent_number":"4913114"},{"patent_number":"4913115"},{"patent_number":"4913633"},{"patent_number":"4914661"},{"patent_number":"4915350"},{"patent_number":"4916494"},{"patent_number":"4917293"},{"patent_number":"4918389"},{"patent_number":"4918535"},{"patent_number":"4918694"},{"patent_number":"4918868"},{"patent_number":"4919105"},{"patent_number":"4919492"},{"patent_number":"4919497"},{"patent_number":"4920602"},{"patent_number":"4920796"},{"patent_number":"4920938"},{"patent_number":"4921007"},{"patent_number":"4921124"},{"patent_number":"4921288"},{"patent_number":"4921314"},{"patent_number":"4922143"},{"patent_number":"4922714"},{"patent_number":"4922966"},{"patent_number":"4923365"},{"patent_number":"4924359"},{"patent_number":"4924399"},{"patent_number":"4924833"},{"patent_number":"4924835"},{"patent_number":"4924943"},{"patent_number":"4925111"},{"patent_number":"4925499"},{"patent_number":"4926150"},{"patent_number":"4926425"},{"patent_number":"6584834"},{"patent_number":"6584955"},{"patent_number":"6585070"},{"patent_number":"6585171"},{"patent_number":"6586928"},{"patent_number":"6587030"},{"patent_number":"6587039"},{"patent_number":"6587071"},{"patent_number":"6587074"},{"patent_number":"6587138"},{"patent_number":"6587769"},{"patent_number":"6587774"},{"patent_number":"6587776"},{"patent_number":"6588047"},{"patent_number":"6588252"},{"patent_number":"6588257"},{"patent_number":"6588261"},{"patent_number":"6588263"},{"patent_number":"6588380"},{"patent_number":"6588397"},{"patent_number":"6588401"},{"patent_number":"6588405"},{"patent_number":"6588678"},{"patent_number":"6588868"},{"patent_number":"6590460"},{"patent_number":"6590541"},{"patent_number":"6590780"},{"patent_number":"6590838"},{"patent_number":"6591023"},{"patent_number":"6591181"},{"patent_number":"6591612"},{"patent_number":"6591660"},{"patent_number":"6591675"},{"patent_number":"6591811"},{"patent_number":"6591933"},{"patent_number":"6592050"},{"patent_number":"6592440"},{"patent_number":"6592493"},{"patent_number":"6592664"},{"patent_number":"6593018"},{"patent_number":"6593889"},{"patent_number":"6594282"},{"patent_number":"6594558"},{"patent_number":"6594564"},{"patent_number":"6595185"},{"patent_number":"6595192"},{"patent_number":"6595238"},{"patent_number":"6595601"},{"patent_number":"6597086"},{"patent_number":"6597367"},{"patent_number":"6597556"},{"patent_number":"6597974"},{"patent_number":"6597982"},{"patent_number":"6598512"},{"patent_number":"6598590"},{"patent_number":"6598804"},{"patent_number":"6598809"},{"patent_number":"6598811"},{"patent_number":"6598944"},{"patent_number":"6599207"},{"patent_number":"6600103"},{"patent_number":"6600284"},{"patent_number":"6600666"},{"patent_number":"6600901"},{"patent_number":"6600974"},{"patent_number":"6601466"},{"patent_number":"6601545"},{"patent_number":"6601546"},{"patent_number":"6601573"},{"patent_number":"6602470"},{"patent_number":"6603222"},{"patent_number":"6603374"},{"patent_number":"6603392"},{"patent_number":"6603749"},{"patent_number":"6603824"},{"patent_number":"6604024"},{"patent_number":"6604025"},{"patent_number":"6604035"},{"patent_number":"6604041"},{"patent_number":"6604516"},{"patent_number":"6605804"},{"patent_number":"6606547"},{"patent_number":"6606550"},{"patent_number":"6607288"},{"patent_number":"6608458"},{"patent_number":"6608473"},{"patent_number":"6609502"},{"patent_number":"6609860"},{"patent_number":"6611193"},{"patent_number":"6611741"},{"patent_number":"6611759"},{"patent_number":"6611781"},{"patent_number":"6611790"},{"patent_number":"6611988"},{"patent_number":"6612096"},{"patent_number":"6612283"},{"patent_number":"6612289"},{"patent_number":"6612539"},{"patent_number":"6613206"},{"patent_number":"6613207"},{"patent_number":"6613393"},{"patent_number":"6614129"},{"patent_number":"6614230"},{"patent_number":"6614346"},{"patent_number":"6614388"},{"patent_number":"6614390"},{"patent_number":"6614404"},{"patent_number":"6615127"},{"patent_number":"7861519"},{"patent_number":"7861586"},{"patent_number":"7861633"},{"patent_number":"7861796"},{"patent_number":"7863072"},{"patent_number":"7864078"},{"patent_number":"7864889"},{"patent_number":"7865273"},{"patent_number":"7865327"},{"patent_number":"7865356"},{"patent_number":"7866225"},{"patent_number":"7866300"},{"patent_number":"7867065"},{"patent_number":"7867120"},{"patent_number":"7868298"},{"patent_number":"7870657"},{"patent_number":"7870658"},{"patent_number":"7870781"},{"patent_number":"7870846"},{"patent_number":"7870847"},{"patent_number":"7870987"},{"patent_number":"7871018"},{"patent_number":"7871056"},{"patent_number":"7871080"},{"patent_number":"7871224"},{"patent_number":"7871227"},{"patent_number":"7871251"},{"patent_number":"7871313"},{"patent_number":"7872333"},{"patent_number":"7872382"},{"patent_number":"7872388"},{"patent_number":"7872398"},{"patent_number":"7872466"},{"patent_number":"7872487"},{"patent_number":"7872666"},{"patent_number":"7873462"},{"patent_number":"7873463"},{"patent_number":"7873493"},{"patent_number":"7874812"},{"patent_number":"7875094"},{"patent_number":"7875482"},{"patent_number":"7876004"},{"patent_number":"7877177"},{"patent_number":"7877193"},{"patent_number":"7877877"},{"patent_number":"7877883"},{"patent_number":"7877982"},{"patent_number":"7877990"},{"patent_number":"7878061"},{"patent_number":"7878090"},{"patent_number":"7878427"},{"patent_number":"7878481"},{"patent_number":"7878779"},{"patent_number":"7878851"},{"patent_number":"7879479"},{"patent_number":"7879482"},{"patent_number":"7880603"},{"patent_number":"7880679"},{"patent_number":"7880886"},{"patent_number":"7881585"},{"patent_number":"7881842"},{"patent_number":"7881852"},{"patent_number":"7881855"},{"patent_number":"7881857"},{"patent_number":"7881858"},{"patent_number":"7882298"},{"patent_number":"7882759"},{"patent_number":"7882945"},{"patent_number":"7883158"},{"patent_number":"7883355"},{"patent_number":"7883360"},{"patent_number":"7884313"},{"patent_number":"7884616"},{"patent_number":"7886191"},{"patent_number":"7886401"},{"patent_number":"7886578"},{"patent_number":"7886587"},{"patent_number":"7886595"},{"patent_number":"7886712"},{"patent_number":"7886717"},{"patent_number":"7886839"},{"patent_number":"7886880"},{"patent_number":"7887269"},{"patent_number":"7887367"},{"patent_number":"7887942"},{"patent_number":"7888838"},{"patent_number":"7889150"},{"patent_number":"7889231"},{"patent_number":"7889354"},{"patent_number":"7890229"},{"patent_number":"7890245"},{"patent_number":"7890650"},{"patent_number":"7890800"},{"patent_number":"7891043"},{"patent_number":"7891169"},{"patent_number":"7891438"},{"patent_number":"7891530"},{"patent_number":"7891587"},{"patent_number":"7892075"},{"patent_number":"7893592"},{"patent_number":"7893649"},{"patent_number":"7893666"},{"patent_number":"7894043"},{"patent_number":"7894973"},{"patent_number":"7895478"},{"patent_number":"7895702"},{"patent_number":"7895987"},{"patent_number":"7896448"},{"patent_number":"7897913"},{"patent_number":"7898046"},{"patent_number":"7898141"},{"patent_number":"7898412"},{"patent_number":"7898671"},{"patent_number":"7898874"},{"patent_number":"7898987"},{"patent_number":"7899596"},{"patent_number":"7899963"},{"patent_number":"7900596"},{"patent_number":"7900599"},{"patent_number":"7902615"},{"patent_number":"7902626"},{"patent_number":"7902793"},{"patent_number":"7903774"},{"patent_number":"7904232"},{"patent_number":"7904297"},{"patent_number":"8407860"},{"patent_number":"8407901"},{"patent_number":"8407992"},{"patent_number":"8408059"},{"patent_number":"8408259"},{"patent_number":"8408367"},{"patent_number":"8410651"},{"patent_number":"8410900"},{"patent_number":"8412411"},{"patent_number":"8412416"},{"patent_number":"8412789"},{"patent_number":"8412921"},{"patent_number":"8413017"},{"patent_number":"8413430"},{"patent_number":"8413496"},{"patent_number":"8413514"},{"patent_number":"8413637"},{"patent_number":"8413950"},{"patent_number":"8414276"},{"patent_number":"8415040"},{"patent_number":"8415047"},{"patent_number":"8415760"},{"patent_number":"8415924"},{"patent_number":"8416399"},{"patent_number":"8417484"},{"patent_number":"8418525"},{"patent_number":"8418527"},{"patent_number":"8418544"},{"patent_number":"8418548"},{"patent_number":"8418556"},{"patent_number":"8418559"},{"patent_number":"8418591"},{"patent_number":"8418599"},{"patent_number":"8418644"},{"patent_number":"8418675"},{"patent_number":"8418779"},{"patent_number":"8418941"},{"patent_number":"8419590"},{"patent_number":"8419957"},{"patent_number":"8420250"},{"patent_number":"8421167"},{"patent_number":"8421394"},{"patent_number":"8421453"},{"patent_number":"8421487"},{"patent_number":"8421551"},{"patent_number":"8422460"},{"patent_number":"8422534"},{"patent_number":"8422737"},{"patent_number":"8423236"},{"patent_number":"8423248"},{"patent_number":"8423251"},{"patent_number":"8423258"},{"patent_number":"8423325"},{"patent_number":"8423836"},{"patent_number":"8424149"},{"patent_number":"8424186"},{"patent_number":"8424288"},{"patent_number":"8424294"},{"patent_number":"8424366"},{"patent_number":"8424375"},{"patent_number":"8424434"},{"patent_number":"8424615"},{"patent_number":"8424840"},{"patent_number":"8424978"},{"patent_number":"8426046"},{"patent_number":"8426052"},{"patent_number":"8426289"},{"patent_number":"8426930"},{"patent_number":"8427031"},{"patent_number":"8427278"},{"patent_number":"8427801"},{"patent_number":"8428236"},{"patent_number":"8428903"},{"patent_number":"8428906"},{"patent_number":"8429786"},{"patent_number":"8429909"},{"patent_number":"8429966"},{"patent_number":"8429971"},{"patent_number":"8429977"},{"patent_number":"8430078"},{"patent_number":"8430079"},{"patent_number":"8430084"},{"patent_number":"8430183"},{"patent_number":"8430217"},{"patent_number":"8430255"},{"patent_number":"8430568"},{"patent_number":"8432123"},{"patent_number":"8432292"},{"patent_number":"8432444"},{"patent_number":"8432450"},{"patent_number":"8432770"},{"patent_number":"8433464"},{"patent_number":"8433480"},{"patent_number":"8433831"},{"patent_number":"8434456"},{"patent_number":"8434565"},{"patent_number":"8434584"},{"patent_number":"8434591"},{"patent_number":"8435618"},{"patent_number":"8435659"},{"patent_number":"8435660"},{"patent_number":"8435807"},{"patent_number":"8436434"},{"patent_number":"8436561"},{"patent_number":"8436710"},{"patent_number":"8436741"},{"patent_number":"8436764"},{"patent_number":"8437897"},{"patent_number":"8438435"},{"patent_number":"8438740"},{"patent_number":"8439006"},{"patent_number":"8439023"},{"patent_number":"8439126"},{"patent_number":"8439170"},{"patent_number":"8440334"},{"patent_number":"8440336"},{"patent_number":"8441397"},{"patent_number":"8441818"},{"patent_number":"8442723"},{"patent_number":"8442762"},{"patent_number":"8443666"},{"patent_number":"8443668"},{"patent_number":"8443671"},{"patent_number":"8443783"},{"patent_number":"8443949"},{"patent_number":"8443955"},{"patent_number":"8443972"},{"patent_number":"8444406"},{"patent_number":"8445368"},{"patent_number":"8446021"},{"patent_number":"8446073"},{"patent_number":"8446571"},{"patent_number":"8447454"},{"patent_number":"8447457"},{"patent_number":"8447462"},{"patent_number":"8447488"},{"patent_number":"8447511"},{"patent_number":"8447515"},{"patent_number":"8447952"},{"patent_number":"8448042"},{"patent_number":"8448289"},{"patent_number":"8448290"},{"patent_number":"8448342"},{"patent_number":"8448414"},{"patent_number":"8448503"},{"patent_number":"8448508"},{"patent_number":"8448512"},{"patent_number":"8448755"},{"patent_number":"8448757"},{"patent_number":"8448916"},{"patent_number":"8449767"},{"patent_number":"8450002"},{"patent_number":"8450008"},{"patent_number":"8450860"},{"patent_number":"8451135"},{"patent_number":"8452552"},{"patent_number":"8453502"},{"patent_number":"8453687"},{"patent_number":"8453757"},{"patent_number":"8454232"},{"patent_number":"8454411"},{"patent_number":"8454412"},{"patent_number":"8454467"},{"patent_number":"8455134"},{"patent_number":"8456052"},{"patent_number":"8456151"},{"patent_number":"8456315"},{"patent_number":"8457163"},{"patent_number":"8457823"},{"patent_number":"8457843"},{"patent_number":"8457851"},{"patent_number":"8457852"},{"patent_number":"8457879"},{"patent_number":"8457886"},{"patent_number":"D630483"},{"patent_number":"D631717"},{"patent_number":"D631718"},{"patent_number":"D631719"},{"patent_number":"D631720"},{"patent_number":"D631721"},{"patent_number":"D679162"},{"patent_number":"D679234"},{"patent_number":"D679235"},{"patent_number":"D679565"},{"patent_number":"D679566"},{"patent_number":"D679967"},{"patent_number":"D680014"},{"patent_number":"D680451"},{"patent_number":"D680458"},{"patent_number":"D680459"},{"patent_number":"D680460"},{"patent_number":"D682195"},{"patent_number":"D682649"},{"patent_number":"D683202"}]},{"assignee_id":"31721","assignee_organization":"The Gillette Company","assignee_last_name":null,"patents":[{"patent_number":"4914817"},{"patent_number":"4916812"},{"patent_number":"4916817"},{"patent_number":"4916989"},{"patent_number":"4917519"},{"patent_number":"6585881"},{"patent_number":"6589612"},{"patent_number":"6593023"},{"patent_number":"6594904"},{"patent_number":"6596438"},{"patent_number":"6598303"},{"patent_number":"6601303"},{"patent_number":"6612040"},{"patent_number":"7862926"},{"patent_number":"7863535"},{"patent_number":"7867553"},{"patent_number":"7877880"},{"patent_number":"7882640"},{"patent_number":"7892627"},{"patent_number":"7895754"},{"patent_number":"7900359"},{"patent_number":"7900360"},{"patent_number":"7902518"},{"patent_number":"8407900"},{"patent_number":"8413334"},{"patent_number":"8429826"},{"patent_number":"8435433"},{"patent_number":"8435670"},{"patent_number":"8438736"},{"patent_number":"8443519"},{"patent_number":"8448338"},{"patent_number":"8448339"},{"patent_number":"D307443"},{"patent_number":"D307444"},{"patent_number":"D307601"},{"patent_number":"D307919"},{"patent_number":"D476887"},{"patent_number":"D476888"},{"patent_number":"D477158"},{"patent_number":"D477220"},{"patent_number":"D477221"},{"patent_number":"D477465"},{"patent_number":"D478186"},{"patent_number":"D478284"},{"patent_number":"D478285"},{"patent_number":"D478507"},{"patent_number":"D478687"},{"patent_number":"D630377"},{"patent_number":"D630782"},{"patent_number":"D630783"},{"patent_number":"D630797"},{"patent_number":"D631198"},{"patent_number":"D631363"},{"patent_number":"D632516"},{"patent_number":"D633254"},{"patent_number":"D679989"},{"patent_number":"D681938"},{"patent_number":"D681956"},{"patent_number":"D681957"},{"patent_number":"D681958"},{"patent_number":"D683221"},{"patent_number":"D683561"}]}],"count":9,"total_found":9}';
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
}

