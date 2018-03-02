<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 2/20/18
 * Time: 5:37 PM
 */

class PVSolrQuery
{
    private $solr_connections = array();
    private $db = null;
    private $errorHandler = null;
    private $entitySpecs = null;
    private $fieldSpecs = null;

    public function __construct(array $entitySpecs, array $fieldSpecs)
    {
        global $config;
        $this->entitySpecs = $entitySpecs;
        $this->fieldSpecs = $fieldSpecs;
        foreach ($entitySpecs as $entitySpec) {
            if (!array_key_exists($entitySpec["entity_name"], $this->solr_connections)) {

                $currentDBSetting = $config->getSOLRSettings();
                $currentDBSetting["path"] = "solr/" . $entitySpec['solr_collection'];
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

    public function countRowsForQuery($entity, $where)
    {
        $connectionToUse = $this->solr_connections[$entity];
        $query = new SolrQuery();
        if (array_key_exists("fq", $where)) {
            foreach ($where['fq'] as $filterQuery) {
                $query->addFilterQuery($filterQuery);
                //$query->addF
            }
        }

        if (array_key_exists("q", $where)) {
            $query->setQuery($where["q"]);
        } else {
            $query->setQuery("*:*");
        }
        $q = $connectionToUse->query($query);
        $response = $q->getResponse();
        return $response["response"]["numFound"];
    }

    public function loadQuery($whereClause, $queryDefId, $db, $table_usage)
    {
        $base = 1;
        if ($table_usage["base"][0] == 1) {
            $base = 2;
        }
        if (!(array_key_exists("AND", $whereClause)) && (!array_key_exists("OR", $whereClause))) {
            $this->loadEntityQuery($whereClause["e"], $whereClause["q"], $queryDefId, $db, $table_usage, $base);

        } else {
            foreach (array_keys($whereClause) as $whereJoin) {
                if ($table_usage["base"][0] == 1) {
                    $base = 2;
                }
                foreach ($whereClause[$whereJoin] as $clause) {
                    if (array_key_exists("e", $clause)) {
                        $table_usage = $this->loadEntityQuery($clause["e"], $clause["q"], $queryDefId, $db, $table_usage, $base);

                    } else {
                        $table_usage = $this->loadQuery($clause, $queryDefId, $db, $table_usage);
                        if ((array_sum($table_usage['base']) > 0) && (array_sum($table_usage["supp"]) > 0) || ((array_sum($table_usage['base']) > 1))) {
                            $table_usage = $db->updateBase($whereJoin, $table_usage,$queryDefId);
                        }
                    }
                    if ((array_sum($table_usage['base']) > 0) && (array_sum($table_usage["supp"]) > 0)) {
                        $table_usage = $db->updateBase($whereJoin, $table_usage,$queryDefId);
                    }
                    $base = 1;

                }

            }

        }
        return $table_usage;
    }

    public function loadEntityQuery($entity_name, $query_string, $queryDefId, $db, $table_usage, $base)
    {

        if ($table_usage["base"][0] == 0) {
            $tableName = "QueryResultsBase";
            $baseKey = "base";
            $baseIndex = 0;
            //$table_usage["base"][0] = 1;
        } else if ($base == 2 && $table_usage["base"][1] == 0) {
            $tableName = "QueryResultsBaseLevel2";
            $baseKey = "base";
            $baseIndex = 1;
            //$table_usage["base"][1] = 1;
        } else if ($table_usage["supp"][0] == 0) {
            $tableName = "QueryResultsSupp";
            $baseKey = "supp";
            $baseIndex = 0;
            //$table_usage["supp"][0] = 1;
        }

        $connectionToUse = $this->solr_connections[$entity_name];
        $query = new SolrQuery();
        $query->setQuery($query_string);
        $db->connectToDb();
        $db->startTransaction();

        $rows_fetched = 0;
        try {
            do {
                $query->setRows(10000);
                $query->setStart($rows_fetched);
                $keyField = $this->entitySpecs[0]["solr_key_id"];
                $query->addField($keyField);
                $q = $connectionToUse->query($query);
                $response = $q->getResponse();
                if ($response["response"]["numFound"] < 1) {
                    break;
                } else {
                    $table_usage[$baseKey][$baseIndex] = 1;
                }
                $db->loadEntityID($response["response"]["docs"], $entity_name, $queryDefId, $tableName);
                $rows_fetched += 10000;
                $rows_left = $response["response"]["numFound"] - $rows_fetched;
            } while ($rows_left > 0);
            $db->commitTransaction();
        } catch (PDOException $e) {
            $db->rollbackTransaction();
        }
        return $table_usage;
    }

    public
    function fetchQuery($fieldList, $whereClause, $queryDefId, $db, $options, $sort)
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

        $entityValuesToFetch = $db->retrieveEntityIdForSolr($queryDefId);
        if (count($entityValuesToFetch) < 1) {
            return array("db_results" => array($this->entitySpecs[0]["entity_name"] => array()), "count_results" => array("total_" . $this->entitySpecs[0]["entity_name"] . "_count" => 0));
        }
        $entityValueString = "( " . (implode(" ", $entityValuesToFetch)) . " ) ";
        $entitiesToFetch = array_keys($fieldList);
        $returned_values = array();
        $main_group = $this->entitySpecs[0]["group_name"];
        $return_array = array();
        foreach ($entitiesToFetch as $entity) {
            $solr_response = $this->fetchEntityQuery($entity, $this->entitySpecs[0]["solr_key_id"] . ":" . $entityValueString, $start, $rows, $fieldList[$entity]);
            $current_array = array();
            foreach ($solr_response["docs"] as $solrDoc) {
                $keyName = $this->entitySpecs[0]["solr_key_id"];
                if (!array_key_exists($solrDoc->$keyName, $current_array)) {
                    $current_array[$solrDoc->$keyName] = array();
                }
                $current_array[$solrDoc->$keyName][] = $solrDoc;
            }
            if ($subEntityCounts || $entity == $this->entitySpecs[0]["entity_name"]) {
                $return_array["total_" . $entity . "_count"] = $solr_response["numFound"];
            }

            $returned_values[$entity] = $current_array;
        }
        if (!array_key_exists($this->entitySpecs[0]["entity_name"], $returned_values)) {
            $solr_response = $this->fetchEntityQuery($this->entitySpecs[0]["entity_name"], $this->entitySpecs[0]["solr_key_id"] . ":" . $entityValueString, $start, $rows, array($this->fieldSpecs[$this->entitySpecs[0]["solr_key_id"]]));
            $current_array = array();
            foreach ($solr_response["docs"] as $solrDoc) {
                $keyName = $this->entitySpecs[0]["solr_key_id"];
                if (!array_key_exists($solrDoc->$keyName, $current_array)) {
                    $current_array[$solrDoc->$keyName] = array();
                }
                $current_array[$solrDoc->$keyName][] = $solrDoc;
            }
            if ($subEntityCounts || $this->entitySpecs[0]["entity_name"] == $this->entitySpecs[0]["entity_name"]) {
                $return_array["total_" . $this->entitySpecs[0]["entity_name"] . "_count"] = $solr_response["numFound"];
            }

            $returned_values[$this->entitySpecs[0]["entity_name"]] = $current_array;
        }
        return array("db_results" => $returned_values, "count_results" => $return_array);
    }

    public
    function fetchEntityQuery($entity_name, $queryString, $start, $rows, $fieldList)
    {

        $connectionToUse = $this->solr_connections[$entity_name];
        $query = new SolrQuery();
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
}