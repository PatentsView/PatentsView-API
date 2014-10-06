<?php
require_once dirname(__FILE__) . '/QueryParser.php';
require_once dirname(__FILE__) . '/DatabaseQuery.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';
require_once dirname(__FILE__) . '/parse_fields.php';

function executeQuery(array $queryParam=null, array $fieldsParam=null, array $sortParam=null, array $optionsParam=null)
{
    $pq = new QueryParser();
    $whereClause = $pq->parse($queryParam);
    if (!$fieldsParam) $fieldsParam = $pq->getFieldsUsed();
    $selectFieldSpecs = parseFieldList($fieldsParam);
    $dbQuery = new DatabaseQuery();
    $dbResults = $dbQuery->queryDatabase($whereClause, $pq->getFieldsUsed(), $selectFieldSpecs, $sortParam, $optionsParam);
    $results = convertDBResultsToNestedStructure($dbResults, $selectFieldSpecs);
    return $results;
}