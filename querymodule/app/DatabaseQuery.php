<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';
error_reporting(E_ALL);
require_once(dirname(__FILE__) . "/Exceptions/QueryException.php");

use Aws\DynamoDb\Marshaler;

class DatabaseQuery
{
    private $entityTotalCounts = array();

    private $entitySpecs = array();
    private $entityGroupVars = array();
    private $fieldSpecs;
    private $entitySpecificWhereClauses = array();

    private $selectFieldSpecs;
    private $sortFieldsUsed;
    private $sortFieldsUsedSec;

    private $db = null;
    private $mongoClient = null;
    private $dynamoClient = null;
    private $errorHandler = null;

    private $matchedSubentitiesOnly = false;
    private $include_subentity_total_counts = false;
    private $sort_by_subentity_counts = false;

    private $supportDatabase = "";

    public function getTotalCounts()
    {
        return $this->entityTotalCounts;
    }

    public function queryDatabase(array $entitySpecs, array $fieldSpecs, $whereClause, array $whereFieldsUsed, array $entitySpecificWhereClauses, $onlyAndsWereUsed, array $selectFieldSpecs, array $sortParam = null, array $options = null)
    {
        global $config;
        $dbSettings = $config->getDbSettings();
        $this->supportDatabase = $dbSettings['supportDatabase'];

        $memUsed = memory_get_usage();
        $this->errorHandler = ErrorHandler::getHandler();
        $page = 1;
        $perPage = 25;
        $this->sortFieldsUsed = array();
        $this->sortFieldsUsedSec = array();
        $this->entitySpecificWhereClauses = $entitySpecificWhereClauses;

        $this->entitySpecs = $entitySpecs;
        $this->fieldSpecs = $fieldSpecs;
        $this->setupGroupVars();
        $this->whereFieldsUsed = $whereFieldsUsed;


        if ($options != null) {

            if (array_key_exists('page', $options)) {
                $page = $options['page'];
            }

            if (array_key_exists('per_page', $options))
                if (($options['per_page'] > $config->getMaxPageSize()) or ($options['per_page'] < 1)) {
                    $this->errorHandler->getLogger()->debug("Page size too big");
                    throw new \Exceptions\QueryException("QR1", array($config->getMaxPageSize()));

                } else
                    $perPage = $options['per_page'];

            if (array_key_exists('matched_subentities_only', $options) && strtolower($options['matched_subentities_only']) != "false") {
                $this->matchedSubentitiesOnly = strtolower($options['matched_subentities_only']);

                # When the matched_subentities_only option is used, we need to check that all the criteria were 'and'ed together
//                if ($this->matchedSubentitiesOnly && !$onlyAndsWereUsed)
//                    $this->errorHandler->sendError(400, "When using the 'matched_subentities_only' option, the query criteria cannot contain any 'or's.", $options);
            }

            if (array_key_exists('include_subentity_total_counts', $options) && strtolower($options['include_subentity_total_counts']) != "false") {
                $this->include_subentity_total_counts = strtolower($options['include_subentity_total_counts']);
            }

            if (array_key_exists('sort_by_subentity_counts', $options) && array_key_exists($options['sort_by_subentity_counts'], $selectFieldSpecs)) {
                $this->sort_by_subentity_counts = strtolower($options['sort_by_subentity_counts']);
            } elseif (array_key_exists('sort_by_subentity_counts', $options) && !array_key_exists($options['sort_by_subentity_counts'], $selectFieldSpecs)) {
                $this->errorHandler->getLogger()->debug(vsprintf("Sorting field %s is not in the output field list.", array($options['sort_by_subentity_counts'])));
                throw new \Exceptions\QueryException("QR2", array($options['sort_by_subentity_counts']));
            }
        }
        $this->selectFieldSpecs = $selectFieldSpecs;
        $this->initializeGroupVars();
        $this->determineSelectFields();
        $from = $this->buildFrom($whereFieldsUsed, $this->selectFieldSpecs, $this->sortFieldsUsed);
        $sortString = $this->buildSortString($sortParam);
        $countInsertSelect = "";
        $whereGroup = "";
        $secSortFields = explode(', ', $sortString);
        if ($this->sort_by_subentity_counts) {
            foreach ($secSortFields as $secSortField) {
                preg_match('/' . getDBField($this->fieldSpecs, $this->sort_by_subentity_counts) . '/', $secSortField, $matches);
                if ($matches) {
                    $secSortArray = explode(' ', $secSortField);
                    $sortString = preg_replace('/' . $secSortArray[0] . '/', 'count(distinct ' . $secSortArray[0] . ')', $sortString);
                    $whereGroup = ' GROUP BY ' . getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']) . ' ';
                    $countInsertSelect = 'count(distinct ' . $secSortArray[0] . ')';

                }
            }
        }

        $selectFields = array();
        foreach ($selectFieldSpecs as $selectFieldSpec) {
            $selectFields[] = $selectFieldSpec["column_name"];
        }
        $selectString = implode(", ", $selectFields);
        // Get the QueryDefId for this where clause
        $query_string = "key->" . $this->entitySpecs[0]['keyId'] . "::query->$whereClause.$whereGroup::sort->$sortString::select->$selectString";
        // Get the QueryDefId for this where clause
        $stringToHash = "key->" . $this->entitySpecs[0]['keyId'] . "::query->$whereClause.$whereGroup::sort->$sortString";
        $whereHash = crc32($stringToHash);   // Using crc32 rather than md5 since we only have 32-bits to work with.
        $queryDefId = sprintf('%s', $whereHash);
        $this->connectToDB();
        $mongoSettings = $config->getMongoSettings();
        $cache_collection = $mongoSettings["mongo_collection"];
        $document = null;
        try {
            $marshaler = new Marshaler();
            $dynamoSettings = $config->getDynamoSettings();
            $key = $marshaler->marshalJson(json_encode(['query_string' => $query_string]));
            $params = ['TableName' => $dynamoSettings['table'], "Key" => $key];
            $udocument = $this->dynamoClient->getItem($params);

        } catch (MongoDB\Driver\Exception\AuthenticationException $e) {
            $this->errorHandler->getLogger()->debug($e->getMessage());
        }
        if (array_key_exists("Item", $udocument)) {
            $document = json_decode($marshaler->unmarshalJson($udocument['Item']));
            return $document;

        }

        $county = 0;
        $maxTries = 3;
        do {
            // If the query results for this where clause don't already exist, then we need to run the
            // query and cache the primary entity IDs.

            $results = $this->runQuery('QueryDefID, QueryString', $this->supportDatabase . '.QueryDef', "QueryDefID=$queryDefId", null);
            //TODO Need to handle a hash collision
            if ($results === false) {
                $this->rollbackTransaction();
                $county++;
                if ($county == $maxTries) {
                    $this->errorHandler->getLogger()->debug("Error during cache Load");
                    throw new \Exceptions\QueryException("QDI1", array());
                }
                usleep(500000);
                continue;
            }
            if (count($results) == 0) {
                // Add an entry for the query

                $this->startTransaction();
                $cacheInsertStatement = $this->supportDatabase . '.QueryDef (QueryDefId, QueryString) VALUES (:queryDefId, :whereClause)';

                // Get all the primary entity IDs and insert into the cached results table
                #Todo: Optimization issue: when there is no where clause perhaps we should disallow it, otherwise it can be really slow depending on the primary entity. For patents on the full DB it takes over 7m - stopped waiting.
                $insertStatement = $this->supportDatabase . '.QueryResults (QueryDefId, Sequence, EntityId)';
                $selectPrimaryEntityIdsString =
                    "$queryDefId, @row_number:=@row_number+1 as sequence, XX.XXid as " . $this->entityGroupVars[0]['keyId'];
                if (strlen($whereClause) > 0) $whereInsert = "WHERE $whereClause $whereGroup "; else $whereInsert = "";
                if (strlen($sortString) > 0) $sortInsert = "ORDER BY $sortString "; else $sortInsert = '';
                $fromInsert = $this->buildFrom($whereFieldsUsed, array($entitySpecs[0]['keyId'] => $this->fieldSpecs[$entitySpecs[0]['keyId']]), $this->sortFieldsUsed);
                $this->fromSubEntity = $fromInsert;
                $insert_status = $this->runInsertSelect($insertStatement,
                    $selectPrimaryEntityIdsString,
                    '(SELECT distinct ' . getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']) . ' as XXid FROM ' .
                    $fromInsert . ' ' . $whereInsert . $sortInsert . ' limit ' . $config->getQueryResultLimit() . ') XX, (select @row_number:=0) temprownum',
                    null,
                    null, $dbSettings);
                if ($insert_status == 0) {
                    $this->runInsert($cacheInsertStatement, array(':queryDefId' => $queryDefId, ':whereClause' => $stringToHash));
                }
                $this->commitTransaction();
                break;
            }
            break;

        } while ($county < $maxTries);

        // First find out how many there are in the complete set.
        $selectStringForEntity = 'count(QueryDefId) as total_found';
        $fromEntity = $this->supportDatabase . '.QueryResults qr';
        $whereEntity = "qr.QueryDefId=$queryDefId";
        $countResults = $this->runQuery($selectStringForEntity, $fromEntity, $whereEntity, null);
        if (!$countResults) {

        }
        $this->entityTotalCounts[$entitySpecs[0]['entity_name']] = intval($countResults[0]['total_found']);


        // Get the primary entities
        $results = array();
        $selectStringForEntityConfig = $this->buildSelectStringForEntity($this->entitySpecs[0]);
        $selectStringForEntity = $selectStringForEntityConfig["select"];
        $additionalJoinsForEntity = $selectStringForEntityConfig["additional_joins"];
        $fromEntity = $this->entitySpecs[0]['join'] .
            ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId' . $additionalJoinsForEntity;
        $whereEntity = "qr.QueryDefId=$queryDefId";
        if ($perPage < $this->entityTotalCounts[$entitySpecs[0]['entity_name']])
            $whereEntity .= ' and ((qr.Sequence>=' . ((($page - 1) * $perPage) + 1) . ') and (qr.Sequence<=' . $page * $perPage . '))';
        $sortEntity = 'qr.sequence';
        $entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, $sortEntity);
        $results[$this->entitySpecs[0]['group_name']] = $entityResults;
        unset($entityResults);

