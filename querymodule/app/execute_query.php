<?php
require_once dirname(__FILE__) . '/QueryParser.php';
require_once dirname(__FILE__) . '/DatabaseQuery.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';
require_once dirname(__FILE__) . '/parse_fields.php';
require_once dirname(__FILE__) . '/entitySpecs.php';

function executeQuery(array $queryParam=null, array $fieldsParam=null, array $sortParam=null, array $optionsParam=null)
{
    global $PATENT_ENTITY_SPECS;
    $pq = new QueryParser();
    $whereClause = $pq->parse($queryParam);
    if (!$fieldsParam) $fieldsParam = $pq->getFieldsUsed();
    $selectFieldSpecs = parseFieldList($fieldsParam);
    $dbQuery = new DatabaseQuery();
    $dbResults = $dbQuery->queryDatabase($PATENT_ENTITY_SPECS, $whereClause, $pq->getFieldsUsed(), $selectFieldSpecs, $sortParam, $optionsParam);
    $results = convertDBResultsToNestedStructure($PATENT_ENTITY_SPECS, $dbResults, $selectFieldSpecs);
    $results['total_found'] = $dbQuery->getTotalFound();
    return $results;
}