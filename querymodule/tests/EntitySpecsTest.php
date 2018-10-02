<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/28/18
 * Time: 5:10 PM
 */
putenv("CONFIG_PATH=".dirname(__FILE__) . "/../../");
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
}
