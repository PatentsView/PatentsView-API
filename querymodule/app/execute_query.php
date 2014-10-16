<?php
require_once dirname(__FILE__) . '/QueryParser.php';
require_once dirname(__FILE__) . '/DatabaseQuery.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';
require_once dirname(__FILE__) . '/parse_fields.php';

function executeQuery(array $entitySpecs, array $fieldSpecs, array $queryParam=null, array $fieldsParam=null, array $sortParam=null, array $optionsParam=null)
{
    $pq = new QueryParser();
    $whereClause = $pq->parse($fieldSpecs, $queryParam);
    if (!$fieldsParam) $fieldsParam = $pq->getFieldsUsed();
    $selectFieldSpecs = parseFieldList($fieldSpecs, $fieldsParam);
    $dbQuery = new DatabaseQuery();
    $dbResults = $dbQuery->queryDatabase($entitySpecs, $fieldSpecs, $whereClause, $pq->getFieldsUsed(), $selectFieldSpecs, $sortParam, $optionsParam);
    $results = convertDBResultsToNestedStructure($entitySpecs, $dbResults, $selectFieldSpecs);
    $results['total_found'] = $dbQuery->getTotalFound();
    return $results;
}