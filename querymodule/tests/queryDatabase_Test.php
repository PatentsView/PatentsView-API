<?php
require_once dirname(__FILE__) . '/../app/DatabaseQuery.php';
require_once dirname(__FILE__) . '/../app/execute_query.php';
require_once dirname(__FILE__) . '/../app/entitySpecs.php';

class queryDatabase_Test extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $config;
        $config = Config::getInstance();
    }

    public function testQueryDatabase()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.number like '840790%'";
        $whereFieldsUsed = array('patent_id');
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs);
        $expected = json_decode('{"patents":[{"patent_id":"8407900","patent_type":"utility","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings"},{"patent_id":"8407901","patent_type":"utility","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool"},{"patent_id":"8407902","patent_type":"utility","patent_number":"8407902","patent_title":"Reciprocating power tool having a counterbalance device"},{"patent_id":"8407903","patent_type":"utility","patent_number":"8407903","patent_title":"Rotating construction laser, in particular a self-compensating rotating construction laser, and method for measuring a tilt of an axis of rotation of a construction laser"},{"patent_id":"8407904","patent_type":"utility","patent_number":"8407904","patent_title":"Rotary laser beam emitter"},{"patent_id":"8407905","patent_type":"utility","patent_number":"8407905","patent_title":"Multiple magneto meters using Lorentz force for integrated systems"},{"patent_id":"8407906","patent_type":"utility","patent_number":"8407906","patent_title":"Window frame deflection measurement device and method of use"},{"patent_id":"8407907","patent_type":"utility","patent_number":"8407907","patent_title":"CMM with modular functionality"},{"patent_id":"8407908","patent_type":"utility","patent_number":"8407908","patent_title":"Profile measurement apparatus"},{"patent_id":"8407909","patent_type":"utility","patent_number":"8407909","patent_title":"Tape measure carrier and gauge"}],"inventors":[{"patent_id":"8407900","inventor_last_name":"Johnson","inventor_id":"195400"},{"patent_id":"8407901","inventor_last_name":"Oberheim","inventor_id":"195401"},{"patent_id":"8407902","inventor_last_name":"Naughton","inventor_id":"195402"},{"patent_id":"8407902","inventor_last_name":"Limberg","inventor_id":"195403"},{"patent_id":"8407902","inventor_last_name":"Scott","inventor_id":"195404"},{"patent_id":"8407903","inventor_last_name":"Koleszar","inventor_id":"195405"},{"patent_id":"8407903","inventor_last_name":"Winistoerfer","inventor_id":"195406"},{"patent_id":"8407904","inventor_last_name":"Kamizono","inventor_id":"102157"},{"patent_id":"8407904","inventor_last_name":"Hayashi","inventor_id":"195407"},{"patent_id":"8407905","inventor_last_name":"Hsu","inventor_id":"195408"},{"patent_id":"8407905","inventor_last_name":"Flannery","inventor_id":"195409"},{"patent_id":"8407905","inventor_last_name":"Van Der Heide","inventor_id":"195410"},{"patent_id":"8407906","inventor_last_name":"Heyer","inventor_id":"195411"},{"patent_id":"8407907","inventor_last_name":"Tait","inventor_id":"195412"},{"patent_id":"8407908","inventor_last_name":"Noda","inventor_id":"195413"},{"patent_id":"8407909","inventor_last_name":"Lindsay","inventor_id":"195414"}],"assignees":[{"patent_id":"8407900","assignee_last_name":null,"assignee_id":"31721"},{"patent_id":"8407901","assignee_last_name":null,"assignee_id":"28657"},{"patent_id":"8407902","assignee_last_name":null,"assignee_id":"7010"},{"patent_id":"8407903","assignee_last_name":null,"assignee_id":"23791"},{"patent_id":"8407904","assignee_last_name":null,"assignee_id":"10348"},{"patent_id":"8407905","assignee_last_name":null,"assignee_id":"15742"},{"patent_id":"8407906","assignee_last_name":null,"assignee_id":"19592"},{"patent_id":"8407907","assignee_last_name":null,"assignee_id":"15034"},{"patent_id":"8407908","assignee_last_name":null,"assignee_id":"24299"},{"patent_id":"8407909","assignee_last_name":null,"assignee_id":null}]}', true);
        $this->assertEquals($expected, $results);
    }

    public function testQueryDatabase_EmptyWhere()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = '';
        $whereFieldsUsed = array();
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $options = array('per_page'=>10000);
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $this->assertGreaterThan(6000, count($results['patents']));
    }

    public function testQueryDatabaseWithPaging()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.patent_id like '84%'";
        $whereFieldsUsed = array('patent_id');
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $options = array('page'=>1, 'per_page'=>25);
        $resultsFirst = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $options = array('page'=>11, 'per_page'=>25);
        $resultsSecond = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $options = array('page'=>6, 'per_page'=>50);
        $resultsThird = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $this->assertNotEquals($resultsFirst['patents'][0],$resultsSecond['patents'][0]);
        $this->assertEquals($resultsSecond['patents'][0],$resultsThird['patents'][0]);
        $patentIds = array();
        foreach ($resultsSecond['patents'] as $row) $patentIds[$row['patent_id']] = 1;
        $this->assertEquals(25, count($patentIds));
        $patentIds = array();
        foreach ($resultsThird['patents'] as $row) $patentIds[$row['patent_id']] = 1;
        $this->assertEquals(50, count($patentIds));
    }

    public function testQueryDatabaseWithSorting()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.number like '840790%'";
        $whereFieldsUsed = array('patent_id');
        $sort = array(array('patent_title'=>'asc'));
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, $sort);
        $expected = json_decode('{"patents":[{"patent_id":"8407907","patent_type":"utility","patent_number":"8407907","patent_title":"CMM with modular functionality"},{"patent_id":"8407901","patent_type":"utility","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool"},{"patent_id":"8407905","patent_type":"utility","patent_number":"8407905","patent_title":"Multiple magneto meters using Lorentz force for integrated systems"},{"patent_id":"8407908","patent_type":"utility","patent_number":"8407908","patent_title":"Profile measurement apparatus"},{"patent_id":"8407902","patent_type":"utility","patent_number":"8407902","patent_title":"Reciprocating power tool having a counterbalance device"},{"patent_id":"8407904","patent_type":"utility","patent_number":"8407904","patent_title":"Rotary laser beam emitter"},{"patent_id":"8407903","patent_type":"utility","patent_number":"8407903","patent_title":"Rotating construction laser, in particular a self-compensating rotating construction laser, and method for measuring a tilt of an axis of rotation of a construction laser"},{"patent_id":"8407900","patent_type":"utility","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings"},{"patent_id":"8407909","patent_type":"utility","patent_number":"8407909","patent_title":"Tape measure carrier and gauge"},{"patent_id":"8407906","patent_type":"utility","patent_number":"8407906","patent_title":"Window frame deflection measurement device and method of use"}],"inventors":[{"patent_id":"8407907","inventor_last_name":"Tait","inventor_id":"195412"},{"patent_id":"8407901","inventor_last_name":"Oberheim","inventor_id":"195401"},{"patent_id":"8407905","inventor_last_name":"Hsu","inventor_id":"195408"},{"patent_id":"8407905","inventor_last_name":"Flannery","inventor_id":"195409"},{"patent_id":"8407905","inventor_last_name":"Van Der Heide","inventor_id":"195410"},{"patent_id":"8407908","inventor_last_name":"Noda","inventor_id":"195413"},{"patent_id":"8407902","inventor_last_name":"Naughton","inventor_id":"195402"},{"patent_id":"8407902","inventor_last_name":"Limberg","inventor_id":"195403"},{"patent_id":"8407902","inventor_last_name":"Scott","inventor_id":"195404"},{"patent_id":"8407904","inventor_last_name":"Kamizono","inventor_id":"102157"},{"patent_id":"8407904","inventor_last_name":"Hayashi","inventor_id":"195407"},{"patent_id":"8407903","inventor_last_name":"Koleszar","inventor_id":"195405"},{"patent_id":"8407903","inventor_last_name":"Winistoerfer","inventor_id":"195406"},{"patent_id":"8407900","inventor_last_name":"Johnson","inventor_id":"195400"},{"patent_id":"8407909","inventor_last_name":"Lindsay","inventor_id":"195414"},{"patent_id":"8407906","inventor_last_name":"Heyer","inventor_id":"195411"}],"assignees":[{"patent_id":"8407907","assignee_last_name":null,"assignee_id":"15034"},{"patent_id":"8407901","assignee_last_name":null,"assignee_id":"28657"},{"patent_id":"8407905","assignee_last_name":null,"assignee_id":"15742"},{"patent_id":"8407908","assignee_last_name":null,"assignee_id":"24299"},{"patent_id":"8407902","assignee_last_name":null,"assignee_id":"7010"},{"patent_id":"8407904","assignee_last_name":null,"assignee_id":"10348"},{"patent_id":"8407903","assignee_last_name":null,"assignee_id":"23791"},{"patent_id":"8407900","assignee_last_name":null,"assignee_id":"31721"},{"patent_id":"8407909","assignee_last_name":null,"assignee_id":null},{"patent_id":"8407906","assignee_last_name":null,"assignee_id":"19592"}]}', true);
        $this->assertEquals($expected, $results);
    }

    /**
     * @expectedException ErrorException
     */   public function testQueryDatabaseWithInvalidSorting()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.patent_number like '820260%'";
        $whereFieldsUsed = array('patent_id');
        $sort = array(array('inventor_id'=>'asc'));
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, $sort);
    }

    public  function testQueryDatabaseAllFields()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $selectFieldSpecs = $PATENT_FIELD_SPECS;
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, null, array(), $selectFieldSpecs, null);
        $memUsed = memory_get_usage();
        if (count($results['patents']) < 25) {
            $this->assertEquals($dbQuery->getTotalFound(), count($results['patents']));
        }
        else {
            $this->assertEquals(25, count($results['patents']));
            $this->assertGreaterThanOrEqual(25, $dbQuery->getTotalFound());
        }
    }

    public  function testQueryDatabaseAllFieldsMaxPageSize()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $selectFieldSpecs = $PATENT_FIELD_SPECS;
        $options = array('page'=>1, 'per_page'=>10000);
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, null, array(), $selectFieldSpecs, null, $options);
        $memUsed = memory_get_usage();
        if (count($results['patents']) < 10000) {
            $this->assertEquals($dbQuery->getTotalFound(), count($results['patents']));
        }
        else {
            $this->assertEquals(10000, count($results['patents']));
            $this->assertGreaterThanOrEqual(10000, $dbQuery->getTotalFound());
        }
    }

    public function testQueryDatabaseInventorCombo1()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $whereClause = "patent.date >= '2007-01-04'";
        $whereFieldsUsed = array('patent_date');
        $sort = array(array('inventor_last_name'=>'desc'));
        $selectFieldsSpecs = array(
            'patent_number' => $INVENTOR_FIELD_SPECS['patent_number'],
            'patent_date' => $INVENTOR_FIELD_SPECS['patent_date'],
            'inventor_id' => $INVENTOR_FIELD_SPECS['inventor_id'],
            'inventor_last_name' => $INVENTOR_FIELD_SPECS['inventor_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, $sort);
        $expected = json_decode('{"inventors":[{"inventor_id":"185862","inventor_last_name":"\u00d8ygard"},{"inventor_id":"114293","inventor_last_name":"\u00d8yen"},{"inventor_id":"207641","inventor_last_name":"\u00d8yen"},{"inventor_id":"124560","inventor_last_name":"\u00d8stergaard"},{"inventor_id":"161461","inventor_last_name":"\u00d8stergaard"},{"inventor_id":"201865","inventor_last_name":"\u00d8stergaard"},{"inventor_id":"261744","inventor_last_name":"\u00d8stergaard"},{"inventor_id":"170599","inventor_last_name":"\u00d8stensen"},{"inventor_id":"272278","inventor_last_name":"\u00d8rnbo"},{"inventor_id":"139757","inventor_last_name":"Zyznowski"},{"inventor_id":"231230","inventor_last_name":"Zyzelewski"},{"inventor_id":"116140","inventor_last_name":"Zyzak"},{"inventor_id":"181839","inventor_last_name":"Zywno"},{"inventor_id":"292686","inventor_last_name":"Zyuban"},{"inventor_id":"137800","inventor_last_name":"Zyss"},{"inventor_id":"227854","inventor_last_name":"Zysk"},{"inventor_id":"207648","inventor_last_name":"Zyoubouji"},{"inventor_id":"249323","inventor_last_name":"Zynda"},{"inventor_id":"222786","inventor_last_name":"Zymroz Jr."},{"inventor_id":"292986","inventor_last_name":"Zylka"},{"inventor_id":"144258","inventor_last_name":"Zygmunt"},{"inventor_id":"227482","inventor_last_name":"Zygmunt"},{"inventor_id":"204998","inventor_last_name":"Zydek"},{"inventor_id":"122716","inventor_last_name":"Zychowski"},{"inventor_id":"275711","inventor_last_name":"Zyburt"}],"patents":[{"inventor_id":"185862","patent_number":"7900137","patent_date":"2011-03-01","patent_id":"7900137"},{"inventor_id":"114293","patent_number":"7866697","patent_date":"2011-01-11","patent_id":"7866697"},{"inventor_id":"207641","patent_number":"8413711","patent_date":"2013-04-09","patent_id":"8413711"},{"inventor_id":"124560","patent_number":"7871110","patent_date":"2011-01-18","patent_id":"7871110"},{"inventor_id":"161461","patent_number":"7888061","patent_date":"2011-02-15","patent_id":"7888061"},{"inventor_id":"201865","patent_number":"8410732","patent_date":"2013-04-02","patent_id":"8410732"},{"inventor_id":"261744","patent_number":"8441138","patent_date":"2013-05-14","patent_id":"8441138"},{"inventor_id":"170599","patent_number":"7892522","patent_date":"2011-02-22","patent_id":"7892522"},{"inventor_id":"272278","patent_number":"8446850","patent_date":"2013-05-21","patent_id":"8446850"},{"inventor_id":"139757","patent_number":"7877947","patent_date":"2011-02-01","patent_id":"7877947"},{"inventor_id":"231230","patent_number":"8425569","patent_date":"2013-04-23","patent_id":"8425569"},{"inventor_id":"116140","patent_number":"7867529","patent_date":"2011-01-11","patent_id":"7867529"},{"inventor_id":"181839","patent_number":"7897942","patent_date":"2011-03-01","patent_id":"7897942"},{"inventor_id":"292686","patent_number":"8458501","patent_date":"2013-06-04","patent_id":"8458501"},{"inventor_id":"137800","patent_number":"7877015","patent_date":"2011-01-25","patent_id":"7877015"},{"inventor_id":"227854","patent_number":"8423704","patent_date":"2013-04-16","patent_id":"8423704"},{"inventor_id":"207648","patent_number":"8413715","patent_date":"2013-04-09","patent_id":"8413715"},{"inventor_id":"249323","patent_number":"8434826","patent_date":"2013-05-07","patent_id":"8434826"},{"inventor_id":"249323","patent_number":"8439324","patent_date":"2013-05-14","patent_id":"8439324"},{"inventor_id":"222786","patent_number":"8420988","patent_date":"2013-04-16","patent_id":"8420988"},{"inventor_id":"292986","patent_number":"8458660","patent_date":"2013-06-04","patent_id":"8458660"},{"inventor_id":"144258","patent_number":"7879904","patent_date":"2011-02-01","patent_id":"7879904"},{"inventor_id":"227482","patent_number":"8423526","patent_date":"2013-04-16","patent_id":"8423526"},{"inventor_id":"204998","patent_number":"8412409","patent_date":"2013-04-02","patent_id":"8412409"},{"inventor_id":"122716","patent_number":"7870244","patent_date":"2011-01-11","patent_id":"7870244"},{"inventor_id":"275711","patent_number":"8448786","patent_date":"2013-05-28","patent_id":"8448786"}]}', true);
        $this->assertEquals($expected, $results);
        $this->assertEquals(25, count($results['inventors']));
    }

    public function testQueryDatabaseInventorWithSorting()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $whereClause = "inventor.name_last like 'You%'";
        $whereFieldsUsed = array('inventor_last_name');
        $sort = array(array('inventor_first_name'=>'desc'));
        $selectFieldsSpecs = array(
            'patent_number' => $INVENTOR_FIELD_SPECS['patent_number'],
            'patent_title' => $INVENTOR_FIELD_SPECS['patent_title'],
            'inventor_id' => $INVENTOR_FIELD_SPECS['inventor_id'],
            'inventor_last_name' => $INVENTOR_FIELD_SPECS['inventor_last_name'],
            'inventor_first_name' => $INVENTOR_FIELD_SPECS['inventor_first_name'],
            'assignee_organization' => $INVENTOR_FIELD_SPECS['assignee_organization']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, $sort);
        $decoded = json_encode($results);
        $expected = json_decode('{"inventors":[{"inventor_id":"192627","inventor_last_name":"You","inventor_first_name":"Zong-hua"},{"inventor_id":"173237","inventor_last_name":"Yourlo","inventor_first_name":"Zhenya Alexander"},{"inventor_id":"246829","inventor_last_name":"You","inventor_first_name":"Zheng"},{"inventor_id":"175696","inventor_last_name":"You","inventor_first_name":"Yuli"},{"inventor_id":"62193","inventor_last_name":"You","inventor_first_name":"Yujian"},{"inventor_id":"189001","inventor_last_name":"You","inventor_first_name":"Yujian"},{"inventor_id":"221642","inventor_last_name":"Youn","inventor_first_name":"Yu Seok"},{"inventor_id":"189590","inventor_last_name":"You","inventor_first_name":"Young-Sub"},{"inventor_id":"262467","inventor_last_name":"You","inventor_first_name":"Young-Min"},{"inventor_id":"236714","inventor_last_name":"You","inventor_first_name":"Young-kuk"},{"inventor_id":"82632","inventor_last_name":"Youn","inventor_first_name":"Yong-Sik"},{"inventor_id":"131231","inventor_last_name":"You","inventor_first_name":"Yong-kuk"},{"inventor_id":"92406","inventor_last_name":"Youn","inventor_first_name":"Yong Sik"},{"inventor_id":"252393","inventor_last_name":"Youn","inventor_first_name":"Yong Sik"},{"inventor_id":"194796","inventor_last_name":"You","inventor_first_name":"Yi-Ping"},{"inventor_id":"106327","inventor_last_name":"Youn","inventor_first_name":"Yeu-Young"},{"inventor_id":"213354","inventor_last_name":"Youda","inventor_first_name":"Yasunobu"},{"inventor_id":"300236","inventor_last_name":"You","inventor_first_name":"Xuejun"},{"inventor_id":"249756","inventor_last_name":"You","inventor_first_name":"Xiaorong"},{"inventor_id":"36428","inventor_last_name":"You","inventor_first_name":"Xianmu"},{"inventor_id":"145988","inventor_last_name":"Youn","inventor_first_name":"Won-Bong"},{"inventor_id":"257535","inventor_last_name":"Young","inventor_first_name":"Winston B."},{"inventor_id":"13994","inventor_last_name":"Young","inventor_first_name":"William R."},{"inventor_id":"4118","inventor_last_name":"Younger Jr.","inventor_first_name":"William L."},{"inventor_id":"73019","inventor_last_name":"Young","inventor_first_name":"William J."}],"patents":[{"inventor_id":"192627","patent_number":"7903600","patent_title":"Control of CDMA signal integration","patent_id":"7903600"},{"inventor_id":"173237","patent_number":"7893646","patent_title":"Game system with robotic game pieces","patent_id":"7893646"},{"inventor_id":"173237","patent_number":"8416468","patent_title":"Sensing device for subsampling imaged coded data","patent_id":"8416468"},{"inventor_id":"246829","patent_number":"8433515","patent_title":"Method for measuring precision of star sensor and system using the same","patent_id":"8433515"},{"inventor_id":"175696","patent_number":"7895034","patent_title":"Audio encoding system","patent_id":"7895034"},{"inventor_id":"62193","patent_number":"6596467","patent_title":"Electronic device manufacture","patent_id":"6596467"},{"inventor_id":"62193","patent_number":"6602804","patent_title":"Porous materials","patent_id":"6602804"},{"inventor_id":"189001","patent_number":"7901795","patent_title":"OLEDs doped with phosphorescent compounds","patent_id":"7901795"},{"inventor_id":"221642","patent_number":"8420598","patent_title":"Mono modified exendin with polyethylene glycol or its derivatives and uses thereof","patent_id":"8420598"},{"inventor_id":"189590","patent_number":"7902059","patent_title":"Methods of forming void-free layers in openings of semiconductor substrates","patent_id":"7902059"},{"inventor_id":"262467","patent_number":"8441594","patent_title":"Liquid crystal display device having improved end reinforcing structure of a chassis surrounding a mold frame","patent_id":"8441594"},{"inventor_id":"236714","patent_number":"8428256","patent_title":"Method and apparatus for efficiently fixing transformed part of content","patent_id":"8428256"},{"inventor_id":"82632","patent_number":"6605996","patent_title":"Automatically gain controllable linear differential amplifier using variable degeneration resistor","patent_id":"6605996"},{"inventor_id":"82632","patent_number":"6615398","patent_title":"Method for dividing ROM and DDFS using the same method","patent_id":"6615398"},{"inventor_id":"131231","patent_number":"7874004","patent_title":"Method of copying and reproducing data from storage medium","patent_id":"7874004"},{"inventor_id":"92406","patent_number":"6610715","patent_title":"Cathecol hydrazone derivatives, process for preparing the same and pharmaceutical composition containing the same","patent_id":"6610715"},{"inventor_id":"252393","patent_number":"8436188","patent_title":"Method for the separation of S-(\u2212)-amlodipine from racemic amlodipine","patent_id":"8436188"},{"inventor_id":"194796","patent_number":"7904736","patent_title":"Multi-thread power-gating control design","patent_id":"7904736"},{"inventor_id":"106327","patent_number":"7863231","patent_title":"Thinner composition and method of removing photoresist using the same","patent_id":"7863231"},{"inventor_id":"213354","patent_number":"8416471","patent_title":"Document illuminating system and image reader including the same","patent_id":"8416471"},{"inventor_id":"300236","patent_number":"D631426","patent_title":"Tire","patent_id":"D631426"},{"inventor_id":"249756","patent_number":"8435098","patent_title":"Abrasive article with cured backsize layer","patent_id":"8435098"},{"inventor_id":"249756","patent_number":"8441724","patent_title":"IR filters with high VLT and neutral color","patent_id":"8441724"},{"inventor_id":"249756","patent_number":"8449635","patent_title":"Abrasive articles and methods for making same","patent_id":"8449635"},{"inventor_id":"36428","patent_number":"6585026","patent_title":"Safety device for window curtains","patent_id":"6585026"},{"inventor_id":"145988","patent_number":"7880706","patent_title":"Display device, method of driving the same and display device driving apparatus","patent_id":"7880706"},{"inventor_id":"257535","patent_number":"8439081","patent_title":"High flow nozzle system for flow control in bladder surge tanks","patent_id":"8439081"},{"inventor_id":"13994","patent_number":"4914501","patent_title":"Vertical contact structure","patent_id":"4914501"},{"inventor_id":"4118","patent_number":"4909396","patent_title":"Panel products display rack","patent_id":"4909396"},{"inventor_id":"73019","patent_number":"6601488","patent_title":"Cutter assembly for a microscope and related method","patent_id":"6601488"}],"assignees":[{"inventor_id":"192627","assignee_organization":"Mediatek Inc.","assignee_id":"5089"},{"inventor_id":"173237","assignee_organization":"Silverbrook Research Pty Ltd","assignee_id":"29400"},{"inventor_id":"246829","assignee_organization":"Tsinghua University","assignee_id":"21718"},{"inventor_id":"175696","assignee_organization":"Digital Rise Technology Co., Ltd.","assignee_id":"5771"},{"inventor_id":"62193","assignee_organization":"Shipley Company, L.L.C.","assignee_id":"4370"},{"inventor_id":"189001","assignee_organization":"University of Southern California","assignee_id":"11198"},{"inventor_id":"189001","assignee_organization":"The Trustees of Princeton University","assignee_id":"22893"},{"inventor_id":"221642","assignee_organization":"B & L Delipharm Corp.","assignee_id":"24772"},{"inventor_id":"189590","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_id":"9047"},{"inventor_id":"262467","assignee_organization":"Samsung Display Co., Ltd.","assignee_id":"18105"},{"inventor_id":"236714","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_id":"9047"},{"inventor_id":"82632","assignee_organization":"Electronics and Telecommunications Research Institute","assignee_id":"5518"},{"inventor_id":"131231","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_id":"9047"},{"inventor_id":"92406","assignee_organization":"Cheil Jedang Corporation","assignee_id":"29691"},{"inventor_id":"252393","assignee_organization":"CJ Cheiljedang Corporation","assignee_id":"6392"},{"inventor_id":"194796","assignee_organization":"National Chiao Tung University","assignee_id":"3030"},{"inventor_id":"194796","assignee_organization":"Industrial Technology Research Institute","assignee_id":"16342"},{"inventor_id":"106327","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_id":"9047"},{"inventor_id":"213354","assignee_organization":"Ricoh Company, Ltd.","assignee_id":"2615"},{"inventor_id":"300236","assignee_organization":"Shandong Yongtai Chemical Group Co. Ltd.","assignee_id":"23341"},{"inventor_id":"249756","assignee_organization":"Saint-Gobain Abrasives, Inc.","assignee_id":"17675"},{"inventor_id":"249756","assignee_organization":"Sperian Eye & Face Protection, Inc.","assignee_id":"15709"},{"inventor_id":"36428","assignee_organization":null,"assignee_id":"5399"},{"inventor_id":"145988","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_id":"9047"},{"inventor_id":"257535","assignee_organization":"Young Engineering & Manufacturing, Inc.","assignee_id":"30896"},{"inventor_id":"13994","assignee_organization":"Harris Corporation","assignee_id":"19521"},{"inventor_id":"4118","assignee_organization":"Weyerhaeuser NR Company","assignee_id":"18769"},{"inventor_id":"73019","assignee_organization":"The University of Kentucky Research Foundation","assignee_id":"28873"}]}', true);
        $this->assertEquals($expected, $results);
    }
}
