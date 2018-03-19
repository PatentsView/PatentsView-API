<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/MaxEntitiesLoadedException.php';

class DatabaseQuery implements \JsonStreamingParser\Listener
{
    private $entityTotalCounts = array();

    private $entitySpecs = array();
    private $entityGroupVars = array();
    private $fieldSpecs;
    private $entitySpecificWhereClauses = array();
    private $start_time;
    private $end_time;
    private $selectFieldSpecs;
    private $sortFieldsUsed;
    private $sortFieldsUsedSec;
    private $queryDefId;
    private $db = null;
    private $errorHandler = null;
    private $entityIDs = array();
    private $matchedSubentitiesOnly = false;
    private $include_subentity_total_counts = false;
    private $sort_by_subentity_counts = false;

    private $dataArray = array();
    private $question_marks = array();
    private $nextSequence = -1;
    private $supportDatabase = "";

    public function __construct($entitySpecs, $fieldSpecs, $queryDefId)
    {
        global $config;
        $dbSettings = $config->getDbSettings();
        $this->supportDatabase = $dbSettings['supportDatabase'];

        $memUsed = memory_get_usage();
        $this->errorHandler = ErrorHandler::getHandler();
        $this->entitySpecs = $entitySpecs;
        $this->fieldSpecs = $fieldSpecs;
        $this->queryDefId = $queryDefId;
        $this->nextSequence = 1;
    }