        $allFieldsUsed = array_merge($whereFieldsUsed, array_keys(array($entitySpecs[0]['keyId'] => $this->fieldSpecs[$entitySpecs[0]['keyId']])), $this->sortFieldsUsed);
        $additionalJoins = array();
        foreach (array_slice($this->entitySpecs, 1) as $entitySpec) {
            $tempSelectConfig = $this->buildSelectStringForEntityReturnApiField($entitySpec);
            $tempSelect = $tempSelectConfig["select"];
            $allFieldsUsed = array_merge($allFieldsUsed, $tempSelect);
            $additionalJoins = array_merge($additionalJoins, $tempSelectConfig["additional_joins"]);
        }
        $allFieldsUsed = array_unique($allFieldsUsed);
        $groupsCheckTotalCount = array();
        foreach ($allFieldsUsed as $fieldUsed) {
            $groupsCheckTotalCount[] = $this->fieldSpecs[$fieldUsed]['entity_name'];
        }
        $groupsCheckTotalCount = array_unique($groupsCheckTotalCount);

        $fromSubEntity = $this->buildFrom($allFieldsUsed, array($entitySpecs[0]['keyId'] => $this->fieldSpecs[$entitySpecs[0]['keyId']]), $this->sortFieldsUsed);
        $fromSubEntity .= ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';


