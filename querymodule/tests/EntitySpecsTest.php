<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/28/18
 * Time: 5:10 PM
 */
putenv("CONFIG_PATH=//var/www/html/current");
require_once dirname(__FILE__) . '/../app/entitySpecs.php';
require_once dirname(__FILE__) . '/../app/DatabaseQuery.php';
require_once dirname(__FILE__) . '/../app/config.php';

class EntitySpecsTest extends PHPUnit_Framework_TestCase
{
    private $db = null;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @dataProvider provideEntitySpec
     **/

    public function test_spec_array($entitySpecs)
    {
        $this->assertNotEquals(0, count($entitySpecs));
    }

    /**
     * @dataProvider provideEntitySpec
     **/
    public function test_join($entitySpecs)
    {
        $first = True;
        $select = "SELECT * FROM ";
        $limit = " WHERE 1=0";
        $baseQuery = "";
        $this->connectToDB();
        foreach ($entitySpecs as $entity) {
            if ($first) {
                $queryToTest = $select . $entity["join"] . $limit;
                $baseQuery = $entity["join"];

                try {
                    $st = $this->db->query($queryToTest, PDO::FETCH_ASSOC);
                    $results = $st->fetchAll();
                    $st->closeCursor();
                    $this->assertEquals(0, count($results));
                } catch (PDOException $e) {
                    $this->fail("Base Query Fail" . $queryToTest);
                }

                $first = False;
                continue;
            }
            $queryToTest = $select . $baseQuery . " " . $entity["join"] . $limit;
            try {
                $st = $this->db->query($queryToTest, PDO::FETCH_ASSOC);
                $results = $st->fetchAll();
                $st->closeCursor();
                $this->assertEquals(0, count($results));
            } catch (PDOException $e) {
                $this->fail("Join Query Fail: " . $queryToTest);
            }
        }
    }