    public
    function retrieveEntityIdForSolr($queryDefId, $start, $rows, $getEverything = false)
    {
        $this->connectToDB();
        $where = "QueryDefID = $queryDefId  order by Sequence asc";
        if (!$getEverything) {
            $where .= " LIMIT $rows OFFSET $start";
        }
        $results = $this->runQuery('DISTINCT EntityId', $this->supportDatabase . '.QueryResults', $where, null);
//        $count_results = $this->runQuery('COUNT(DISTINCT EntityId)', $this->supportDatabase . '.QueryResultsBase', "QueryDefID = $queryDefId", null);

        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result["EntityId"];
        }
        return $ids;
    }

    public function connectToDB()
    {
        global $config;
        if ($this->db === null) {
            $dbSettings = $config->getDbSettings();
            try {
                $this->db = new PDO("mysql:host=$dbSettings[host];dbname=$dbSettings[database];charset=utf8", $dbSettings['user'], $dbSettings['password']);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {

                $this->errorHandler->sendError(500, "Failed to connect to database: $dbSettings[database].", $e);
                throw new $e;
            }

        }

    }

    private function runQuery($select, $from, $where, $order, $fetch_type = PDO::FETCH_ASSOC)
    {

        $this->connectToDB();

        if (strlen($where) > 0) $where = "WHERE $where ";
        if (strlen($order) > 0) $order = "ORDER BY $order";
        $sqlQuery = "SELECT $select FROM $from $where $order";
        $this->errorHandler->getLogger()->debug($sqlQuery);

        try {
            $st = $this->db->query("$sqlQuery", $fetch_type);
            $results = $st->fetchAll();
            $st->closeCursor();
        } catch (Exception $e) {

            $this->errorHandler->sendError(500, "Query execution failed.", $e);
            throw new $e;
        }
        //file_put_contents('php://stderr', print_r(count($results), TRUE));
        //file_put_contents('php://stderr', print_r("\n", TRUE));
        return $results;
    }

    public function rollbackTransaction()
    {
        $this->db->rollback();
    }

    public
    function checkQueryDef()
    {
        $this->connectToDB();
        $results = $this->runQuery('QueryDefID, QueryString', $this->supportDatabase . '.QueryDef', "QueryDefID = $this->queryDefId", null);
        return count($results);
    }

    public
    function addQueryDef($queryDefId, $queryString)
    {
        $insertStatement = $this->supportDatabase . '.QueryDef (QueryDefId, QueryString) VALUES (:queryDefId, :whereClause)';
        $this->runInsert($insertStatement, array(':queryDefId' => $queryDefId, ':whereClause' => $queryString));
    }

    private function runInsert($insert, $params)
    {
        $this->connectToDB();

        $sqlStatement = "INSERT INTO $insert";
        $this->errorHandler->getLogger()->debug($sqlStatement);
        $this->errorHandler->getLogger()->debug($params);

        $counto = 0;
        $maxTriesy = 3;
        do {
            try {
                $st = $this->db->prepare($sqlStatement);
                $results = $st->execute($params);
                $st->closeCursor();
                break;
            } catch (Exception $e) {
                if ($counto == $maxTriesy) {
                    $this->errorHandler->sendError(500, "Insert execution failed.", $e);
                    throw new $e;
                    break;
                }
                usleep(1000000);
                continue;
            }
            break;
        } while ($counto < $maxTriesy);

        return $results;
    }

    public
    function startDocument()
    {
        $this->startTransaction();
        $this->dataArray = array();
        $this->question_marks = array();
        $this->start_time = microtime(true);
        // TODO: Implement startDocument() method.
    }

    public function startTransaction()
    {
        $this->db->beginTransaction();
    }

    public
    function endDocument()
    {
        if (count($this->dataArray) > 1) {
            $this->loadEntityID();
        }

        $this->dataArray = array();
        $this->commitTransaction();
        // TODO: Implement endDocument() method.
    }

    public function loadEntityID($data = null, $question_marks = null)
    {
        $datafields = array('QueryDefId', 'Sequence', 'EntityId');
        $keyField = $this->entitySpecs[0]["solr_fetch_id"];
        $insertData = array();
        if (!$data)
            $data = $this->dataArray;
        if (!$question_marks)
            $question_marks = $this->question_marks;
        // foreach ($data as $solrDocument) {
//            $data_array = array("QueryDefId" => $solrDocument["queryDefId"], "Sequence" => $solrDocument["sequence"], "EntityId" =>$solrDocument["sequence"]);
//            if (array_key_exists("secondaryKeyField", $fieldPresence)) {
//                $secField = $fieldPresence["secondaryKeyField"];
//                $data_array["SecondaryEntityId"] = $solrDocument->doclist->docs[0]->$secField;
//            } else {
//                $data_array["SecondaryEntityId"] = $solrDocument->groupValue;
//            }
//
//            $insertData[] = $data_array;
//            $sequenceStart += 1;
//        }
        $this->connectToDB();

//        $question_marks = array();
//        $insert_values = array();
////        foreach ($data as $d) {
////            $question_marks[] = '(' . placeholders('?', sizeof($d)) . ')';
////            $insert_values = array_merge($insert_values, array_values($d));
////        }

        $sql = "INSERT INTO " . $this->supportDatabase . ".QueryResults" . "(" . implode(",", $datafields) . ") VALUES " .
            implode(',', $question_marks);

        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage();
            throw $e;
        }
        //$this->commitTransaction();
    }

    public function commitTransaction()
    {
        $this->db->commit();
    }

    public
    function startObject()
    {

        // TODO: Implement startObject() method.
    }

    public
    function endObject()
    {
        // TODO: Implement endObject() method.
    }

    public
    function startArray()
    {
        // TODO: Implement startArray() method.
    }

    public
    function endArray()
    {
        // TODO: Implement endArray() method.
    }

    /**
     * @param string $key
     */
    public
    function key($key)
    {
        if ($key == $this->entitySpecs[0]["solr_fetch_id"]) {
            $this->rightKey = true;
        } else {
            $this->rightKey = false;
        }

        // TODO: Implement key() method.
    }

    /**
     * Value may be a string, integer, boolean, etc.
     * @param mixed $value
     */
    public
    function value($value)
    {
        global $config;
        if (!is_array($value)) {
            if ($this->rightKey && !array_key_exists($value, $this->entityIDs)) {
                $this->nextSequence += 1;
                $this->dataArray[] = $this->queryDefId;
                $this->dataArray[] = $this->nextSequence;
                $this->dataArray[] = $value;
                $this->entityIDs[$value] = 1;
                $this->question_marks[] = '(' . placeholders('?', 3) . ')';
                if ($this->nextSequence % $config->getMaxPageSize() == 0) {
                    $this->loadEntityID();

                    $time_elapsed = microtime(true) - $this->start_time;
                    $this->dataArray = array();
                    $this->question_marks = array();
                    if ($this->nextSequence >= $config->getQueryResultLimit()) {
                        throw new MaxEntitiesLoadedException("Entities loaded exceeds max limit");
                    }
                }

            }
        }
//        if (count($this->dataArray) > 10000) {
//            $this->loadEntityID();
//            $this->dataArray = array();
//        }

    }

    /**
     * @param string $whitespace
     */
    public
    function whitespace($whitespace)
    {
        // TODO: Implement whitespace() method.
    }

    private
    function buildSelectString()
    {
        $selectString = '';

        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($selectString != '')
                $selectString .= ', ';
            $selectString .= getDBField($this->fieldSpecs, $apiField) . " as $apiField";
        }

        return $selectString;
    }
}

function placeholders($text, $count = 0, $separator = ",")
{
    $result = array();
    if ($count > 0) {
        for ($x = 0; $x < $count; $x++) {
            $result[] = $text;
        }
    }

    return implode($separator, $result);
}