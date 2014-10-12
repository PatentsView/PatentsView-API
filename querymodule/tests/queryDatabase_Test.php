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
        global $FIELD_SPECS;
        global $PATENT_ENTITY_SPECS;
        $whereClause = "patent.id like '867760%'";
        $whereFieldsUsed = array('patent_id');
        $selectFieldsSpecs = array(
            'patent_id' => $FIELD_SPECS['patent_id'],
            'patent_type' => $FIELD_SPECS['patent_type'],
            'patent_number' => $FIELD_SPECS['patent_number'],
            'patent_country' => $FIELD_SPECS['patent_country'],
            'patent_title' => $FIELD_SPECS['patent_title'],
            'inventor_last_name' => $FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs);
        $expected = json_decode('{"patents":[{"patent_id":"8677600","patent_type":"utility","patent_number":"8677600","patent_country":"US","patent_title":"Methods of dispensing and making porous material with growth enhancing element"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same"},{"patent_id":"8677602","patent_type":"utility","patent_number":"8677602","patent_country":"US","patent_title":"Method of making a flexible device shaft with angled spiral wrap"},{"patent_id":"8677603","patent_type":"utility","patent_number":"8677603","patent_country":"US","patent_title":"Drop maker for sap collection system"},{"patent_id":"8677604","patent_type":"utility","patent_number":"8677604","patent_country":"US","patent_title":"Method of manufacturing boundary acoustic wave device"},{"patent_id":"8677605","patent_type":"utility","patent_number":"8677605","patent_country":"US","patent_title":"Method for manufacturing sensor unit"},{"patent_id":"8677606","patent_type":"utility","patent_number":"8677606","patent_country":"US","patent_title":"Method for assembling a rotor with permanent magnets"},{"patent_id":"8677607","patent_type":"utility","patent_number":"8677607","patent_country":"US","patent_title":"Method of manufacturing magnetoresistive element"},{"patent_id":"8677608","patent_type":"utility","patent_number":"8677608","patent_country":"US","patent_title":"Method for manufacturing iron core and apparatus for manufacturing iron core"},{"patent_id":"8677609","patent_type":"utility","patent_number":"8677609","patent_country":"US","patent_title":"Method for producing a circuit-breaker pole part"}],"inventors":[{"patent_id":"8677600","inventor_last_name":"Jr.","inventor_id":"8677600-1"},{"patent_id":"8677600","inventor_last_name":"Singer","inventor_id":"8677600-2"},{"patent_id":"8677601","inventor_last_name":"Millwee","inventor_id":"8677601-1"},{"patent_id":"8677601","inventor_last_name":"Shay","inventor_id":"8677601-2"},{"patent_id":"8677601","inventor_last_name":"Majkrzak","inventor_id":"8677601-3"},{"patent_id":"8677601","inventor_last_name":"Young","inventor_id":"8677601-4"},{"patent_id":"8677601","inventor_last_name":"Kupumbati","inventor_id":"8677601-5"},{"patent_id":"8677602","inventor_last_name":"Dayton","inventor_id":"8677602-1"},{"patent_id":"8677602","inventor_last_name":"Boutillette","inventor_id":"8677602-2"},{"patent_id":"8677603","inventor_last_name":"Reynolds","inventor_id":"8677603-1"},{"patent_id":"8677604","inventor_last_name":"Kando","inventor_id":"8677604-1"},{"patent_id":"8677605","inventor_last_name":"Lim","inventor_id":"8677605-1"},{"patent_id":"8677605","inventor_last_name":"Goh","inventor_id":"8677605-2"},{"patent_id":"8677606","inventor_last_name":"Desiron","inventor_id":"8677606-1"},{"patent_id":"8677607","inventor_last_name":"Yanagisawa","inventor_id":"8677607-1"},{"patent_id":"8677607","inventor_last_name":"Hirose","inventor_id":"8677607-2"},{"patent_id":"8677607","inventor_last_name":"Saruki","inventor_id":"8677607-3"},{"patent_id":"8677608","inventor_last_name":"Akita","inventor_id":"8677608-1"},{"patent_id":"8677608","inventor_last_name":"Furusawa","inventor_id":"8677608-2"},{"patent_id":"8677609","inventor_last_name":"Shang","inventor_id":"8677609-1"}],"assignees":[{"patent_id":"8677600","assignee_last_name":"","assignee_id":"ad87f29b5aa3d8c0bcbf86bc0ed69b8d"},{"patent_id":"8677601","assignee_last_name":"","assignee_id":"ba63daad7af208753ccc1e7383c11281"},{"patent_id":"8677602","assignee_last_name":"","assignee_id":"60ab17c614bc80bfbddd5b3c4bae3ea3"},{"patent_id":"8677603","assignee_last_name":null,"assignee_id":null},{"patent_id":"8677604","assignee_last_name":"","assignee_id":"545147830daddf3dbcc15397ee38fc31"},{"patent_id":"8677605","assignee_last_name":"","assignee_id":"dfb22487d8089f003b2b092aeec21823"},{"patent_id":"8677606","assignee_last_name":"","assignee_id":"02b31f7d03b24c5c86e6d365d6b17133"},{"patent_id":"8677607","assignee_last_name":"","assignee_id":"22817f7a619b8a60fd4aec1a5166ddc6"},{"patent_id":"8677608","assignee_last_name":"","assignee_id":"50b9f7425f3c324d06c53ca449075086"},{"patent_id":"8677609","assignee_last_name":"","assignee_id":"847ef656a074289a8fbee02b3d7c6e73"}]}', true);
        $this->assertEquals($expected, $results);
    }

    public function testQueryDatabase_EmptyWhere()
    {
        global $FIELD_SPECS;
        global $PATENT_ENTITY_SPECS;
        $whereClause = '';
        $whereFieldsUsed = array();
        $selectFieldsSpecs = array(
            'patent_id' => $FIELD_SPECS['patent_id'],
            'patent_type' => $FIELD_SPECS['patent_type'],
            'patent_number' => $FIELD_SPECS['patent_number'],
            'patent_country' => $FIELD_SPECS['patent_country'],
            'patent_title' => $FIELD_SPECS['patent_title'],
            'inventor_last_name' => $FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $options = array('per_page'=>10000);
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $this->assertGreaterThan(6000, count($results['patents']));
    }

    public function testQueryDatabaseWithPaging()
    {
        global $FIELD_SPECS;
        global $PATENT_ENTITY_SPECS;
        $whereClause = "patent.id like '86%'";
        $whereFieldsUsed = array('patent_id');
        $selectFieldsSpecs = array(
            'patent_id' => $FIELD_SPECS['patent_id'],
            'patent_type' => $FIELD_SPECS['patent_type'],
            'patent_number' => $FIELD_SPECS['patent_number'],
            'patent_country' => $FIELD_SPECS['patent_country'],
            'patent_title' => $FIELD_SPECS['patent_title'],
            'inventor_last_name' => $FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $options = array('page'=>1, 'per_page'=>25);
        $resultsFirst = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $options = array('page'=>11, 'per_page'=>25);
        $resultsSecond = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $options = array('page'=>6, 'per_page'=>50);
        $resultsThird = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
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
        global $FIELD_SPECS;
        global $PATENT_ENTITY_SPECS;
        $whereClause = "patent.id like '867760%'";
        $whereFieldsUsed = array('patent_id');
        $sort = array(array('patent_title'=>'asc'));
        $selectFieldsSpecs = array(
            'patent_id' => $FIELD_SPECS['patent_id'],
            'patent_type' => $FIELD_SPECS['patent_type'],
            'patent_number' => $FIELD_SPECS['patent_number'],
            'patent_country' => $FIELD_SPECS['patent_country'],
            'patent_title' => $FIELD_SPECS['patent_title'],
            'inventor_last_name' => $FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, $sort);
        $expected = json_decode('{"patents":[{"patent_id":"8677600","patent_type":"utility","patent_number":"8677600","patent_country":"US","patent_title":"Methods of dispensing and making porous material with growth enhancing element"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same"},{"patent_id":"8677602","patent_type":"utility","patent_number":"8677602","patent_country":"US","patent_title":"Method of making a flexible device shaft with angled spiral wrap"},{"patent_id":"8677603","patent_type":"utility","patent_number":"8677603","patent_country":"US","patent_title":"Drop maker for sap collection system"},{"patent_id":"8677604","patent_type":"utility","patent_number":"8677604","patent_country":"US","patent_title":"Method of manufacturing boundary acoustic wave device"},{"patent_id":"8677605","patent_type":"utility","patent_number":"8677605","patent_country":"US","patent_title":"Method for manufacturing sensor unit"},{"patent_id":"8677606","patent_type":"utility","patent_number":"8677606","patent_country":"US","patent_title":"Method for assembling a rotor with permanent magnets"},{"patent_id":"8677607","patent_type":"utility","patent_number":"8677607","patent_country":"US","patent_title":"Method of manufacturing magnetoresistive element"},{"patent_id":"8677608","patent_type":"utility","patent_number":"8677608","patent_country":"US","patent_title":"Method for manufacturing iron core and apparatus for manufacturing iron core"},{"patent_id":"8677609","patent_type":"utility","patent_number":"8677609","patent_country":"US","patent_title":"Method for producing a circuit-breaker pole part"}],"inventors":[{"patent_id":"8677600","inventor_last_name":"Jr.","inventor_id":"8677600-1"},{"patent_id":"8677600","inventor_last_name":"Singer","inventor_id":"8677600-2"},{"patent_id":"8677601","inventor_last_name":"Millwee","inventor_id":"8677601-1"},{"patent_id":"8677601","inventor_last_name":"Shay","inventor_id":"8677601-2"},{"patent_id":"8677601","inventor_last_name":"Majkrzak","inventor_id":"8677601-3"},{"patent_id":"8677601","inventor_last_name":"Young","inventor_id":"8677601-4"},{"patent_id":"8677601","inventor_last_name":"Kupumbati","inventor_id":"8677601-5"},{"patent_id":"8677602","inventor_last_name":"Dayton","inventor_id":"8677602-1"},{"patent_id":"8677602","inventor_last_name":"Boutillette","inventor_id":"8677602-2"},{"patent_id":"8677603","inventor_last_name":"Reynolds","inventor_id":"8677603-1"},{"patent_id":"8677604","inventor_last_name":"Kando","inventor_id":"8677604-1"},{"patent_id":"8677605","inventor_last_name":"Lim","inventor_id":"8677605-1"},{"patent_id":"8677605","inventor_last_name":"Goh","inventor_id":"8677605-2"},{"patent_id":"8677606","inventor_last_name":"Desiron","inventor_id":"8677606-1"},{"patent_id":"8677607","inventor_last_name":"Yanagisawa","inventor_id":"8677607-1"},{"patent_id":"8677607","inventor_last_name":"Hirose","inventor_id":"8677607-2"},{"patent_id":"8677607","inventor_last_name":"Saruki","inventor_id":"8677607-3"},{"patent_id":"8677608","inventor_last_name":"Akita","inventor_id":"8677608-1"},{"patent_id":"8677608","inventor_last_name":"Furusawa","inventor_id":"8677608-2"},{"patent_id":"8677609","inventor_last_name":"Shang","inventor_id":"8677609-1"}],"assignees":[{"patent_id":"8677600","assignee_last_name":"","assignee_id":"ad87f29b5aa3d8c0bcbf86bc0ed69b8d"},{"patent_id":"8677601","assignee_last_name":"","assignee_id":"ba63daad7af208753ccc1e7383c11281"},{"patent_id":"8677602","assignee_last_name":"","assignee_id":"60ab17c614bc80bfbddd5b3c4bae3ea3"},{"patent_id":"8677603","assignee_last_name":null,"assignee_id":null},{"patent_id":"8677604","assignee_last_name":"","assignee_id":"545147830daddf3dbcc15397ee38fc31"},{"patent_id":"8677605","assignee_last_name":"","assignee_id":"dfb22487d8089f003b2b092aeec21823"},{"patent_id":"8677606","assignee_last_name":"","assignee_id":"02b31f7d03b24c5c86e6d365d6b17133"},{"patent_id":"8677607","assignee_last_name":"","assignee_id":"22817f7a619b8a60fd4aec1a5166ddc6"},{"patent_id":"8677608","assignee_last_name":"","assignee_id":"50b9f7425f3c324d06c53ca449075086"},{"patent_id":"8677609","assignee_last_name":"","assignee_id":"847ef656a074289a8fbee02b3d7c6e73"}]}', true);
        $this->assertEquals($expected, $results);
    }

    /**
     * @expectedException ErrorException
     */   public function testQueryDatabaseWithInvalidSorting()
    {
        global $FIELD_SPECS;
        global $PATENT_ENTITY_SPECS;
        $whereClause = "patent.id like '867760%'";
        $whereFieldsUsed = array('patent_id');
        $sort = array(array('inventor_id'=>'asc'));
        $selectFieldsSpecs = array(
            'patent_id' => $FIELD_SPECS['patent_id'],
            'patent_type' => $FIELD_SPECS['patent_type'],
            'patent_number' => $FIELD_SPECS['patent_number'],
            'patent_country' => $FIELD_SPECS['patent_country'],
            'patent_title' => $FIELD_SPECS['patent_title'],
            'inventor_last_name' => $FIELD_SPECS['inventor_last_name'],
            'assignee_last_name' => $FIELD_SPECS['assignee_last_name']
        );
        $dbQuery = new DatabaseQuery();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, $sort);
    }

    public  function testQueryDatabaseAllFieldsMaxPageSize()
    {
        global $FIELD_SPECS;
        global $PATENT_ENTITY_SPECS;
        $selectFieldSpecs = $FIELD_SPECS;
        $options = array('page'=>1, 'per_page'=>10000);
        $dbQuery = new DatabaseQuery();
        $memUsed = memory_get_usage();
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, null, array(), $selectFieldSpecs, null, $options);
        $memUsed = memory_get_usage();
        if (count($results['patents']) < 10000) {
            $this->assertEquals($dbQuery->getTotalFound(), count($results['patents']));
        }
        else {
            $this->assertEquals(10000, count($results['patents']));
            $this->assertGreaterThanOrEqual(10000, $dbQuery->getTotalFound());
        }
    }
}
