<?php
require_once dirname(__FILE__) . '/../app/DatabaseQuery.php';
require_once dirname(__FILE__) . '/../app/executeQuery.php';
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
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs);
        $this->assertEquals(10, count($results['patents']));
        $this->assertEquals(16, count($results['inventors']));
        $this->assertEquals(10, count($results['assignees']));
    }

    #Todo: Way too slow. Need to consider putting a limit 100000 on the total number of entities found. Otherwise the insert into the QueryResults is too slow.
/*    public function testQueryDatabase_EmptyWhere()
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
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
        $this->assertGreaterThan(6000, count($results['patents']));
    }*/

    public function testQueryDatabaseWithPaging()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.patent_id like '840%'";
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
        $resultsFirst = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
        $options = array('page'=>11, 'per_page'=>25);
        $resultsSecond = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
        $options = array('page'=>6, 'per_page'=>50);
        $resultsThird = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, null, $options);
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
        $whereClause = "patent.number like '84079%'";
        $whereFieldsUsed = array('patent_number');
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
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $this->assertEquals(25, count($results['patents']));
        $this->assertEquals(52, count($results['inventors']));
        $this->assertEquals(26, count($results['assignees']));
        $priorPatentTitle = '';
        foreach ($results['patents'] as $patent) {
            $this->assertGreaterThanOrEqual(strtolower($priorPatentTitle), strtolower($patent['patent_title']));
            $priorPatentTitle = $patent['patent_title'];
        }
    }

    /**
     * @expectedException ErrorException
     */   public function testQueryDatabaseWithInvalidSorting()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $whereClause = "patent.number like '820260%'";
        $whereFieldsUsed = array('patent_number');
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
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
    }

    public  function testQueryDatabaseAllFields()
    {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $selectFieldSpecs = $PATENT_FIELD_SPECS;
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $whereClause = "patent.number like '8202%'";
        $whereFieldsUsed = array('patent_number');
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldSpecs, null);
        $memUsed = memory_get_usage();
        $this->assertGreaterThanOrEqual(1, count($results['patents']));
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
        global $config;
        $selectFieldSpecs = $PATENT_FIELD_SPECS;
        $options = array('page'=>1, 'per_page'=>$config->getMaxPageSize());
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $whereClause = "patent.number like '82%'";
        $whereFieldsUsed = array('patent_number');
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldSpecs, null, $options);
        $memUsed = memory_get_usage();
        if (count($results['patents']) < $config->getMaxPageSize()) {
            $this->assertEquals($dbQuery->getTotalFound(), count($results['patents']));
        }
        else {
            $this->assertEquals($config->getMaxPageSize(), count($results['patents']));
            $this->assertGreaterThanOrEqual($config->getMaxPageSize(), $dbQuery->getTotalFound());
        }
    }

    #Todo: Slow - 1.1m, 1.5m total patents
    public function testQueryDatabaseInventorCombo1()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $whereClause = "patent.year >= 2007";
        $whereFieldsUsed = array('patent_date');
        $sort = array(array('inventor_last_name'=>'desc'));
        $selectFieldsSpecs = array(
            'patent_number' => $INVENTOR_FIELD_SPECS['patent_number'],
            'patent_date' => $INVENTOR_FIELD_SPECS['patent_date'],
            'inventor_id' => $INVENTOR_FIELD_SPECS['inventor_id'],
            'inventor_last_name' => $INVENTOR_FIELD_SPECS['inventor_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $encoded = json_encode($results);
        $this->assertEquals(25, count($results['inventors']));
        $this->assertEquals(47, count($results['patents']));
    }

    public function testQueryDatabaseInventorCombo2()
    {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;
        $whereClause = "inventor.name_last = 'jones'";
        $whereFieldsUsed = array('inventor_last_name');
        $sort = array(array('inventor_first_name'=>'desc'));
        $selectFieldsSpecs = array(
            'patent_number' => $INVENTOR_FIELD_SPECS['patent_number'],
            'patent_date' => $INVENTOR_FIELD_SPECS['patent_date'],
            'inventor_id' => $INVENTOR_FIELD_SPECS['inventor_id'],
            'inventor_last_name' => $INVENTOR_FIELD_SPECS['inventor_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $this->assertEquals(25, count($results['inventors']));
        $this->assertEquals(117, count($results['patents']));
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
        $results = $dbQuery->queryDatabase($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $whereClause, $whereFieldsUsed, array(), true, $selectFieldsSpecs, $sort);
        $this->assertEquals(25, count($results['inventors']));
        $this->assertEquals(72, count($results['patents']));
        $this->assertEquals(33, count($results['assignees']));
        $priorFirstName = 'ZZZZZZZ';
        foreach ($results['inventors'] as $inventor) {
            $this->assertLessThanOrEqual(strtolower($priorFirstName), strtolower($inventor['inventor_first_name']));
            $priorPatentTitle = $inventor['inventor_first_name'];
        }
    }
}
