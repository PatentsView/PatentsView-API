<?php
require_once dirname(__FILE__) . '/QueryParser.php';
require_once dirname(__FILE__) . '/DatabaseQuery.php';
require_once dirname(__FILE__) . '/SolrQuery.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';


function executeQuery(array $entitySpecs, array $fieldSpecs, array $queryParam = null, array $fieldsParam = null, array $sortParam = null, array $optionsParam = null)
{
    $qp = new QueryParser();
    $primaryEntity=$entitySpecs[0];

    $mainEntityName = $primaryEntity["entity_name"];
    $queryString = json_encode($queryParam);

    // Changed so that if the caller did not explicitly list fields to be returned, we will use a pre-defined set
    // for the primary entity.
    if (!$fieldsParam) $fieldsParam = $primaryEntity['default_fields'];
    if (!$sortParam) $sortParam =array($primaryEntity['default_fields'][0]);

    // Get the FieldSpecs for the list of fields to be returned.
    $selectFieldSpecs = parseFieldList($entitySpecs, $fieldSpecs, $fieldsParam);

    // Get field specs for sort fields
    $sortFieldSpecs = parseFieldList($entitySpecs, $fieldSpecs, $sortParam);

    // Generate a unique string based on query, to be used as cache key
    // Use only query & sort parameters since fields selected do not impact which documents are selected
    $uniqueQueryString = "key->" . $primaryEntity['keyId'] . "::query->$queryString::sort->$sortParam";
    $whereHash = crc32($uniqueQueryString);   // Using crc32 rather than md5 since we only have 32-bits to work with.
    $queryDefId = sprintf('%u', $whereHash);

    // Constructs SOLR streaming expression for the request query
    $streamingXpression = $qp->parse($fieldSpecs, $queryParam, $entitySpecs);

    // Initialize MySQL connection parameters and variables
    // Note: This object is capable of receiving streaming JSON and load it into MySQL table
    $dbQuery = new DatabaseQuery($entitySpecs, $fieldSpecs, $queryDefId);

    // Check if the query results are cached
    $queryResultCountFromCache = $dbQuery->checkQueryDef($queryDefId);

    if ($queryResultCountFromCache < 1) {
        // Cache Miss
        // Initialize SOLR Connections and variables
        $solrQuery = new PVSolrQuery($entitySpecs, $fieldSpecs);
        // Load entity ids into MySQL cache table for current api query
        $solrQuery->loadQuery($streamingXpression, $queryDefId, $dbQuery, $sortFieldSpecs, array(0 => false));
        // Create cache entry
        $dbQuery->addQueryDef($queryDefId, $queryString);
    }
    // Use cached entity IDs to fetch requested fields from SOLR
    $dbResults = $solrQuery->fetchQuery($selectFieldSpecs, $streamingXpression, $queryDefId, $dbQuery, $optionsParam, $sortFieldSpecs);

    // Create the primary entity - subentity structure
    $results = convertDBResultsToNestedStructure($entitySpecs, $fieldSpecs, $dbResults, $selectFieldSpecs);

    return $results;
}