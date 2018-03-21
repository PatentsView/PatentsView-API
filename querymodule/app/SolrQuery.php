<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 2/20/18
 * Time: 5:37 PM
 */

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\StreamWrapper;

require_once dirname(__FILE__) . '/MaxEntitiesLoadedException.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';

class PVSolrQuery
{
    private $solr_connections = array();
    private $httpClient = null;
    private $errorHandler = null;
    private $entitySpecs = null;
    private $fieldSpecs = null;

    public function __construct(array $entitySpecs, array $fieldSpecs)
    {


        global $config;

        $this->errorHandler = ErrorHandler::getHandler();
        $this->entitySpecs = $entitySpecs;
        $this->fieldSpecs = $fieldSpecs;
        $currentDBSetting = $config->getSOLRSettings();
        $this->httpClient = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://' . $currentDBSetting["hostname"] . ":" . $currentDBSetting["port"] . "/solr/" . $entitySpecs[0]["solr_fetch_collection"] . "/"
        ]);
        $currentDBSetting["path"] = "solr/" . $entitySpecs[0]['solr_fetch_collection'];

        $currentDBSetting["wt"] = "phps";
        $currentDBSetting["timeout"] = 60;

        $this->solr_connections["main_entity_fetch"] = new SolrClient($currentDBSetting);

        foreach ($entitySpecs as $entitySpec) {
            if (!array_key_exists($entitySpec["entity_name"], $this->solr_connections)) {
                $currentDBSetting = $config->getSOLRSettings();

                $currentDBSetting["path"] = "solr/" . $entitySpec['solr_collection'];

                $currentDBSetting["wt"] = "phps";
                $currentDBSetting["timeout"] = 60;

                try {
//                file_put_contents('php://stderr', print_r($currentDBSetting, TRUE));
                    $this->solr_connections[$entitySpec["entity_name"]] = new SolrClient($currentDBSetting);
                } catch (SolrIllegalArgumentException $e) {

                    $this->errorHandler->sendError(500, "Failed to connect to database: $currentDBSetting[hostname] , $entitySpecs[solr_collection]", $e);
                    throw new $e;
                }
                //$this->solr_connections[$entitySpec["entity_name"]] = $currentDBSetting;
            }
        }

    }

    public function getSolrConnection($entity_name)
    {
        return $this->solr_connections[$entity_name];
    }

    public function loadQuery($whereClause, $queryDefId, $db, $table_usage, $sort, array $isSecondaryKeyUpdate, $level = 0)
    {
        global $config;
        $tempDir = $config->getTempPath();
        $currentResponseFile = $tempDir . $queryDefId . ".json";
        $currentDBSetting = $config->getSOLRSettings();
        $entityIDResponse = $this->httpClient->request('GET', 'stream', ['query' => ['expr' => $whereClause["query"]], 'stream' => true]);
        //$urlToRequest = 'http://' . $currentDBSetting["hostname"] . ":" . $currentDBSetting["port"] . "/solr/" . $this->entitySpecs[0]["solr_fetch_collection"] . "/stream?expr=" . urlencode($whereClause["query"]);
        $body = $entityIDResponse->getBody();
        $resource = StreamWrapper::getResource($body);


//
        // $body->
//        while (!$body->eof()) {
//            $current_str = $body->read(2097152);
        //file_put_contents($currentResponseFile, $body);
//        }
        //$stream = fopen($currentResponseFile, 'r');
        try {

            $parser = new \JsonStreamingParser\Parser($resource, $db);
            $parser->parse();

        } catch (MaxEntitiesLoadedException $e) {
            $db->commitTransaction();
        } catch (Exception $e) {
            $db->rollbackTransaction();
            throw $e;
        }
//        $all_entity_data = json_decode($body);
//        $location_ids = array();
//        $keyToCheck = $this->entitySpecs[0]["solr_key_id"];
//        $current_entity_set = array();
//        $sequence = 0;
//        $start_time = microtime(true);
//        $time_elapsed = $this->end_time - $this->start_time;
//        foreach ($all_entity_data["result-sets"]["docs"] as $entity_datum) {
//            if (!array_key_exists($entity_datum[$keyToCheck], $location_ids)) {
//                $location_ids[$entity_datum[$keyToCheck]] = 1;
//                array_push($current_entity_set, $queryDefId);
//                array_push($current_entity_set, $sequence);
//                array_push($current_entity_set, $entity_datum[$keyToCheck]);
//                $question_marks[] = '(' . placeholders('?', 3) . ')';
//            }
//            if (count($current_entity_set) > 999) {
//                $db->loadEntityId($current_entity_set, $question_marks);
//                $question_marks = array();
//                $current_entity_set = array();
//                $end_time = microtime(true);
//                $time_elapsed = $end_time - $start_time;
//            }
//            $sequence += 1;
//        }


    }