        // Loop through the subentities and get them.
        foreach (array_slice($this->entitySpecs, 1) as $entitySpec) {
            $tempSelectConfig = $this->buildSelectStringForEntity($entitySpec);
            $tempSelect = $tempSelectConfig["select"];
            $additionalJoins = $tempSelectConfig["additional_joins"];
            if ($tempSelect != '') { // If there aren't any fields to get back, then skip the group.
                $selectStringForEntity = getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . ' as ' . $this->entitySpecs[0]['keyId'];
                $selectStringForEntity .= ", $tempSelect";
                $fromEntity = $this->entitySpecs[0]['join'] .
                    ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';
                $fromEntity .= ' ' . $entitySpec['join'] . ' ' . $additionalJoins;
                $whereEntity = "qr.QueryDefId=$queryDefId";
                if ($perPage < $this->entityTotalCounts[$entitySpecs[0]['entity_name']])
                    $whereEntity .= ' and ((qr.Sequence>=' . ((($page - 1) * $perPage) + 1) . ') and (qr.Sequence<=' . $page * $perPage . '))';

                if ($this->matchedSubentitiesOnly) {
                    $whereEntity .= ' and ' . $whereClause;
                    $fromEntity = $fromSubEntity;
                }
                if (array_key_exists($entitySpec['group_name'], $this->sortFieldsUsedSec)) {
                    $sortStringSec = implode(',', $this->sortFieldsUsedSec[$entitySpec['group_name']]);
                    $entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, $sortStringSec);
                } else {
                    $entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, null);
                }
                $results[$entitySpec['group_name']] = $entityResults;
                unset($entityResults);

