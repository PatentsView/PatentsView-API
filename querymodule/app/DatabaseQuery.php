<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


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
    private $errorHandler = null;

    private $matchedSubentitiesOnly = false;
    private $include_subentity_total_counts = false;
    private $sort_by_subentity_counts = false;

    private $supportDatabase = "";

    public function __construct($entitySpecs, $fieldSpecs)
    {
        global $config;
        $dbSettings = $config->getDbSettings();
        $this->supportDatabase = $dbSettings['supportDatabase'];

        $memUsed = memory_get_usage();
        $this->errorHandler = ErrorHandler::getHandler();
        $this->entitySpecs = $entitySpecs;
        $this->fieldSpecs = $fieldSpecs;
    }

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
                if (($options['per_page'] > $config->getMaxPageSize()) or ($options['per_page'] < 1))
                    $this->errorHandler->sendError(400, "Per_page must be a positive number not to exceed " . $config->getMaxPageSize() . ".", $options);
                else
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
                $this->errorHandler->sendError(500, "Sorting field is not in the output field list.", "Sorting field " . $options['sort_by_subentity_counts'] . " is not in the output field list.");
                throw new $e;
            } else {
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


        // Get the QueryDefId for this where clause
        $stringToHash = "key->" . $this->entitySpecs[0]['keyId'] . "::query->$whereClause.$whereGroup::sort->$sortString";
        $whereHash = crc32($stringToHash);   // Using crc32 rather than md5 since we only have 32-bits to work with.
        $queryDefId = sprintf('%u', $whereHash);


        $county = 0;
        $maxTries = 3;
        do {
            try {
                // If the query results for this where clause don't already exist, then we need to run the
                // query and cache the primary entity IDs.

                $results = $this->runQuery('QueryDefID, QueryString', $this->supportDatabase . '.QueryDef', "QueryDefID=$queryDefId", null);
                //TODO Need to handle a hash collision

                if (count($results) == 0) {
                    // Add an entry for the query

                    $this->startTransaction();
                    $insertStatement = $this->supportDatabase . '.QueryDef (QueryDefId, QueryString) VALUES (:queryDefId, :whereClause)';
                    $this->runInsert($insertStatement, array(':queryDefId' => $queryDefId, ':whereClause' => $stringToHash));

                    // Get all the primary entity IDs and insert into the cached results table
                    #Todo: Optimization issue: when there is no where clause perhaps we should disallow it, otherwise it can be really slow depending on the primary entity. For patents on the full DB it takes over 7m - stopped waiting.
                    $insertStatement = $this->supportDatabase . '.QueryResults (QueryDefId, Sequence, EntityId)';
                    $selectPrimaryEntityIdsString =
                        "$queryDefId, @row_number:=@row_number+1 as sequence, XX.XXid as " . $this->entityGroupVars[0]['keyId'];
                    if (strlen($whereClause) > 0) $whereInsert = "WHERE $whereClause $whereGroup "; else $whereInsert = "";
                    if (strlen($sortString) > 0) $sortInsert = "ORDER BY $sortString "; else $sortInsert = '';
                    $fromInsert = $this->buildFrom($whereFieldsUsed, array($entitySpecs[0]['keyId'] => $this->fieldSpecs[$entitySpecs[0]['keyId']]), $this->sortFieldsUsed);
                    $this->fromSubEntity = $fromInsert;
                    $this->runInsertSelect($insertStatement,
                        $selectPrimaryEntityIdsString,
                        '(SELECT distinct ' . getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']) . ' as XXid FROM ' .
                        $fromInsert . ' ' . $whereInsert . $sortInsert . ' limit ' . $config->getQueryResultLimit() . ') XX, (select @row_number:=0) temprownum',
                        null,
                        null, $dbSettings);
                    $this->commitTransaction();
                    break;
                }
            } catch (Exception $e) {

                $this->rollbackTransaction();
                $county++;
                if ($county == $maxTries) {
                    $this->errorHandler->sendError(500, "Insert select execution failed.", $e);
                    throw new $e;
                    break;
                }
                usleep(500000);
                continue;
            }
            break;

        } while ($county < $maxTries);

        // First find out how many there are in the complete set.
        $selectStringForEntity = 'count(QueryDefId) as total_found';
        $fromEntity = $this->supportDatabase . '.QueryResults qr';
        $whereEntity = "qr.QueryDefId=$queryDefId";
        $countResults = $this->runQuery($selectStringForEntity, $fromEntity, $whereEntity, null);

        $this->entityTotalCounts[$entitySpecs[0]['entity_name']] = intval($countResults[0]['total_found']);


        // Get the primary entities
        $results = array();
        $selectStringForEntity = $this->buildSelectStringForEntity($this->entitySpecs[0]);
        $fromEntity = $this->entitySpecs[0]['join'] .
            ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';
        $whereEntity = "qr.QueryDefId=$queryDefId";
        if ($perPage < $this->entityTotalCounts[$entitySpecs[0]['entity_name']])
            $whereEntity .= ' and ((qr.Sequence>=' . ((($page - 1) * $perPage) + 1) . ') and (qr.Sequence<=' . $page * $perPage . '))';
        $sortEntity = 'qr.sequence';
        $entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, $sortEntity);
        $results[$this->entitySpecs[0]['group_name']] = $entityResults;
        unset($entityResults);

        $allFieldsUsed = array_merge($whereFieldsUsed, array_keys(array($entitySpecs[0]['keyId'] => $this->fieldSpecs[$entitySpecs[0]['keyId']])), $this->sortFieldsUsed);
        foreach (array_slice($this->entitySpecs, 1) as $entitySpec) {
            $tempSelect = $this->buildSelectStringForEntityReturnApiField($entitySpec);
            $allFieldsUsed = array_merge($allFieldsUsed, $tempSelect);
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
            $tempSelect = $this->buildSelectStringForEntity($entitySpec);
            if ($tempSelect != '') { // If there aren't any fields to get back, then skip the group.
                $selectStringForEntity = getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . ' as ' . $this->entitySpecs[0]['keyId'];
                $selectStringForEntity .= ", $tempSelect";
                $fromEntity = $this->entitySpecs[0]['join'] .
                    ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';
                $fromEntity .= ' ' . $entitySpec['join'];
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
                    try {
                        // Count of all subentities for all primary entities.
                        $selectStringForEntity = 'count(distinct ' . getDBField($this->fieldSpecs, $entitySpec['distinctCountId']) . ') as subentity_count';
                        $fromEntity = $fromSubEntity;
                        if (!in_array($entitySpec['entity_name'], $groupsCheckTotalCount)) {
                            $fromEntity .= ' ' . $entitySpec['join'];
                        }
                        $whereEntity = "qr.QueryDefId=$queryDefId";
                        $whereEntity .= ' and ' . $whereClause;
                        $countResults = $this->runQuery($selectStringForEntity, $fromEntity, $whereEntity, null);
                        $this->entityTotalCounts[$entitySpec['entity_name']] = intval($countResults[0]['subentity_count']);
                    } catch (Exception $e) {
                        $this->errorHandler->getLogger()->debug($e);
                    }
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
        foreach ($this->entityGroupVars as $group)
            foreach ($allFieldsUsed as $apiField)
                if ($group['entity_name'] == $this->fieldSpecs[$apiField]['entity_name'])
                    if (!in_array($group['join'], $joins))
                        $joins[] = $group['join'];

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
                        ErrorHandler::getHandler()->sendError(400, "Invalid field for sorting: $apiField");
                        throw $e;
                    }
                    if (strtolower($fieldSpec['sort']) == 'y') {
                        if (($direction != 'asc') and ($direction != 'desc')) {
                            ErrorHandler::getHandler()->sendError(400, "Not a valid direction for sorting: $direction");
                            throw new ErrorException("Not a valid direction for sorting: $direction");
                        } else {
                            if ($orderString != '')
                                $orderString .= ', ';
                            $orderString .= getDBField($this->fieldSpecs, $apiField) . ' ' . $direction;
                            $this->sortFieldsUsed[] = $apiField;
                        }
                    } elseif (strtolower($fieldSpec['sort']) == 'suppl') {
                        if (($direction != 'asc') and ($direction != 'desc')) {
                            ErrorHandler::getHandler()->sendError(400, "Not a valid direction for sorting: $direction");
                            throw new ErrorException("Not a valid direction for sorting: $direction");
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
                        $msg = "Not a valid field for sorting: $apiField";
                        ErrorHandler::getHandler()->sendError(400, $msg);
                        throw new ErrorException($msg);
                    }
                }
            }
        }

        if ($orderString != '')
            $orderString .= ', ';
        $orderString .= getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']);
        return $orderString;
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

    public function startTransaction()
    {
        $this->db->beginTransaction();
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
        shell_exec($cmd);
        $cmd2 = 'mysql --local-infile=1 -h' . escapeshellarg($dbSettings['host']) . ' -u' . escapeshellarg($dbSettings['user']) . ' -p' . escapeshellarg($dbSettings['password']) . ' ' . escapeshellarg($dbSettings['supportDatabase']) . ' -e "LOAD DATA LOCAL INFILE ' . "'" . $tmp_dir . $insertHash . ".txt'" . ' INTO TABLE QueryResults IGNORE 1 LINES; COMMIT;"';

        try {
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
            $results = shell_exec($cmd2);
            unlink($tmp_dir . $insertHash . '.txt');
            //$st = $this->db->prepare($sqlQuery);
            //$results = $st->execute();
            //$st->closeCursor();
        } catch (Exception $e) {
            $this->errorHandler->sendError(500, "Insert select execution failed.", $e);
            throw new $e;
        }

        return $results;
    }

    public function commitTransaction()
    {
        $this->db->commit();
    }

    public function rollbackTransaction()
    {
        $this->db->rollback();
    }

    private function buildSelectStringForEntity($entitySpec)
    {
        $selectString = '';
        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($fieldInfo['entity_name'] == $entitySpec['entity_name']) {
                if ($selectString != '')
                    $selectString .= ', ';
                $selectString .= getDBField($this->fieldSpecs, $apiField) . " as $apiField";
            }
        }
        return $selectString;
    }

    private function buildSelectStringForEntityReturnApiField($entitySpec)
    {
        $selectString = Array();
        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($fieldInfo['entity_name'] == $entitySpec['entity_name']) {
                $selectString[] = $apiField;
            }
        }
        return $selectString;
    }

    public function loadEntityID($data, array $fieldPresence, $queryDefId, $tableName, $sequenceStart = 0)
    {
        $datafields = array('QueryDefId', 'Sequence', 'EntityId', 'SecondaryEntityId');
        $keyField = $this->entitySpecs[0]["solr_key_id"];
        $insertData = array();
        $insert_values = array();
        $secondary = False;
        if (array_key_exists("secondaryKeyField", $fieldPresence)) {
            $secondary = True;
        }
        for ($docNumber = 0; $docNumber < count($data); $docNumber++) {
            $keyValue = $data[$docNumber]["value"];

            if ($secondary) {
                foreach ($data[$docNumber]["pivot"] as $innerDoc) {
                    $insert_values[] = $queryDefId;
                    $insert_values[] = $sequenceStart;
                    $insert_values[] = $keyValue;
                    $insert_values[] = $innerDoc["value"];
                }
            } else {
                $insert_values[] = $queryDefId;
                $insert_values[] = $sequenceStart;
                $insert_values[] = $keyValue;
                $insert_values[] = $keyValue;

            }
            $question_marks[] = '(?,?,?,? )';
            $sequenceStart += 1;
            //array_merge($insert_values, array_values($data_array));
        }

        $this->connectToDB();


//        foreach ($insertData as $d) {
//            $question_marks[] = '(' . placeholders('?', sizeof($d)) . ')';
//            $insert_values = array_merge($insert_values, array_values($d));
//        }

        $sql = "INSERT INTO " . $this->supportDatabase . "." . $tableName . " (" . implode(",", $datafields) . ") VALUES " .
            implode(',', $question_marks);

        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute($insert_values);
        } catch
        (PDOException $e) {
            echo $e->getMessage();
            throw $e;
        }
        //$this->commitTransaction();
    }

    public function retrieveEntityIdForSolr($queryDefId, $start, $rows, $getEverything = false)
    {
        $this->connectToDB();
        $where = "QueryDefID=$queryDefId  order by Sequence asc";
        if (!$getEverything) {
            $where .= " LIMIT $rows OFFSET $start";
        }
        $results = $this->runQuery('DISTINCT EntityId', $this->supportDatabase . '.QueryResultsBase', $where, null);
//        $count_results = $this->runQuery('COUNT(DISTINCT EntityId)', $this->supportDatabase . '.QueryResultsBase', "QueryDefID=$queryDefId", null);

        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result["EntityId"];
        }
        return $ids;
    }


    public function updateBase($whereJoin, $table_usage, $queryDefId, $useSecondary = false)
    {
        $filterField = "EntityId";
        if ($useSecondary) {
            $filterField = "SecondaryEntityId";
        }
        $source_table = "QueryResultsSupp";
        $dest_table = "QueryResultsBase";
        if ($table_usage["supp"][0] == 1) {
            if ($table_usage["base"][1] == 1) {
                $dest_table = "QueryResultsBaseLevel2";
            }
        } else {
            $source_table = "QueryResultsBaseLevel2";
        }

        if ($whereJoin == "AND") {
            $updateQuery = "DELETE FROM  " . $this->supportDatabase . "." . $dest_table . " WHERE " . $filterField . " NOT IN (SELECT " . $filterField . " FROM " . $this->supportDatabase . "." . $source_table . " WHERE QueryDefId = ?) AND QueryDefId = ?;";
        } else {
            $updateQuery = "INSERT INTO  " . $this->supportDatabase . "." . $dest_table . " SELECT * FROM " . $this->supportDatabase . "." . $source_table . "  WHERE " . $filterField . " NOT IN (SELECT " . $filterField . " FROM " . $this->supportDatabase . "." . $dest_table . " WHERE QueryDefId = ? ) AND QueryDefId = ?;";
        }
        $this->startTransaction();
        $stmt = $this->db->prepare($updateQuery);
        try {
            $stmt->execute(array($queryDefId, $queryDefId));
            $this->commitTransaction();
            $stmt = $this->db->prepare("DELETE FROM " . $this->supportDatabase . "." . $source_table . " where QueryDefId=$queryDefId");
            if ($source_table == "QueryResultsSupp") {
                $table_usage["supp"][0] = 0;
            } else {
                $table_usage["base"][1] = 0;
            }
            $stmt->execute();

        } catch (PDOException $e) {
            echo $e->getMessage();
            $this->rollbackTransaction();
            throw $e;
        }
        return $table_usage;
    }

    public function checkQueryDef($queryDefId)
    {
        $this->connectToDB();
        $results = $this->runQuery('QueryDefID, QueryString', $this->supportDatabase . '.QueryDef', "QueryDefID=$queryDefId", null);
        return count($results);
    }

    public function addQueryDef($queryDefId, $queryString)
    {
        $insertStatement = $this->supportDatabase . '.QueryDef (QueryDefId, QueryString) VALUES (:queryDefId, :whereClause)';
        $this->runInsert($insertStatement, array(':queryDefId' => $queryDefId, ':whereClause' => $queryString));
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