//    public function loadQuery($whereClause, $queryDefId, $db, $table_usage, $sort, array $isSecondaryKeyUpdate, $level = 0)
//    {
//        $base = 1;
//        if ($table_usage["base"][0] == 1) {
//            $base = 2;
//        }
//        if (!(array_key_exists("AND", $whereClause)) && (!array_key_exists("OR", $whereClause))) {
//            $current_sort = array();
//
//            if (array_key_exists($whereClause["e"], $sort)) {
//                $current_sort = $sort[$whereClause["e"]];
//            }
//            $this->loadEntityQuery(getEntitySpecs($this->entitySpecs, $whereClause["e"]), $whereClause["q"], $queryDefId, $db, $table_usage, $base, $current_sort, $whereClause["s"]);
//
//        } else {
//            foreach (array_keys($whereClause) as $whereJoin) {
//                if ($table_usage["base"][0] == 1) {
//                    $base = 2;
//                }
//                $isSecondaryKeyUpdate[$level] = false;
//                $i = 0;
//                $joins = array_keys($whereClause[$whereJoin]);
//                foreach ($whereClause[$whereJoin] as $clause) {
//                    if ((array_key_exists("s", $clause) && $clause["s"])) {
//                        $isSecondaryKeyUpdate[$level] = true;
//                    }
//                }
//
//                foreach ($whereClause[$whereJoin] as $clause) {
//
//                    if (array_key_exists("e", $clause)) {
//                        $secondarySoFar = array_sum(array_slice($isSecondaryKeyUpdate, 0, $level + 1));
//                        $current_sort = array();
//                        if (array_key_exists($clause["e"], $sort)) {
//                            $current_sort = $sort[$clause["e"]];
//                        }
//                        $table_usage = $this->loadEntityQuery(getEntitySpecs($this->entitySpecs, $clause["e"]), $clause["q"], $queryDefId, $db, $table_usage, $base, $current_sort, $secondarySoFar);
//
//                    } else {
//                        $table_usage = $this->loadQuery($clause, $queryDefId, $db, $table_usage, $sort, $isSecondaryKeyUpdate, $level + 1);
//                        if ((array_sum($table_usage['base']) > 0) && (array_sum($table_usage["supp"]) > 0) || ((array_sum($table_usage['base']) > 1))) {
//                            $table_usage = $db->updateBase($whereJoin, $table_usage, $queryDefId, $isSecondaryKeyUpdate[$level]);
//                            $isSecondaryKeyUpdate[$level] = false;
//                            for ($k = $i; $k < count($joins); $k++) {
//                                $lookAheadClause = $whereClause[$whereJoin][$joins[$k]];
//                                if ((array_key_exists("s", $lookAheadClause) && $lookAheadClause["s"])) {
//                                    $isSecondaryKeyUpdate[$level] = true;
//                                }
//                            }
//                        }
//                    }
//                    if ((array_sum($table_usage['base']) > 0) && (array_sum($table_usage["supp"]) > 0)) {
//                        $table_usage = $db->updateBase($whereJoin, $table_usage, $queryDefId, $isSecondaryKeyUpdate[$level]);
//
//                        $isSecondaryKeyUpdate[$level] = false;
//                        for ($k = $i; $k < count($joins); $k++) {
//                            $lookAheadClause = $whereClause[$whereJoin][$joins[$k]];
//                            if ((array_key_exists("s", $lookAheadClause) && $lookAheadClause["s"])) {
//                                $isSecondaryKeyUpdate[$level] = true;
//                            }
//                        }
//
//                    }
//                    $base = 1;
//                    $i += 1;
//                }
//
//            }
//
//        }
//        return $table_usage;
//    }
//
//    public function loadEntityQuery($entitySpec, $query_string, $queryDefId, $db, $table_usage, $base, $sort, $useSecondary = false)
//    {
//
//        if ($table_usage["base"][0] == 0) {
//            $tableName = "QueryResultsBase";
//            $baseKey = "base";
//            $baseIndex = 0;
//            //$table_usage["base"][0] = 1;
//        } else if ($base == 2 && $table_usage["base"][1] == 0) {
//            $tableName = "QueryResultsBaseLevel2";
//            $baseKey = "base";
//            $baseIndex = 1;
//            //$table_usage["base"][1] = 1;
//        } else if ($table_usage["supp"][0] == 0) {
//            $tableName = "QueryResultsSupp";
//            $baseKey = "supp";
//            $baseIndex = 0;
//            //$table_usage["supp"][0] = 1;
//        }
//
//        $connectionToUse = $this->solr_connections[$entitySpec["entity_name"]];
//        $query = new SolrQuery();
//        $query->setQuery($query_string);
//        $db->connectToDb();
//        $db->startTransaction();
//        $query->setGroup(true);
//        $keyField = $this->entitySpecs[0]["solr_key_id"];
//        $fieldPresence = array("keyField" => $keyField);
//        $query->addField($keyField);
//        $query->addGroupField($keyField);
//
//        if (array_key_exists("secondary_key_id", $entitySpec) & $useSecondary) {
//            $secondaryKeyField = $entitySpec["secondary_key_id"];
//            $query->addField($secondaryKeyField);
//            $query->addGroupField($secondaryKeyField);
//            $fieldPresence["secondaryKeyField"] = $secondaryKeyField;
//        }
//
//        $rows_fetched = 0;
//        $total_fetched = 0;
//        $keys = array();
//        try {
//            do {
//                $query->setRows(10000);
//                $query->setStart($total_fetched);
//
//                //http://ec2-52-23-55-147.compute-1.amazonaws.com:8983/solr/location_patent_join/select?indent=on&q=patents.patent_num_cited_by_us_patents%20:%203&wt=json&group=true&group.main=true&group.field=location_key_id&group.field=inventor_id&fl=location_key_id,inventor_id&rows=10000
//
//
//                try {
//                    //$query->setTimeAllowed(300000);
//                    $q = $connectionToUse->query($query);
//                } catch (SolrClientException $e) {
//                    //print_r($e);
//                    $endtime = microtime(true);
//                    $this->errorHandler->sendError(500, "Error in querying data");
//                    break;
//
//                }
//                $response = $q->getResponse();
//                $rows_fetched = count($response["grouped"][$keyField]["groups"]);
//                $table_usage[$baseKey][$baseIndex] = 1;
//                if ($rows_fetched < 1) {
//                    break;
//                }
//
//
////                foreach ($response["response"]["docs"] as $doc) {
////                    if (!array_key_exists($doc->location_key_id, $keys)) {
////                        $keys[$doc->location_key_id] = 0;
////                    }else{
////                        null;
////                    }
////                    $keys[$doc->location_key_id] += 1;
////                }
//                $db->loadEntityID($response["grouped"][$keyField]["groups"], $fieldPresence, $queryDefId, $tableName, $total_fetched);
//                $total_fetched += $rows_fetched;
//
//
//            } while (True);
//            $db->commitTransaction();
//        } catch (PDOException $e) {
//            $db->rollbackTransaction();
//        }
//
//        return $table_usage;
//    }


    public
    function fetchQuery($selectFieldSpecs, $whereClause, $queryDefId, $db, $options,$sort)
    {
        $rows = 25;
        $start = 0;
        $matchSubEntityOnly = false;
        $subEntityCounts = false;
        if ($options) {

            if (array_key_exists("per_page", $options)) {
                $rows = (int)$options["per_page"];
            }

            if (array_key_exists("page", $options)) {
                $start = ($rows) * ($options["page"] - 1);
            }

            if (array_key_exists("matched_subentities_only", $options)) {
                $matchSubEntityOnly = $options["matched_subentities_only"];
            }

            if (array_key_exists("include_subentity_total_counts ", $options)) {
                $subEntityCounts = $options["include_subentity_total_counts"];
            }
        }

        $entityValuesToFetch = $db->retrieveEntityIdForSolr($queryDefId, $start, $rows);
        if (count($entityValuesToFetch) < 1) {
            return array("db_results" => array($this->entitySpecs[0]["entity_name"] => array()), "count_results" => array("total_" . $this->entitySpecs[0]["entity_name"] . "_count" => 0));
        }
        $entitiesLeft = count($entityValuesToFetch);
        $queryCounts = array();
        $queryResults = array();
        $entitiesToFetch = array_keys($selectFieldSpecs);
        if (!in_array($this->entitySpecs[0]["entity_name"], $entitiesToFetch)) {
            array_push($entitiesToFetch, $this->entitySpecs[0]["entity_name"]);
        }

        do {
            $entityValueString = "( " . (implode(" ", array_slice($entityValuesToFetch, count($entityValuesToFetch) - $entitiesLeft, 1024))) . " ) ";
            $main_group = $this->entitySpecs[0]["group_name"];

            foreach ($entitiesToFetch as $entity) {
                $numFound = 0;
                $numFetched = 0;
                $solrStart = 0;
                $solrRows = 10000;
                $current_array = array_fill_keys($entityValuesToFetch, array());
                do {
                    $currentEntityFieldList = array_key_exists($entity, $selectFieldSpecs) ? $selectFieldSpecs [$entity] : array($this->fieldSpecs[$this->entitySpecs[0]["solr_fetch_id"]]);
                    $solr_response = $this->fetchEntityQuery($entity, $this->entitySpecs[0]["solr_fetch_id"] . ":" . $entityValueString, $solrStart, $solrRows, $currentEntityFieldList);
                    $numFound = $solr_response["numFound"];

                    foreach ($solr_response["docs"] as $solrDoc) {
                        $keyName = $this->entitySpecs[0]["solr_fetch_id"];
                        $current_array[$solrDoc->$keyName][] = $solrDoc;
                    }
                    if ($subEntityCounts || $entity == $this->entitySpecs[0]["entity_name"]) {
                        $total_count = $this->getEntityCounts($entity, $queryDefId, $db);
                        $queryCounts["total_" . $entity . "_count"] = $total_count;
                    }

                    $queryResults[$entity] = $current_array;
                    $numFetched += 10000;

                    $solrStart += 10000;
                } while ($numFetched < $numFound);
            }
            $entitiesLeft -= 1024;
        } while ($entitiesLeft >= 0);
        $dbResults = array("db_results" => $queryResults, "count_results" => $queryCounts);
        $results = convertDBResultsToNestedStructure($this->entitySpecs, $this->fieldSpecs, $dbResults, $selectFieldSpecs);
        return $results;
    }

    public
    function fetchEntityQuery($entity_name, $queryString, $start, $rows, $fieldList)
    {

        $connectionToUse = $this->solr_connections[$entity_name];
        if ($entity_name == $this->entitySpecs[0]["entity_name"]) {
            $connectionToUse = $this->solr_connections["main_entity_fetch"];
        }
        $query = new SolrQuery();
        //$query->setTimeAllowed(300000);
        $query->setQuery($queryString);
        $query->setStart($start);
        $query->setRows($rows);

        foreach (array_keys($fieldList) as $field) {
            $query->addField($fieldList[$field]["solr_column_name"]);
        }

        $q = $connectionToUse->query($query);
        $response = $q->getResponse();
        return $response["response"];

    }

    public function getEntityCounts($entity, $queryDefId, $db)
    {
        $rows = 1000;
        $offset = 0;
        $entity_count = 0;

        if ($entity == $this->entitySpecs[0]["entity_name"]) {

            $entityValuesToFetch = $db->retrieveEntityIdForSolr($queryDefId, $offset, $rows, true);
            return count($entityValuesToFetch);
        }

        do {

            $entityValuesToFetch = $db->retrieveEntityIdForSolr($queryDefId, $offset, $rows);
            if (count($entityValuesToFetch) < 1) {
                break;
            }
            $entityValueString = "( " . (implode(" ", $entityValuesToFetch)) . " ) ";
            $entity_count += $this->countRowsForQuery($entity, $this->entitySpecs[0]["solr_fetch_id"] . ":" . $entityValueString);
            $offset += $rows;
        } while (true);
        return $entity_count;
    }

    public function countRowsForQuery($entity, $where)
    {
        $connectionToUse = $this->solr_connections[$entity];
        $query = new SolrQuery();
        $query->setStart(0);
        $query->setRows(0);
        $query->setQuery($where);
        $q = $connectionToUse->query($query);
        $response = $q->getResponse();
        return $response["response"]["numFound"];
    }

}