                if ($this->include_subentity_total_counts) {
                    // Count of all subentities for all primary entities.
                    $selectStringForEntity = 'count(distinct ' . getDBField($this->fieldSpecs, $entitySpec['distinctCountId']) . ') as subentity_count';
                    $fromEntity = $fromSubEntity;
                    if (!in_array($entitySpec['entity_name'], $groupsCheckTotalCount)) {
                        $fromEntity .= ' ' . $entitySpec['join'];
                    }
                    $whereEntity = "qr.QueryDefId=$queryDefId";
                    $whereEntity .= ' and ' . $whereClause;
                    $countResults = $this->runQuery($selectStringForEntity, $fromEntity, $whereEntity, null);
                    if ($countResults === false) {
                        $this->errorHandler->getLogger()->debug($e);
                    }
                    $this->entityTotalCounts[$entitySpec['entity_name']] = intval($countResults[0]['subentity_count']);

                }
            }
        }
        return $results;
    }

    private function setupGroupVars()
    {

        $this->entityGroupVars = $this->entitySpecs;
        foreach ($this->entityGroupVars as &$group) {
            $name = $group['entity_name'];
            $group['hasId'] = "alreadyHas{$name}Id";
            $group['hasFields'] = "has{$name}Fields";
        }
        unset($group);
    }

    private function initializeGroupVars()
    {
        foreach ($this->entityGroupVars as $group) {
            $this->{$group['hasId']} = false;
            $this->{$group['hasFields']} = false;
        }

        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            foreach ($this->entityGroupVars as $group) {
                if ($apiField == $group['keyId'])
                    $this->{$group['hasId']} = true;
                if ($fieldInfo['entity_name'] == $group['entity_name'])
                    $this->{$group['hasFields']} = true;
            }
        }
    }

    private function determineSelectFields()
    {
        foreach ($this->entityGroupVars as $group) {
            if ($group['entity_name'] == $this->entityGroupVars[0]['entity_name']) {
                if (!$this->{$group['hasId']})
                    $this->selectFieldSpecs[$group['keyId']] = $this->fieldSpecs[$group['keyId']];
            } else {
                if ($this->{$group['hasFields']} and !$this->{$group['hasId']} and array_key_exists($group['keyId'], $this->fieldSpecs))
                    $this->selectFieldSpecs[$group['keyId']] = $this->fieldSpecs[$group['keyId']];
            }
        }
    }

    private function buildFrom(array $whereFieldsUsed, array $selectFieldSpecs, array $sortFields)
    {
        // Smerge all the fields into one array
        $allFieldsUsed = array_merge($whereFieldsUsed, array_keys($selectFieldSpecs), $sortFields);
        $allFieldsUsed = array_unique($allFieldsUsed);
        $fromString = '';
        $joins = array();

        // We need to go through the entities in order so the joins are done in the same order as they appear
        // in the entity specs.
        foreach ($this->entityGroupVars as $group) {
            foreach ($allFieldsUsed as $apiField) {
                if ($group['entity_name'] == $this->fieldSpecs[$apiField]['entity_name']) {
                    if (!in_array($group['join'], $joins)) {
                        $joins[] = $group['join'];
                    }
                    if (array_key_exists("additional_join", $this->fieldSpecs[$apiField])) {
                        if (!in_array($this->fieldSpecs[$apiField]['additional_join'], $joins)) {
                            $joins[] = $this->fieldSpecs[$apiField]['additional_join'];
                        }
                    }
                }
            }
        }
        $joins = array_unique($joins);
        foreach ($joins as $join) {
            $fromString .= ' ' . $join . ' ';
        }

        return $fromString;
    }

    private function buildSortString($sortParam)
    {
        $orderString = '';
        if ($sortParam != null) {
            foreach ($sortParam as $sortField) {
                foreach ($sortField as $apiField => $direction) {
                    try {
                        $fieldSpec = $this->fieldSpecs[$apiField];
                    } catch (ErrorException $e) {

                        throw new \Exceptions\QueryException("QR3", array($apiField));
                    }
                    if (strtolower($fieldSpec['sort']) == 'y') {
                        if (($direction != 'asc') and ($direction != 'desc')) {
                            throw new \Exceptions\QueryException("QR4", array($direction));
                        } else {
                            if ($orderString != '')
                                $orderString .= ', ';
                            $orderString .= getDBField($this->fieldSpecs, $apiField) . ' ' . $direction;
                            $this->sortFieldsUsed[] = $apiField;
                        }
                    } elseif (strtolower($fieldSpec['sort']) == 'suppl') {
                        if (($direction != 'asc') and ($direction != 'desc')) {
                            throw new \Exceptions\QueryException("QR4", array($direction));
                        } else {
                            if ($orderString != '')
                                $orderString .= ', ';
                            $orderString .= getDBField($this->fieldSpecs, $apiField) . ' ' . $direction;
                            $this->sortFieldsUsed[] = $apiField;
                            if (!$this->sort_by_subentity_counts || ($this->sort_by_subentity_counts && getDBField($this->fieldSpecs, $apiField) !== getDBField($this->fieldSpecs, $this->sort_by_subentity_counts))) {

                                $secEntityField = $fieldSpec['entity_name'];
                                $secEntityField .= "s";

                                if (array_key_exists($secEntityField, $this->sortFieldsUsedSec)) {
                                    array_push($this->sortFieldsUsedSec[$secEntityField], getDBField($this->fieldSpecs, $apiField) . ' ' . $direction);
                                } else {
                                    $this->sortFieldsUsedSec[$secEntityField] = array(getDBField($this->fieldSpecs, $apiField) . ' ' . $direction);
                                }
                            }
                        }
                    } else {
                        throw new \Exceptions\QueryException("QR3", array($apiField));
                    }
                }
            }
        }

        if ($orderString != '')
            $orderString .= ', ';
        $orderString .= getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']);
        return $orderString;
    }

    private
    function connectToDB()
    {
        global $config;
        if ($this->db === null) {
            $dbSettings = $config->getDbSettings();
            try {
                $this->db = new PDO("mysql:host=$dbSettings[host];dbname=$dbSettings[database];charset=utf8", $dbSettings['user'], $dbSettings['password']);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                $this->errorHandler->getLogger()->debug("Failed to connect to database: $dbSettings[database].");
                $this->rollbackTransaction();
                throw new \Exceptions\QueryException("QDC1", array());
            }

        }
        if ($this->dynamoClient === null) {
            $dynamoSettings = $config->getDynamoSettings();
            $sdk = new Aws\Sdk($dynamoSettings);
            try {
                $this->dynamoClient = $sdk->createDynamoDb();
            } catch (Aws\DynamoDB\Exception\DynamoDbException $e) {
                $this->errorHandler->getLogger()->debug("Failed to connect to dynamodb");
                throw new \Exceptions\QueryException("QDC1", array());
            }

        }
    }

    private
    function rollbackTransaction()
    {
        try {
            $this->db->rollback();
        } catch (PDOException $e) {
            $this->errorHandler->getLogger()->debug("Failure in rollback transaction" . $e->getMessage());
        }
    }

    private
    function runQuery($select, $from, $where, $order)
    {

        $this->connectToDB();

        if (strlen($where) > 0) $where = "WHERE $where ";
        if (strlen($order) > 0) $order = "ORDER BY $order";
        $sqlQuery = "SELECT $select FROM $from $where $order";
        $this->errorHandler->getLogger()->debug($sqlQuery);

        try {
            $st = $this->db->query("$sqlQuery", PDO::FETCH_ASSOC);
            $results = $st->fetchAll();
            $st->closeCursor();
        } catch (PDOException $e) {
            $this->errorHandler->getLogger()->debug($e->getMessage());
            $this->rollbackTransaction();
            return false;
        }
        //file_put_contents('php://stderr', print_r(count($results), TRUE));
        //file_put_contents('php://stderr', print_r("\n", TRUE));
        return $results;
    }

    private function startTransaction()
    {

        try {

            $this->db->exec("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
            $this->db->beginTransaction();
        } catch (PDOException $e) {
            $this->errorHandler->getLogger()->debug("Failure in starting transaction" . $e->getMessage());
            $this->rollbackTransaction();
            throw new \Exceptions\QueryException("QDIS1", array());
        }
    }

    private function runInsertSelect($insert, $select, $from, $where, $order, $dbSettings)
    {
        global $config;
        $this->connectToDB();

        if (strlen($where) > 0) $where = "WHERE $where ";
        if (strlen($order) > 0) $order = "ORDER BY $order";
        $tmp_dir = $config->getTempPath();
        $insertHash = substr(md5(uniqid(mt_rand(), true)), 0, 10);

        $selectSt = "SELECT $select FROM $from $where $order";
        $selectSt = preg_replace('/"/', '\"', $selectSt);
        $cmd = 'mysql -B -h' . escapeshellarg($dbSettings['host']) . ' -u' . escapeshellarg($dbSettings['user']) . ' -p' . escapeshellarg($dbSettings['password']) . ' ' . escapeshellarg($dbSettings['database']) . ' -e "' . $selectSt . '" > ' . escapeshellarg($tmp_dir . $insertHash . '.txt');
        $export_command_status = -1;
        $export_output = array();
        exec($cmd, $export_output, $export_command_status);
        if ($export_command_status != 0) {
            $this->errorHandler->getLogger()->debug("Failure in exporting to text file." . implode("\n", $export_output));
            $this->errorHandler->getLogger()->debug("Failure in LOAD DATA INFILE" . $cmd);
            throw new \Exceptions\QueryException("QDIS1", array());
        }
        $cmd2 = 'mysql --local-infile=1 -h' . escapeshellarg($dbSettings['host']) . ' -u' . escapeshellarg($dbSettings['user']) . ' -p' . escapeshellarg($dbSettings['password']) . ' ' . escapeshellarg($dbSettings['supportDatabase']) . ' -e "LOAD DATA LOCAL INFILE ' . "'" . $tmp_dir . $insertHash . ".txt'" . ' INTO TABLE QueryResults IGNORE 1 LINES; COMMIT;"';

        if (filesize($tmp_dir . $insertHash . ".txt") !== 0) {
            $outfile = fopen($tmp_dir . $insertHash . ".txt", 'r');
            $outstr = fread($outfile, filesize($tmp_dir . $insertHash . ".txt"));
            $outstr = preg_replace('/\s+\n/', "\n", $outstr);
            $outstr = preg_replace('/\n$/', '', $outstr);

            fclose($outfile);
            unlink($tmp_dir . $insertHash . '.txt');
            $outfile = fopen($tmp_dir . $insertHash . ".txt", 'w');
            fwrite($outfile, $outstr);
            fclose($outfile);
        }

        $import_command_status = -1;
        $import_output = array();
        exec($cmd2, $import_output, $import_command_status);
        if ($import_command_status != 0) {

            $this->errorHandler->getLogger()->debug("Failure in LOAD DATA INFILE" . implode("\n", $import_output));
            $this->errorHandler->getLogger()->debug("Failure in LOAD DATA INFILE" . $cmd2);
            throw new \Exceptions\QueryException("QDIS3", array());


        }

        unlink($tmp_dir . $insertHash . '.txt');
        //$st = $this->db->prepare($sqlQuery);
        //$results = $st->execute();
        //$st->closeCursor();


        return $import_command_status;
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
            } catch (PDOException $e) {
                if ($counto == $maxTriesy) {
                    $this->errorHandler->getLogger()->debug("Error during cache row creation");
                    $this->rollbackTransaction();
                    throw new \Exceptions\QueryException("QDI2", array());
                }
                usleep(1000000);
                continue;
            }
            break;
        } while ($counto < $maxTriesy);

        return $results;
    }

    private function commitTransaction()
    {
        $this->db->commit();
    }

    private function buildSelectStringForEntity($entitySpec)
    {
        $selectString = '';
        $additional_joins = array();
        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($fieldInfo['entity_name'] == $entitySpec['entity_name']) {
                if ($selectString != '')
                    $selectString .= ', ';
                $selectString .= getDBField($this->fieldSpecs, $apiField) . " as $apiField";
                if (array_key_exists("additional_join", $fieldInfo)) {
                    $additional_joins[] = $fieldInfo["additional_join"];
                }

            }
        }
        return array("select" => $selectString, "additional_joins" => implode(" ", array_unique($additional_joins)));
    }

    private function buildSelectStringForEntityReturnApiField($entitySpec)
    {
        $selectString = Array();
        $additional_joins = Array();
        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($fieldInfo['entity_name'] == $entitySpec['entity_name']) {
                $selectString[] = $apiField;
                if (array_key_exists("additional_join", $fieldInfo)) {
                    $additional_joins[] = $fieldInfo["additional_join"];
                }
            }
        }
        return array("select" => $selectString, "additional_joins" => $additional_joins);
    }

    private function buildSelectString()
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