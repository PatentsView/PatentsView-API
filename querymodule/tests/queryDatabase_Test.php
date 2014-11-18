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
        $whereClause = "patent.patent_number like '820260%'";
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
        $expected = json_decode('{"patents":[{"patent_id":"8202600","patent_type":"utility","patent_number":"8202600","patent_title":"Artificial leather, base to be used in the leather, and processes for production of both"},{"patent_id":"8202601","patent_type":"utility","patent_number":"8202601","patent_title":"Honeycomb structure and manufacturing method of the honeycomb structure"},{"patent_id":"8202602","patent_type":"utility","patent_number":"8202602","patent_title":"Honeycomb segment with spacer and honeycomb structure"},{"patent_id":"8202603","patent_type":"utility","patent_number":"8202603","patent_title":"Elastic sheet structure"},{"patent_id":"8202604","patent_type":"utility","patent_number":"8202604","patent_title":"Pneumatic tire and method of production of same"},{"patent_id":"8202605","patent_type":"utility","patent_number":"8202605","patent_title":"Absorbent paper product having non-embossed surface features"},{"patent_id":"8202606","patent_type":"utility","patent_number":"8202606","patent_title":"Insulation structure for resistor grids"},{"patent_id":"8202607","patent_type":"utility","patent_number":"8202607","patent_title":"Nano diamond containing intermediate transfer members"},{"patent_id":"8202608","patent_type":"utility","patent_number":"8202608","patent_title":"Electret composition and method for printing"},{"patent_id":"8202609","patent_type":"utility","patent_number":"8202609","patent_title":"Absorbent material with wet strength containing wax"}],"inventors":[{"patent_id":"8202600","inventor_last_name":"Okada","inventor_id":"15417"},{"patent_id":"8202600","inventor_last_name":"Ichihashi","inventor_id":"15418"},{"patent_id":"8202601","inventor_last_name":"Ido","inventor_id":"15421"},{"patent_id":"8202601","inventor_last_name":"Ohno","inventor_id":"15419"},{"patent_id":"8202601","inventor_last_name":"Kunieda","inventor_id":"15420"},{"patent_id":"8202602","inventor_last_name":"Shindo","inventor_id":"15422"},{"patent_id":"8202603","inventor_last_name":"Chang","inventor_id":"15423"},{"patent_id":"8202604","inventor_last_name":"Tomoi","inventor_id":"15424"},{"patent_id":"8202605","inventor_last_name":"Ostendorf","inventor_id":"15425"},{"patent_id":"8202605","inventor_last_name":"Spitzer","inventor_id":"15426"},{"patent_id":"8202606","inventor_last_name":"Krahn","inventor_id":"15427"},{"patent_id":"8202607","inventor_last_name":"Wu","inventor_id":"15428"},{"patent_id":"8202608","inventor_last_name":"Senft","inventor_id":"15429"},{"patent_id":"8202608","inventor_last_name":"Thayer","inventor_id":"15430"},{"patent_id":"8202609","inventor_last_name":"Ducker","inventor_id":"15431"},{"patent_id":"8202609","inventor_last_name":"Harlen","inventor_id":"15432"},{"patent_id":"8202609","inventor_last_name":"Varney","inventor_id":"15433"}],"assignees":[{"patent_id":"8202600","assignee_last_name":null,"assignee_id":"276"},{"patent_id":"8202601","assignee_last_name":null,"assignee_id":"4228"},{"patent_id":"8202602","assignee_last_name":null,"assignee_id":"5988"},{"patent_id":"8202603","assignee_last_name":null,"assignee_id":"713"},{"patent_id":"8202604","assignee_last_name":null,"assignee_id":"5144"},{"patent_id":"8202605","assignee_last_name":null,"assignee_id":"3363"},{"patent_id":"8202606","assignee_last_name":null,"assignee_id":"668"},{"patent_id":"8202607","assignee_last_name":null,"assignee_id":"5607"},{"patent_id":"8202608","assignee_last_name":null,"assignee_id":null},{"patent_id":"8202609","assignee_last_name":null,"assignee_id":"5485"}]}', true);
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
        $whereClause = "patent.patent_number like '82%'";
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
        $whereClause = "patent.patent_number like '820260%'";
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
        $expected = json_decode('{"patents":[{"patent_id":"8202600","patent_type":"utility","patent_number":"8202600","patent_title":"Artificial leather, base to be used in the leather, and processes for production of both"},{"patent_id":"8202601","patent_type":"utility","patent_number":"8202601","patent_title":"Honeycomb structure and manufacturing method of the honeycomb structure"},{"patent_id":"8202602","patent_type":"utility","patent_number":"8202602","patent_title":"Honeycomb segment with spacer and honeycomb structure"},{"patent_id":"8202603","patent_type":"utility","patent_number":"8202603","patent_title":"Elastic sheet structure"},{"patent_id":"8202604","patent_type":"utility","patent_number":"8202604","patent_title":"Pneumatic tire and method of production of same"},{"patent_id":"8202605","patent_type":"utility","patent_number":"8202605","patent_title":"Absorbent paper product having non-embossed surface features"},{"patent_id":"8202606","patent_type":"utility","patent_number":"8202606","patent_title":"Insulation structure for resistor grids"},{"patent_id":"8202607","patent_type":"utility","patent_number":"8202607","patent_title":"Nano diamond containing intermediate transfer members"},{"patent_id":"8202608","patent_type":"utility","patent_number":"8202608","patent_title":"Electret composition and method for printing"},{"patent_id":"8202609","patent_type":"utility","patent_number":"8202609","patent_title":"Absorbent material with wet strength containing wax"}],"inventors":[{"patent_id":"8202600","inventor_last_name":"Okada","inventor_id":"15417"},{"patent_id":"8202600","inventor_last_name":"Ichihashi","inventor_id":"15418"},{"patent_id":"8202601","inventor_last_name":"Ohno","inventor_id":"15419"},{"patent_id":"8202601","inventor_last_name":"Kunieda","inventor_id":"15420"},{"patent_id":"8202601","inventor_last_name":"Ido","inventor_id":"15421"},{"patent_id":"8202602","inventor_last_name":"Shindo","inventor_id":"15422"},{"patent_id":"8202603","inventor_last_name":"Chang","inventor_id":"15423"},{"patent_id":"8202604","inventor_last_name":"Tomoi","inventor_id":"15424"},{"patent_id":"8202605","inventor_last_name":"Ostendorf","inventor_id":"15425"},{"patent_id":"8202605","inventor_last_name":"Spitzer","inventor_id":"15426"},{"patent_id":"8202606","inventor_last_name":"Krahn","inventor_id":"15427"},{"patent_id":"8202607","inventor_last_name":"Wu","inventor_id":"15428"},{"patent_id":"8202608","inventor_last_name":"Senft","inventor_id":"15429"},{"patent_id":"8202608","inventor_last_name":"Thayer","inventor_id":"15430"},{"patent_id":"8202609","inventor_last_name":"Ducker","inventor_id":"15431"},{"patent_id":"8202609","inventor_last_name":"Harlen","inventor_id":"15432"},{"patent_id":"8202609","inventor_last_name":"Varney","inventor_id":"15433"}],"assignees":[{"patent_id":"8202600","assignee_last_name":null,"assignee_id":"276"},{"patent_id":"8202601","assignee_last_name":null,"assignee_id":"4228"},{"patent_id":"8202602","assignee_last_name":null,"assignee_id":"5988"},{"patent_id":"8202603","assignee_last_name":null,"assignee_id":"713"},{"patent_id":"8202604","assignee_last_name":null,"assignee_id":"5144"},{"patent_id":"8202605","assignee_last_name":null,"assignee_id":"3363"},{"patent_id":"8202606","assignee_last_name":null,"assignee_id":"668"},{"patent_id":"8202607","assignee_last_name":null,"assignee_id":"5607"},{"patent_id":"8202608","assignee_last_name":null,"assignee_id":null},{"patent_id":"8202609","assignee_last_name":null,"assignee_id":"5485"}]}', true);
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
}
