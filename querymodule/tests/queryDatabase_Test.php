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
        $expected = array(array('patent_number' => '8677600', 'assignee_last_name' => '', 'inventor_last_name' => 'Jr.', 'assignee_id' => 'ad87f29b5aa3d8c0bcbf86bc0ed69b8d', 'patent_title' => 'Methods of dispensing and making porous material with growth enhancing element', 'inventor_id' => '8677600-1', 'patent_country' => 'US', 'patent_id' => '8677600', 'patent_type' => 'utility'), array('patent_number' => '8677600', 'assignee_last_name' => '', 'inventor_last_name' => 'Singer', 'assignee_id' => 'ad87f29b5aa3d8c0bcbf86bc0ed69b8d', 'patent_title' => 'Methods of dispensing and making porous material with growth enhancing element', 'inventor_id' => '8677600-2', 'patent_country' => 'US', 'patent_id' => '8677600', 'patent_type' => 'utility'), array('patent_number' => '8677601', 'assignee_last_name' => '', 'inventor_last_name' => 'Millwee', 'assignee_id' => 'ba63daad7af208753ccc1e7383c11281', 'patent_title' => 'Prosthetic heart valve, prosthetic heart valve assembly and method for making same', 'inventor_id' => '8677601-1', 'patent_country' => 'US', 'patent_id' => '8677601', 'patent_type' => 'utility'), array('patent_number' => '8677601', 'assignee_last_name' => '', 'inventor_last_name' => 'Shay', 'assignee_id' => 'ba63daad7af208753ccc1e7383c11281', 'patent_title' => 'Prosthetic heart valve, prosthetic heart valve assembly and method for making same', 'inventor_id' => '8677601-2', 'patent_country' => 'US', 'patent_id' => '8677601', 'patent_type' => 'utility'), array('patent_number' => '8677601', 'assignee_last_name' => '', 'inventor_last_name' => 'Majkrzak', 'assignee_id' => 'ba63daad7af208753ccc1e7383c11281', 'patent_title' => 'Prosthetic heart valve, prosthetic heart valve assembly and method for making same', 'inventor_id' => '8677601-3', 'patent_country' => 'US', 'patent_id' => '8677601', 'patent_type' => 'utility'), array('patent_number' => '8677601', 'assignee_last_name' => '', 'inventor_last_name' => 'Young', 'assignee_id' => 'ba63daad7af208753ccc1e7383c11281', 'patent_title' => 'Prosthetic heart valve, prosthetic heart valve assembly and method for making same', 'inventor_id' => '8677601-4', 'patent_country' => 'US', 'patent_id' => '8677601', 'patent_type' => 'utility'), array('patent_number' => '8677601', 'assignee_last_name' => '', 'inventor_last_name' => 'Kupumbati', 'assignee_id' => 'ba63daad7af208753ccc1e7383c11281', 'patent_title' => 'Prosthetic heart valve, prosthetic heart valve assembly and method for making same', 'inventor_id' => '8677601-5', 'patent_country' => 'US', 'patent_id' => '8677601', 'patent_type' => 'utility'), array('patent_number' => '8677602', 'assignee_last_name' => '', 'inventor_last_name' => 'Dayton', 'assignee_id' => '60ab17c614bc80bfbddd5b3c4bae3ea3', 'patent_title' => 'Method of making a flexible device shaft with angled spiral wrap', 'inventor_id' => '8677602-1', 'patent_country' => 'US', 'patent_id' => '8677602', 'patent_type' => 'utility'), array('patent_number' => '8677602', 'assignee_last_name' => '', 'inventor_last_name' => 'Boutillette', 'assignee_id' => '60ab17c614bc80bfbddd5b3c4bae3ea3', 'patent_title' => 'Method of making a flexible device shaft with angled spiral wrap', 'inventor_id' => '8677602-2', 'patent_country' => 'US', 'patent_id' => '8677602', 'patent_type' => 'utility'), array('patent_number' => '8677603', 'assignee_last_name' => null, 'inventor_last_name' => 'Reynolds', 'assignee_id' => null, 'patent_title' => 'Drop maker for sap collection system', 'inventor_id' => '8677603-1', 'patent_country' => 'US', 'patent_id' => '8677603', 'patent_type' => 'utility'), array('patent_number' => '8677604', 'assignee_last_name' => '', 'inventor_last_name' => 'Kando', 'assignee_id' => '545147830daddf3dbcc15397ee38fc31', 'patent_title' => 'Method of manufacturing boundary acoustic wave device', 'inventor_id' => '8677604-1', 'patent_country' => 'US', 'patent_id' => '8677604', 'patent_type' => 'utility'), array('patent_number' => '8677605', 'assignee_last_name' => '', 'inventor_last_name' => 'Lim', 'assignee_id' => 'dfb22487d8089f003b2b092aeec21823', 'patent_title' => 'Method for manufacturing sensor unit', 'inventor_id' => '8677605-1', 'patent_country' => 'US', 'patent_id' => '8677605', 'patent_type' => 'utility'), array('patent_number' => '8677605', 'assignee_last_name' => '', 'inventor_last_name' => 'Goh', 'assignee_id' => 'dfb22487d8089f003b2b092aeec21823', 'patent_title' => 'Method for manufacturing sensor unit', 'inventor_id' => '8677605-2', 'patent_country' => 'US', 'patent_id' => '8677605', 'patent_type' => 'utility'), array('patent_number' => '8677606', 'assignee_last_name' => '', 'inventor_last_name' => 'Desiron', 'assignee_id' => '02b31f7d03b24c5c86e6d365d6b17133', 'patent_title' => 'Method for assembling a rotor with permanent magnets', 'inventor_id' => '8677606-1', 'patent_country' => 'US', 'patent_id' => '8677606', 'patent_type' => 'utility'), array('patent_number' => '8677607', 'assignee_last_name' => '', 'inventor_last_name' => 'Yanagisawa', 'assignee_id' => '22817f7a619b8a60fd4aec1a5166ddc6', 'patent_title' => 'Method of manufacturing magnetoresistive element', 'inventor_id' => '8677607-1', 'patent_country' => 'US', 'patent_id' => '8677607', 'patent_type' => 'utility'), array('patent_number' => '8677607', 'assignee_last_name' => '', 'inventor_last_name' => 'Hirose', 'assignee_id' => '22817f7a619b8a60fd4aec1a5166ddc6', 'patent_title' => 'Method of manufacturing magnetoresistive element', 'inventor_id' => '8677607-2', 'patent_country' => 'US', 'patent_id' => '8677607', 'patent_type' => 'utility'), array('patent_number' => '8677607', 'assignee_last_name' => '', 'inventor_last_name' => 'Saruki', 'assignee_id' => '22817f7a619b8a60fd4aec1a5166ddc6', 'patent_title' => 'Method of manufacturing magnetoresistive element', 'inventor_id' => '8677607-3', 'patent_country' => 'US', 'patent_id' => '8677607', 'patent_type' => 'utility'), array('patent_number' => '8677608', 'assignee_last_name' => '', 'inventor_last_name' => 'Akita', 'assignee_id' => '50b9f7425f3c324d06c53ca449075086', 'patent_title' => 'Method for manufacturing iron core and apparatus for manufacturing iron core', 'inventor_id' => '8677608-1', 'patent_country' => 'US', 'patent_id' => '8677608', 'patent_type' => 'utility'), array('patent_number' => '8677608', 'assignee_last_name' => '', 'inventor_last_name' => 'Furusawa', 'assignee_id' => '50b9f7425f3c324d06c53ca449075086', 'patent_title' => 'Method for manufacturing iron core and apparatus for manufacturing iron core', 'inventor_id' => '8677608-2', 'patent_country' => 'US', 'patent_id' => '8677608', 'patent_type' => 'utility'), array('patent_number' => '8677609', 'assignee_last_name' => '', 'inventor_last_name' => 'Shang', 'assignee_id' => '847ef656a074289a8fbee02b3d7c6e73', 'patent_title' => 'Method for producing a circuit-breaker pole part', 'inventor_id' => '8677609-1', 'patent_country' => 'US', 'patent_id' => '8677609', 'patent_type' => 'utility'));
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
        $options = array('page'=>-1);
        $results = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $whereFieldsUsed, $selectFieldsSpecs, null, $options);
        $this->assertGreaterThan(10000, count($results));
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
        $this->assertNotEquals($resultsFirst[0],$resultsSecond[0]);
        $this->assertEquals($resultsSecond[0],$resultsThird[0]);
        $patentIds = array();
        foreach ($resultsSecond as $row) $patentIds[$row['patent_id']] = 1;
        $this->assertEquals(25, count($patentIds));
        $patentIds = array();
        foreach ($resultsThird as $row) $patentIds[$row['patent_id']] = 1;
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
        $expected = json_decode('[{"patent_id":"8677603","patent_type":"utility","patent_number":"8677603","patent_country":"US","patent_title":"Drop maker for sap collection system","inventor_last_name":"Reynolds","assignee_last_name":null,"inventor_id":"8677603-1","assignee_id":null},{"patent_id":"8677606","patent_type":"utility","patent_number":"8677606","patent_country":"US","patent_title":"Method for assembling a rotor with permanent magnets","inventor_last_name":"Desiron","assignee_last_name":"","inventor_id":"8677606-1","assignee_id":"02b31f7d03b24c5c86e6d365d6b17133"},{"patent_id":"8677608","patent_type":"utility","patent_number":"8677608","patent_country":"US","patent_title":"Method for manufacturing iron core and apparatus for manufacturing iron core","inventor_last_name":"Akita","assignee_last_name":"","inventor_id":"8677608-1","assignee_id":"50b9f7425f3c324d06c53ca449075086"},{"patent_id":"8677608","patent_type":"utility","patent_number":"8677608","patent_country":"US","patent_title":"Method for manufacturing iron core and apparatus for manufacturing iron core","inventor_last_name":"Furusawa","assignee_last_name":"","inventor_id":"8677608-2","assignee_id":"50b9f7425f3c324d06c53ca449075086"},{"patent_id":"8677605","patent_type":"utility","patent_number":"8677605","patent_country":"US","patent_title":"Method for manufacturing sensor unit","inventor_last_name":"Lim","assignee_last_name":"","inventor_id":"8677605-1","assignee_id":"dfb22487d8089f003b2b092aeec21823"},{"patent_id":"8677605","patent_type":"utility","patent_number":"8677605","patent_country":"US","patent_title":"Method for manufacturing sensor unit","inventor_last_name":"Goh","assignee_last_name":"","inventor_id":"8677605-2","assignee_id":"dfb22487d8089f003b2b092aeec21823"},{"patent_id":"8677609","patent_type":"utility","patent_number":"8677609","patent_country":"US","patent_title":"Method for producing a circuit-breaker pole part","inventor_last_name":"Shang","assignee_last_name":"","inventor_id":"8677609-1","assignee_id":"847ef656a074289a8fbee02b3d7c6e73"},{"patent_id":"8677602","patent_type":"utility","patent_number":"8677602","patent_country":"US","patent_title":"Method of making a flexible device shaft with angled spiral wrap","inventor_last_name":"Dayton","assignee_last_name":"","inventor_id":"8677602-1","assignee_id":"60ab17c614bc80bfbddd5b3c4bae3ea3"},{"patent_id":"8677602","patent_type":"utility","patent_number":"8677602","patent_country":"US","patent_title":"Method of making a flexible device shaft with angled spiral wrap","inventor_last_name":"Boutillette","assignee_last_name":"","inventor_id":"8677602-2","assignee_id":"60ab17c614bc80bfbddd5b3c4bae3ea3"},{"patent_id":"8677604","patent_type":"utility","patent_number":"8677604","patent_country":"US","patent_title":"Method of manufacturing boundary acoustic wave device","inventor_last_name":"Kando","assignee_last_name":"","inventor_id":"8677604-1","assignee_id":"545147830daddf3dbcc15397ee38fc31"},{"patent_id":"8677607","patent_type":"utility","patent_number":"8677607","patent_country":"US","patent_title":"Method of manufacturing magnetoresistive element","inventor_last_name":"Yanagisawa","assignee_last_name":"","inventor_id":"8677607-1","assignee_id":"22817f7a619b8a60fd4aec1a5166ddc6"},{"patent_id":"8677607","patent_type":"utility","patent_number":"8677607","patent_country":"US","patent_title":"Method of manufacturing magnetoresistive element","inventor_last_name":"Hirose","assignee_last_name":"","inventor_id":"8677607-2","assignee_id":"22817f7a619b8a60fd4aec1a5166ddc6"},{"patent_id":"8677607","patent_type":"utility","patent_number":"8677607","patent_country":"US","patent_title":"Method of manufacturing magnetoresistive element","inventor_last_name":"Saruki","assignee_last_name":"","inventor_id":"8677607-3","assignee_id":"22817f7a619b8a60fd4aec1a5166ddc6"},{"patent_id":"8677600","patent_type":"utility","patent_number":"8677600","patent_country":"US","patent_title":"Methods of dispensing and making porous material with growth enhancing element","inventor_last_name":"Jr.","assignee_last_name":"","inventor_id":"8677600-1","assignee_id":"ad87f29b5aa3d8c0bcbf86bc0ed69b8d"},{"patent_id":"8677600","patent_type":"utility","patent_number":"8677600","patent_country":"US","patent_title":"Methods of dispensing and making porous material with growth enhancing element","inventor_last_name":"Singer","assignee_last_name":"","inventor_id":"8677600-2","assignee_id":"ad87f29b5aa3d8c0bcbf86bc0ed69b8d"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventor_last_name":"Millwee","assignee_last_name":"","inventor_id":"8677601-1","assignee_id":"ba63daad7af208753ccc1e7383c11281"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventor_last_name":"Shay","assignee_last_name":"","inventor_id":"8677601-2","assignee_id":"ba63daad7af208753ccc1e7383c11281"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventor_last_name":"Majkrzak","assignee_last_name":"","inventor_id":"8677601-3","assignee_id":"ba63daad7af208753ccc1e7383c11281"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventor_last_name":"Young","assignee_last_name":"","inventor_id":"8677601-4","assignee_id":"ba63daad7af208753ccc1e7383c11281"},{"patent_id":"8677601","patent_type":"utility","patent_number":"8677601","patent_country":"US","patent_title":"Prosthetic heart valve, prosthetic heart valve assembly and method for making same","inventor_last_name":"Kupumbati","assignee_last_name":"","inventor_id":"8677601-5","assignee_id":"ba63daad7af208753ccc1e7383c11281"}]', true);
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
}
 