<?php
require_once dirname(__FILE__) . '/../app/DatabaseQuery.php';
require_once dirname(__FILE__) . '/../app/executeQuery.php';
require_once dirname(__FILE__) . '/../app/entitySpecs.php';
require_once(dirname(__FILE__) . "/../app/Exceptions/QueryException.php");

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
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs);
        $encoded = json_encode($results);
        $expected = json_decode('{"patents":[{"patent_id":"8407900","patent_type":"utility","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings"},{"patent_id":"8407901","patent_type":"utility","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool"},{"patent_id":"8407902","patent_type":"utility","patent_number":"8407902","patent_title":"Reciprocating power tool having a counterbalance device"},{"patent_id":"8407903","patent_type":"utility","patent_number":"8407903","patent_title":"Rotating construction laser, in particular a self-compensating rotating construction laser, and method for measuring a tilt of an axis of rotation of a construction laser"},{"patent_id":"8407904","patent_type":"utility","patent_number":"8407904","patent_title":"Rotary laser beam emitter"},{"patent_id":"8407905","patent_type":"utility","patent_number":"8407905","patent_title":"Multiple magneto meters using Lorentz force for integrated systems"},{"patent_id":"8407906","patent_type":"utility","patent_number":"8407906","patent_title":"Window frame deflection measurement device and method of use"},{"patent_id":"8407907","patent_type":"utility","patent_number":"8407907","patent_title":"CMM with modular functionality"},{"patent_id":"8407908","patent_type":"utility","patent_number":"8407908","patent_title":"Profile measurement apparatus"},{"patent_id":"8407909","patent_type":"utility","patent_number":"8407909","patent_title":"Tape measure carrier and gauge"}],"inventors":[{"patent_id":"8407900","inventor_last_name":"Johnson","inventor_key_id":"191888"},{"patent_id":"8407901","inventor_last_name":"Oberheim","inventor_key_id":"191889"},{"patent_id":"8407902","inventor_last_name":"Limberg","inventor_key_id":"191890"},{"patent_id":"8407902","inventor_last_name":"Naughton","inventor_key_id":"191891"},{"patent_id":"8407902","inventor_last_name":"Scott","inventor_key_id":"191892"},{"patent_id":"8407903","inventor_last_name":"Koleszar","inventor_key_id":"191893"},{"patent_id":"8407903","inventor_last_name":"Winistoerfer","inventor_key_id":"191894"},{"patent_id":"8407904","inventor_last_name":"Kamizono","inventor_key_id":"100929"},{"patent_id":"8407904","inventor_last_name":"Hayashi","inventor_key_id":"191895"},{"patent_id":"8407905","inventor_last_name":"Van Der Heide","inventor_key_id":"191896"},{"patent_id":"8407905","inventor_last_name":"Hsu","inventor_key_id":"191897"},{"patent_id":"8407905","inventor_last_name":"Flannery","inventor_key_id":"191898"},{"patent_id":"8407906","inventor_last_name":"Heyer","inventor_key_id":"191899"},{"patent_id":"8407907","inventor_last_name":"Tait","inventor_key_id":"191900"},{"patent_id":"8407908","inventor_last_name":"Noda","inventor_key_id":"191901"},{"patent_id":"8407909","inventor_last_name":"Lindsay","inventor_key_id":"191902"}],"assignees":[{"patent_id":"8407900","assignee_last_name":null,"assignee_key_id":"31723"},{"patent_id":"8407901","assignee_last_name":null,"assignee_key_id":"28678"},{"patent_id":"8407902","assignee_last_name":null,"assignee_key_id":"6994"},{"patent_id":"8407903","assignee_last_name":null,"assignee_key_id":"23816"},{"patent_id":"8407904","assignee_last_name":null,"assignee_key_id":"17717"},{"patent_id":"8407905","assignee_last_name":null,"assignee_key_id":"27839"},{"patent_id":"8407906","assignee_last_name":null,"assignee_key_id":"19604"},{"patent_id":"8407907","assignee_last_name":null,"assignee_key_id":"15053"},{"patent_id":"8407908","assignee_last_name":null,"assignee_key_id":"24323"},{"patent_id":"8407909","assignee_last_name":null,"assignee_key_id":null}]}', true);
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
        $options = array('per_page' => 10000);
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
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
        $options = array('page' => 1, 'per_page' => 25);
        $resultsFirst = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
        $options = array('page' => 11, 'per_page' => 25);
        $resultsSecond = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
        $options = array('page' => 6, 'per_page' => 50);
        $resultsThird = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
        $this->assertNotEquals($resultsFirst['patents'][0], $resultsSecond['patents'][0]);
        $this->assertEquals($resultsSecond['patents'][0], $resultsThird['patents'][0]);
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
        $sort = array(array('patent_title' => 'asc'));
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $encoded = json_encode($results);
        $expected = json_decode('{"patents":[{"patent_id":"8407907","patent_type":"utility","patent_number":"8407907","patent_title":"CMM with modular functionality"},{"patent_id":"8407901","patent_type":"utility","patent_number":"8407901","patent_title":"Drive mechanism for a reciprocating tool"},{"patent_id":"8407905","patent_type":"utility","patent_number":"8407905","patent_title":"Multiple magneto meters using Lorentz force for integrated systems"},{"patent_id":"8407908","patent_type":"utility","patent_number":"8407908","patent_title":"Profile measurement apparatus"},{"patent_id":"8407902","patent_type":"utility","patent_number":"8407902","patent_title":"Reciprocating power tool having a counterbalance device"},{"patent_id":"8407904","patent_type":"utility","patent_number":"8407904","patent_title":"Rotary laser beam emitter"},{"patent_id":"8407903","patent_type":"utility","patent_number":"8407903","patent_title":"Rotating construction laser, in particular a self-compensating rotating construction laser, and method for measuring a tilt of an axis of rotation of a construction laser"},{"patent_id":"8407900","patent_type":"utility","patent_number":"8407900","patent_title":"Shaving cartridge having mostly elastomeric wings"},{"patent_id":"8407909","patent_type":"utility","patent_number":"8407909","patent_title":"Tape measure carrier and gauge"},{"patent_id":"8407906","patent_type":"utility","patent_number":"8407906","patent_title":"Window frame deflection measurement device and method of use"}],"inventors":[{"patent_id":"8407907","inventor_last_name":"Tait","inventor_key_id":"191900"},{"patent_id":"8407901","inventor_last_name":"Oberheim","inventor_key_id":"191889"},{"patent_id":"8407905","inventor_last_name":"Van Der Heide","inventor_key_id":"191896"},{"patent_id":"8407905","inventor_last_name":"Hsu","inventor_key_id":"191897"},{"patent_id":"8407905","inventor_last_name":"Flannery","inventor_key_id":"191898"},{"patent_id":"8407908","inventor_last_name":"Noda","inventor_key_id":"191901"},{"patent_id":"8407902","inventor_last_name":"Limberg","inventor_key_id":"191890"},{"patent_id":"8407902","inventor_last_name":"Naughton","inventor_key_id":"191891"},{"patent_id":"8407902","inventor_last_name":"Scott","inventor_key_id":"191892"},{"patent_id":"8407904","inventor_last_name":"Kamizono","inventor_key_id":"100929"},{"patent_id":"8407904","inventor_last_name":"Hayashi","inventor_key_id":"191895"},{"patent_id":"8407903","inventor_last_name":"Koleszar","inventor_key_id":"191893"},{"patent_id":"8407903","inventor_last_name":"Winistoerfer","inventor_key_id":"191894"},{"patent_id":"8407900","inventor_last_name":"Johnson","inventor_key_id":"191888"},{"patent_id":"8407909","inventor_last_name":"Lindsay","inventor_key_id":"191902"},{"patent_id":"8407906","inventor_last_name":"Heyer","inventor_key_id":"191899"}],"assignees":[{"patent_id":"8407907","assignee_last_name":null,"assignee_key_id":"15053"},{"patent_id":"8407901","assignee_last_name":null,"assignee_key_id":"28678"},{"patent_id":"8407905","assignee_last_name":null,"assignee_key_id":"27839"},{"patent_id":"8407908","assignee_last_name":null,"assignee_key_id":"24323"},{"patent_id":"8407902","assignee_last_name":null,"assignee_key_id":"6994"},{"patent_id":"8407904","assignee_last_name":null,"assignee_key_id":"17717"},{"patent_id":"8407903","assignee_last_name":null,"assignee_key_id":"23816"},{"patent_id":"8407900","assignee_last_name":null,"assignee_key_id":"31723"},{"patent_id":"8407909","assignee_last_name":null,"assignee_key_id":null},{"patent_id":"8407906","assignee_last_name":null,"assignee_key_id":"19604"}]}', true);
        $this->assertEquals($expected, $results);
    }

    /**
     * @expectedException ErrorException
     */
    public function testQueryDatabaseWithInvalidSorting()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.patent_number like '820260%'";
        $whereFieldsUsed = array('patent_id');
        $sort = array(array('inventor_id' => 'asc'));
        $selectFieldsSpecs = array(
            'patent_id' => $PATENT_FIELD_SPECS['patent_id'],
            'patent_type' => $PATENT_FIELD_SPECS['patent_type'],
            'patent_number' => $PATENT_FIELD_SPECS['patent_number'],
            'patent_title' => $PATENT_FIELD_SPECS['patent_title'],
            'inventor_last_name' => $PATENT_FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $PATENT_FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();

        $exception = null;
        try {
            $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        } catch (\PVExceptions\QueryException $e) {
            $exception = $e->getCustomCode();
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
        $this->assertEquals("QR2", $exception);


    }

    public function testQueryDatabaseAllFields()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $selectFieldSpecs = $PATENT_FIELD_SPECS;
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, null, array(), array(), true, $selectFieldSpecs, null);
        $memUsed = memory_get_usage();
        if (count($results['patents']) < 25) {
            $this->assertEquals($dbQuery->getTotalCounts(), count($results['patents']));
        } else {
            $this->assertEquals(25, count($results['patents']));
            $this->assertGreaterThanOrEqual(25, $dbQuery->getTotalCounts());
        }
    }

    public function testQueryDatabaseAllFieldsMaxPageSize()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $selectFieldSpecs = $PATENT_FIELD_SPECS;
        $options = array('page' => 1, 'per_page' => 10000);
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, null, array(), array(), true, $selectFieldSpecs, null, $options);
        $memUsed = memory_get_usage();
        if (count($results['patents']) < 10000) {
            $this->assertEquals($dbQuery->getTotalCounts(), count($results['patents']));
        } else {
            $this->assertEquals(10000, count($results['patents']));
            $this->assertGreaterThanOrEqual(10000, $dbQuery->getTotalCounts());
        }
    }

    public function testQueryDatabaseInventorCombo1()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $whereClause = "patent.date >= '2007-01-04'";
        $whereFieldsUsed = array('patent_date');
        $sort = array(array('inventor_last_name' => 'desc'));
        $selectFieldsSpecs = array(
            'patent_number' => $INVENTOR_FIELD_SPECS['patent_number'],
            'patent_date' => $INVENTOR_FIELD_SPECS['patent_date'],
            'inventor_id' => $INVENTOR_FIELD_SPECS['inventor_id'],
            'inventor_last_name' => $INVENTOR_FIELD_SPECS['inventor_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $encoded = json_encode($results);
        $expected = json_decode('{"inventors":[{"inventor_id":"7900137-1","inventor_last_name":"\u00d8ygard","inventor_key_id":"182671"},{"inventor_id":"7866697-5","inventor_last_name":"\u00d8yen","inventor_key_id":"112873"},{"inventor_id":"8413711-1","inventor_last_name":"\u00d8yen","inventor_key_id":"203733"},{"inventor_id":"7871110-3","inventor_last_name":"\u00d8stergaard","inventor_key_id":"122923"},{"inventor_id":"7888061-1","inventor_last_name":"\u00d8stergaard","inventor_key_id":"158939"},{"inventor_id":"8410732-2","inventor_last_name":"\u00d8stergaard","inventor_key_id":"198146"},{"inventor_id":"8441138-2","inventor_last_name":"\u00d8stergaard","inventor_key_id":"255945"},{"inventor_id":"6595925-1","inventor_last_name":"\u00d8stensen","inventor_key_id":"60419"},{"inventor_id":"8446850-1","inventor_last_name":"\u00d8rnbo","inventor_key_id":"266039"},{"inventor_id":"7877947-3","inventor_last_name":"Zyznowski","inventor_key_id":"137773"},{"inventor_id":"8425569-1","inventor_last_name":"Zyzelewski","inventor_key_id":"226584"},{"inventor_id":"7867529-4","inventor_last_name":"Zyzak","inventor_key_id":"114684"},{"inventor_id":"7897942-2","inventor_last_name":"Zywno","inventor_key_id":"178767"},{"inventor_id":"8458501-1","inventor_last_name":"Zyuban","inventor_key_id":"285590"},{"inventor_id":"7877015-3","inventor_last_name":"Zyss","inventor_key_id":"135852"},{"inventor_id":"8423704-1","inventor_last_name":"Zysk","inventor_key_id":"223322"},{"inventor_id":"8413715-1","inventor_last_name":"Zyoubouji","inventor_key_id":"203743"},{"inventor_id":"8434826-4","inventor_last_name":"Zynda","inventor_key_id":"244002"},{"inventor_id":"8420988-3","inventor_last_name":"Zymroz Jr.","inventor_key_id":"218435"},{"inventor_id":"8458660-1","inventor_last_name":"Zylka","inventor_key_id":"285878"},{"inventor_id":"7879904-1","inventor_last_name":"Zygmunt","inventor_key_id":"142161"},{"inventor_id":"8412409-1","inventor_last_name":"Zydek","inventor_key_id":"201165"},{"inventor_id":"7870244-10","inventor_last_name":"Zychowski","inventor_key_id":"121121"},{"inventor_id":"8448786-2","inventor_last_name":"Zyburt","inventor_key_id":"269350"},{"inventor_id":"8429956-3","inventor_last_name":"Zwollo","inventor_key_id":"235087"}],"patents":[{"inventor_key_id":"182671","patent_number":"7900137","patent_date":"2011-03-01","patent_id":"7900137"},{"inventor_key_id":"112873","patent_number":"7866697","patent_date":"2011-01-11","patent_id":"7866697"},{"inventor_key_id":"203733","patent_number":"8413711","patent_date":"2013-04-09","patent_id":"8413711"},{"inventor_key_id":"122923","patent_number":"7871110","patent_date":"2011-01-18","patent_id":"7871110"},{"inventor_key_id":"158939","patent_number":"7888061","patent_date":"2011-02-15","patent_id":"7888061"},{"inventor_key_id":"198146","patent_number":"8410732","patent_date":"2013-04-02","patent_id":"8410732"},{"inventor_key_id":"255945","patent_number":"8441138","patent_date":"2013-05-14","patent_id":"8441138"},{"inventor_key_id":"60419","patent_number":"6595925","patent_date":"2003-07-22","patent_id":"6595925"},{"inventor_key_id":"60419","patent_number":"7892522","patent_date":"2011-02-22","patent_id":"7892522"},{"inventor_key_id":"266039","patent_number":"8446850","patent_date":"2013-05-21","patent_id":"8446850"},{"inventor_key_id":"137773","patent_number":"7877947","patent_date":"2011-02-01","patent_id":"7877947"},{"inventor_key_id":"226584","patent_number":"8425569","patent_date":"2013-04-23","patent_id":"8425569"},{"inventor_key_id":"114684","patent_number":"7867529","patent_date":"2011-01-11","patent_id":"7867529"},{"inventor_key_id":"178767","patent_number":"7897942","patent_date":"2011-03-01","patent_id":"7897942"},{"inventor_key_id":"285590","patent_number":"8458501","patent_date":"2013-06-04","patent_id":"8458501"},{"inventor_key_id":"135852","patent_number":"7877015","patent_date":"2011-01-25","patent_id":"7877015"},{"inventor_key_id":"223322","patent_number":"8423704","patent_date":"2013-04-16","patent_id":"8423704"},{"inventor_key_id":"203743","patent_number":"8413715","patent_date":"2013-04-09","patent_id":"8413715"},{"inventor_key_id":"244002","patent_number":"8434826","patent_date":"2013-05-07","patent_id":"8434826"},{"inventor_key_id":"244002","patent_number":"8439324","patent_date":"2013-05-14","patent_id":"8439324"},{"inventor_key_id":"218435","patent_number":"8420988","patent_date":"2013-04-16","patent_id":"8420988"},{"inventor_key_id":"285878","patent_number":"8458660","patent_date":"2013-06-04","patent_id":"8458660"},{"inventor_key_id":"142161","patent_number":"7879904","patent_date":"2011-02-01","patent_id":"7879904"},{"inventor_key_id":"142161","patent_number":"8423526","patent_date":"2013-04-16","patent_id":"8423526"},{"inventor_key_id":"201165","patent_number":"8412409","patent_date":"2013-04-02","patent_id":"8412409"},{"inventor_key_id":"121121","patent_number":"7870244","patent_date":"2011-01-11","patent_id":"7870244"},{"inventor_key_id":"269350","patent_number":"8448786","patent_date":"2013-05-28","patent_id":"8448786"},{"inventor_key_id":"235087","patent_number":"8429956","patent_date":"2013-04-30","patent_id":"8429956"}]}', true);
        $this->assertEquals($expected, $results);
        $this->assertEquals(25, count($results['inventors']));
    }

    public function testQueryDatabaseInventorWithSorting()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $whereClause = "inventor.name_last like 'You%'";
        $whereFieldsUsed = array('inventor_last_name');
        $sort = array(array('inventor_first_name' => 'desc'));
        $selectFieldsSpecs = array(
            'patent_number' => $INVENTOR_FIELD_SPECS['patent_number'],
            'patent_title' => $INVENTOR_FIELD_SPECS['patent_title'],
            'inventor_id' => $INVENTOR_FIELD_SPECS['inventor_id'],
            'inventor_last_name' => $INVENTOR_FIELD_SPECS['inventor_last_name'],
            'inventor_first_name' => $INVENTOR_FIELD_SPECS['inventor_first_name'],
            'assignee_organization' => $INVENTOR_FIELD_SPECS['assignee_organization']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $decoded = json_encode($results);
        $expected = json_decode('{"inventors":[{"inventor_id":"7903600-2","inventor_last_name":"You","inventor_first_name":"Zong-hua","inventor_key_id":"189224"},{"inventor_id":"7893646-2","inventor_last_name":"Yourlo","inventor_first_name":"Zhenya Alexander","inventor_key_id":"170392"},{"inventor_id":"8433515-1","inventor_last_name":"You","inventor_first_name":"Zheng","inventor_key_id":"241606"},{"inventor_id":"7895034-1","inventor_last_name":"You","inventor_first_name":"Yuli","inventor_key_id":"172779"},{"inventor_id":"6596467-2","inventor_last_name":"You","inventor_first_name":"Yujian","inventor_key_id":"61731"},{"inventor_id":"7901795-2","inventor_last_name":"You","inventor_first_name":"Yujian","inventor_key_id":"185736"},{"inventor_id":"8420598-4","inventor_last_name":"Youn","inventor_first_name":"Yu Seok","inventor_key_id":"217311"},{"inventor_id":"7902059-3","inventor_last_name":"You","inventor_first_name":"Young-Sub","inventor_key_id":"186288"},{"inventor_id":"8441594-1","inventor_last_name":"You","inventor_first_name":"Young-Min","inventor_key_id":"256652"},{"inventor_id":"8428256-3","inventor_last_name":"You","inventor_first_name":"Young-kuk","inventor_key_id":"231844"},{"inventor_id":"6605996-3","inventor_last_name":"Youn","inventor_first_name":"Yong-Sik","inventor_key_id":"81808"},{"inventor_id":"7874004-2","inventor_last_name":"You","inventor_first_name":"Yong-kuk","inventor_key_id":"129435"},{"inventor_id":"6610715-7","inventor_last_name":"Youn","inventor_first_name":"Yong Sik","inventor_key_id":"91381"},{"inventor_id":"8436188-3","inventor_last_name":"Youn","inventor_first_name":"Yong Sik","inventor_key_id":"246931"},{"inventor_id":"7904736-1","inventor_last_name":"You","inventor_first_name":"Yi-Ping","inventor_key_id":"191308"},{"inventor_id":"7863231-7","inventor_last_name":"Youn","inventor_first_name":"Yeu-Young","inventor_key_id":"105021"},{"inventor_id":"8416471-6","inventor_last_name":"Youda","inventor_first_name":"Yasunobu","inventor_key_id":"209277"},{"inventor_id":"D631426-3","inventor_last_name":"You","inventor_first_name":"Xuejun","inventor_key_id":"292947"},{"inventor_id":"8435098-1","inventor_last_name":"You","inventor_first_name":"Xiaorong","inventor_key_id":"244416"},{"inventor_id":"6585026-2","inventor_last_name":"You","inventor_first_name":"Xianmu","inventor_key_id":"36186"},{"inventor_id":"7880706-1","inventor_last_name":"Youn","inventor_first_name":"Won-Bong","inventor_key_id":"143860"},{"inventor_id":"8439081-1","inventor_last_name":"Young","inventor_first_name":"Winston B.","inventor_key_id":"251890"},{"inventor_id":"4914501-2","inventor_last_name":"Young","inventor_first_name":"William R.","inventor_key_id":"13947"},{"inventor_id":"4909396-1","inventor_last_name":"Younger Jr.","inventor_first_name":"William L.","inventor_key_id":"4109"},{"inventor_id":"6601488-2","inventor_last_name":"Young","inventor_first_name":"William J.","inventor_key_id":"72379"}],"patents":[{"inventor_key_id":"189224","patent_number":"7903600","patent_title":"Control of CDMA signal integration","patent_id":"7903600"},{"inventor_key_id":"170392","patent_number":"7893646","patent_title":"Game system with robotic game pieces","patent_id":"7893646"},{"inventor_key_id":"170392","patent_number":"8416468","patent_title":"Sensing device for subsampling imaged coded data","patent_id":"8416468"},{"inventor_key_id":"241606","patent_number":"8433515","patent_title":"Method for measuring precision of star sensor and system using the same","patent_id":"8433515"},{"inventor_key_id":"172779","patent_number":"7895034","patent_title":"Audio encoding system","patent_id":"7895034"},{"inventor_key_id":"61731","patent_number":"6596467","patent_title":"Electronic device manufacture","patent_id":"6596467"},{"inventor_key_id":"61731","patent_number":"6602804","patent_title":"Porous materials","patent_id":"6602804"},{"inventor_key_id":"185736","patent_number":"7901795","patent_title":"OLEDs doped with phosphorescent compounds","patent_id":"7901795"},{"inventor_key_id":"217311","patent_number":"8420598","patent_title":"Mono modified exendin with polyethylene glycol or its derivatives and uses thereof","patent_id":"8420598"},{"inventor_key_id":"186288","patent_number":"7902059","patent_title":"Methods of forming void-free layers in openings of semiconductor substrates","patent_id":"7902059"},{"inventor_key_id":"256652","patent_number":"8441594","patent_title":"Liquid crystal display device having improved end reinforcing structure of a chassis surrounding a mold frame","patent_id":"8441594"},{"inventor_key_id":"231844","patent_number":"8428256","patent_title":"Method and apparatus for efficiently fixing transformed part of content","patent_id":"8428256"},{"inventor_key_id":"81808","patent_number":"6605996","patent_title":"Automatically gain controllable linear differential amplifier using variable degeneration resistor","patent_id":"6605996"},{"inventor_key_id":"81808","patent_number":"6615398","patent_title":"Method for dividing ROM and DDFS using the same method","patent_id":"6615398"},{"inventor_key_id":"129435","patent_number":"7874004","patent_title":"Method of copying and reproducing data from storage medium","patent_id":"7874004"},{"inventor_key_id":"91381","patent_number":"6610715","patent_title":"Cathecol hydrazone derivatives, process for preparing the same and pharmaceutical composition containing the same","patent_id":"6610715"},{"inventor_key_id":"246931","patent_number":"8436188","patent_title":"Method for the separation of S-(\u2212)-amlodipine from racemic amlodipine","patent_id":"8436188"},{"inventor_key_id":"191308","patent_number":"7904736","patent_title":"Multi-thread power-gating control design","patent_id":"7904736"},{"inventor_key_id":"105021","patent_number":"7863231","patent_title":"Thinner composition and method of removing photoresist using the same","patent_id":"7863231"},{"inventor_key_id":"209277","patent_number":"8416471","patent_title":"Document illuminating system and image reader including the same","patent_id":"8416471"},{"inventor_key_id":"292947","patent_number":"D631426","patent_title":"Tire","patent_id":"D631426"},{"inventor_key_id":"244416","patent_number":"8435098","patent_title":"Abrasive article with cured backsize layer","patent_id":"8435098"},{"inventor_key_id":"244416","patent_number":"8441724","patent_title":"IR filters with high VLT and neutral color","patent_id":"8441724"},{"inventor_key_id":"244416","patent_number":"8449635","patent_title":"Abrasive articles and methods for making same","patent_id":"8449635"},{"inventor_key_id":"36186","patent_number":"6585026","patent_title":"Safety device for window curtains","patent_id":"6585026"},{"inventor_key_id":"143860","patent_number":"7880706","patent_title":"Display device, method of driving the same and display device driving apparatus","patent_id":"7880706"},{"inventor_key_id":"251890","patent_number":"8439081","patent_title":"High flow nozzle system for flow control in bladder surge tanks","patent_id":"8439081"},{"inventor_key_id":"13947","patent_number":"4914501","patent_title":"Vertical contact structure","patent_id":"4914501"},{"inventor_key_id":"4109","patent_number":"4909396","patent_title":"Panel products display rack","patent_id":"4909396"},{"inventor_key_id":"72379","patent_number":"6601488","patent_title":"Cutter assembly for a microscope and related method","patent_id":"6601488"}],"assignees":[{"inventor_key_id":"189224","assignee_organization":"Mediatek Inc.","assignee_key_id":"5083"},{"inventor_key_id":"170392","assignee_organization":"Silverbrook Research Pty Ltd","assignee_key_id":"29420"},{"inventor_key_id":"241606","assignee_organization":"Tsinghua University","assignee_key_id":"21744"},{"inventor_key_id":"172779","assignee_organization":"Digital Rise Technology Co., Ltd.","assignee_key_id":"5760"},{"inventor_key_id":"61731","assignee_organization":"Shipley Company, L.L.C.","assignee_key_id":"4372"},{"inventor_key_id":"185736","assignee_organization":"University of South Carolina","assignee_key_id":"17875"},{"inventor_key_id":"185736","assignee_organization":"The Trustees of Princeton University","assignee_key_id":"22921"},{"inventor_key_id":"217311","assignee_organization":"B & L Delipharm Corp.","assignee_key_id":"24805"},{"inventor_key_id":"186288","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_key_id":"9027"},{"inventor_key_id":"256652","assignee_organization":"Samsung Display Co., Ltd.","assignee_key_id":"18117"},{"inventor_key_id":"231844","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_key_id":"9027"},{"inventor_key_id":"81808","assignee_organization":"Electronics and Telecommunications Research Institute","assignee_key_id":"5517"},{"inventor_key_id":"129435","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_key_id":"9027"},{"inventor_key_id":"91381","assignee_organization":"Cheil Jedang Corporation","assignee_key_id":"29709"},{"inventor_key_id":"246931","assignee_organization":"CJ Cheiljedang Corporation","assignee_key_id":"6375"},{"inventor_key_id":"191308","assignee_organization":"National Chiao Tung University","assignee_key_id":"3031"},{"inventor_key_id":"191308","assignee_organization":"Industrial Technology Research Institute","assignee_key_id":"16351"},{"inventor_key_id":"105021","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_key_id":"9027"},{"inventor_key_id":"209277","assignee_organization":"Ricoh Company, Ltd.","assignee_key_id":"2608"},{"inventor_key_id":"292947","assignee_organization":"Shandong Yongtai Chemical Group Co. Ltd.","assignee_key_id":"23356"},{"inventor_key_id":"244416","assignee_organization":"Saint-Gobain Abrasifs","assignee_key_id":"16513"},{"inventor_key_id":"244416","assignee_organization":"Sperian Eye & Face Protection, Inc.","assignee_key_id":"15722"},{"inventor_key_id":"36186","assignee_organization":null,"assignee_key_id":"5396"},{"inventor_key_id":"143860","assignee_organization":"Samsung Electronics Co., Ltd.","assignee_key_id":"9027"},{"inventor_key_id":"251890","assignee_organization":"Young Engineering & Manufacturing, Inc.","assignee_key_id":"30903"},{"inventor_key_id":"13947","assignee_organization":"Harris Corporation","assignee_key_id":"19533"},{"inventor_key_id":"4109","assignee_organization":"Weyerhaeuser NR Company","assignee_key_id":"18783"},{"inventor_key_id":"72379","assignee_organization":"University of Kentucky Research Foundation","assignee_key_id":"13713"}]}', true);
        $this->assertEquals($expected, $results);
    }
}
