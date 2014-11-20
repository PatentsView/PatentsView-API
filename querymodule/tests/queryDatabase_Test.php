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
}