    private function connectToDB()
    {

        $config = Config::getInstance();
        if ($this->db === null) {
            $dbSettings = $config->getDbSettings();
            $this->db = new PDO("mysql:host=$dbSettings[host];dbname=$dbSettings[database];charset=utf8", $dbSettings['user'], $dbSettings['password']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        }

    }

    /**
     * @dataProvider provideSpec
     */
    public function test_field($entitySpecs, $fieldSpecs)
    {
        $first = True;

        $limit = " WHERE 1=0";
        $baseQuery = "";
        $this->connectToDB();
        foreach ($entitySpecs as $entity) {
            foreach ($fieldSpecs as $fieldSpec) {
                if ($fieldSpec["entity_name"] === $entity["entity_name"]) {
                    $select = "SELECT " . $fieldSpec["column_name"] . " FROM ";
                    if ($first) {
                        $queryToTest = $select . $entity["join"] . $limit;
                        $baseQuery = $entity["join"];

                        try {
                            $st = $this->db->query($queryToTest, PDO::FETCH_ASSOC);
                            $results = $st->fetchAll();
                            $st->closeCursor();
                            $this->assertEquals(0, count($results));
                        } catch (PDOException $e) {
                            $this->fail("Field failed for Base Query Fail" . $queryToTest);
                        }
                    } else {
                        $queryToTest = $select . $baseQuery . " " . $entity["join"] . $limit;
                        try {
                            $st = $this->db->query($queryToTest, PDO::FETCH_ASSOC);
                            $results = $st->fetchAll();
                            $st->closeCursor();
                            $this->assertEquals(0, count($results));
                        } catch (PDOException $e) {
                            $this->fail("Join Query Fail: " . $queryToTest);
                        }
                    }
                }
            }
            $first = False;
        }
    }

    /**
     * @dataProvider provideFieldRefSpec
     */
    public function testFieldList($referenceFieldList, $fieldSpecs)
    {
        foreach ($referenceFieldList["fields"] as $apiField) {
            $this->assertArrayHasKey($apiField, $fieldSpecs);
        }
    }

    public
    function provideEntitySpec()
    {
        global $PATENT_ENTITY_SPECS, $ASSIGNEE_ENTITY_SPECS, $INVENTOR_ENTITY_SPECS, $LOCATION_ENTITY_SPECS, $USPC_ENTITY_SPECS, $CPC_ENTITY_SPECS, $NBER_ENTITY_SPECS, $CPC_GROUP_ENTITY_SPECS;

        return [[$PATENT_ENTITY_SPECS], [$ASSIGNEE_ENTITY_SPECS], [$INVENTOR_ENTITY_SPECS], [$LOCATION_ENTITY_SPECS], [$USPC_ENTITY_SPECS], [$CPC_ENTITY_SPECS], [$NBER_ENTITY_SPECS], [$CPC_GROUP_ENTITY_SPECS]];
    }

    public
    function provideSpec()
    {
        global $PATENT_ENTITY_SPECS, $ASSIGNEE_ENTITY_SPECS, $INVENTOR_ENTITY_SPECS, $LOCATION_ENTITY_SPECS, $USPC_ENTITY_SPECS, $CPC_ENTITY_SPECS, $NBER_ENTITY_SPECS, $CPC_GROUP_ENTITY_SPECS, $PATENT_FIELD_SPECS, $ASSIGNEE_FIELD_SPECS, $INVENTOR_FIELD_SPECS, $LOCATION_FIELD_SPECS, $USPC_FIELD_SPECS, $CPC_FIELD_SPECS, $NBER_FIELD_SPECS, $CPC_GROUP_FIELD_SPECS;

        return [[$PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS], [$ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS], [$INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS], [$LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS], [$USPC_ENTITY_SPECS, $USPC_FIELD_SPECS], [$CPC_ENTITY_SPECS, $CPC_FIELD_SPECS], [$NBER_ENTITY_SPECS, $NBER_FIELD_SPECS], [$CPC_GROUP_ENTITY_SPECS, $CPC_GROUP_FIELD_SPECS]];
    }

    public function provideFieldRefSpec()
    {
        global $ASSIGNEE_FIELD_SPECS;
        $assignee_master_fields_content = file_get_contents(dirname(__FILE__) . '/testspec/assignee_master_field_list.json');
        $assignee_master_fields_list = json_decode($assignee_master_fields_content, true);

        global $PATENT_FIELD_SPECS;
        $patent_master_fields_list = json_decode(file_get_contents(dirname(__FILE__) . '/testspec/patent_master_field_list.json'), true);

        global $INVENTOR_FIELD_SPECS;
        $inventor_master_fields_list = json_decode(file_get_contents(dirname(__FILE__) . '/testspec/inventor_master_field_list.json'), true);
        global $CPC_FIELD_SPECS;
        $cpc_master_fields_list = json_decode(file_get_contents(dirname(__FILE__) . '/testspec/cpc_master_field_list.json'), true);
        global $NBER_FIELD_SPECS;
        $nber_master_fields_list = json_decode(file_get_contents(dirname(__FILE__) . '/testspec/nber_master_field_list.json'), true);
        global $LOCATION_FIELD_SPECS;
        $location_master_fields_list = json_decode(file_get_contents(dirname(__FILE__) . '/testspec/location_master_field_list.json'), true);
        global $USPC_FIELD_SPECS;
        $uspc_master_fields_list = json_decode(file_get_contents(dirname(__FILE__) . '/testspec/uspc_master_field_list.json'), true);

        return [[$assignee_master_fields_list, $ASSIGNEE_FIELD_SPECS], [$patent_master_fields_list, $PATENT_FIELD_SPECS], [$inventor_master_fields_list, $INVENTOR_FIELD_SPECS], [$cpc_master_fields_list, $CPC_FIELD_SPECS],  [$nber_master_fields_list, $NBER_FIELD_SPECS], [$location_master_fields_list, $LOCATION_FIELD_SPECS], [$uspc_master_fields_list, $USPC_FIELD_SPECS]];
    }
}