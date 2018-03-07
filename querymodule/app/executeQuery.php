<?php
require_once dirname(__FILE__) . '/QueryParser.php';
require_once dirname(__FILE__) . '/DatabaseQuery.php';
require_once dirname(__FILE__) . '/SolrQuery.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';


function executeQuery(array $entitySpecs, array $fieldSpecs, array $queryParam = null, array $fieldsParam = null, array $sortParam = null, array $optionsParam = null)
{
    $qp = new QueryParser();


    $main_entity_name = $entitySpecs[0]["entity_name"];
    $queryString = json_encode($queryParam);
    //$queryString=implode(",",$queryParam);

    $uniqueQueryString = "key->" . $entitySpecs[0]['keyId'] . "::query->$queryString";
    $whereHash = crc32($uniqueQueryString);   // Using crc32 rather than md5 since we only have 32-bits to work with.
    $queryDefId = sprintf('%u', $whereHash);
    // Build the where clause to determine the primary entities for the results
    $whereClause = $qp->parse($fieldSpecs, $queryParam, 'all', $entitySpecs);


    // Changed so that if the caller did not explicitly list fields to be returned, we will use a pre-defined set
    // for the primary entity.
    if (!$fieldsParam) $fieldsParam = $entitySpecs[0]['default_fields'];

    // Get the FieldSpecs for the list of fields to be returned.
    $selectFieldSpecs = parseFieldList($entitySpecs, $fieldSpecs, $fieldsParam);
    $sortFieldSpecs = parseFieldList($entitySpecs, $fieldSpecs, $sortParam);

    $dbQuery = new DatabaseQuery($entitySpecs, $fieldSpecs);
    $queryResultsStatus = $dbQuery->checkQueryDef($queryDefId);
    $solrQuery = new PVSolrQuery($entitySpecs, $fieldSpecs);
    if ($queryResultsStatus < 1) {
        $table_usage = array("base" => array(0, 0), "supp" => array(0, 0));
        $solrQuery->loadQuery($whereClause, $queryDefId, $dbQuery, $table_usage, $sortFieldSpecs, array(0 => false));
        $dbQuery->addQueryDef($queryDefId, $queryString);
    }

    $dbResults = $solrQuery->fetchQuery($selectFieldSpecs, $whereClause, $queryDefId, $dbQuery, $optionsParam, $sortFieldSpecs);


    $results = convertDBResultsToNestedStructure($entitySpecs, $fieldSpecs, $dbResults, $selectFieldSpecs);

//    foreach ($dbQuery->getTotalCounts() as $entityName => $count)
//        $results['total_' . $entityName . '_count'] = $count;
    return $results;
}