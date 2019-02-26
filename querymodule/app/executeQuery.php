<?php
require_once dirname(__FILE__) . '/QueryParser.php';
require_once dirname(__FILE__) . '/DatabaseQuery.php';
require_once dirname(__FILE__) . '/convertDBResultsToNestedStructure.php';

function executeQuery(array $entitySpecs, array $fieldSpecs, array $queryParam = null, array $fieldsParam = null, array $sortParam = null, array $optionsParam = null)
{
    $qp = new QueryParser();

    $entitySpecificWhereClauses = array();

    // Get each subentity-specific "where" clause. This will be used when getting the results from the DB and needing
    // to limit the results based on the "matched_subentities_only" flag.
    foreach ($entitySpecs as $entity) {
        $entityName = $entity['entity_name'];
        $entitySpecificWhereClauses[$entityName] = $qp->parse($fieldSpecs, $queryParam, $entityName);
    }

    // Build the where clause to determine the primary entities for the results
    $whereClause = $qp->parse($fieldSpecs, $queryParam, 'all');

    // If the caller did not explicitly list fields to be returned, get them from the other parameters.
    # if (!$fieldsParam) $fieldsParam = $qp->getFieldsUsed();
    // Changed so that if the caller did not explicitly list fields to be returned, we will use a pre-defined set
    // for the primary entity.
    if (!$fieldsParam) $fieldsParam = $entitySpecs[0]['default_fields'];

    // Get the FieldSpecs for the list of fields to be returned.
    $selectFieldSpecs = parseFieldList($fieldSpecs, $fieldsParam);

    $dbQuery = new DatabaseQuery();

    // Run the query against the DB
    $dbResults = $dbQuery->queryDatabase($entitySpecs, $fieldSpecs, $whereClause, $qp->getFieldsUsed(),
        $entitySpecificWhereClauses, $qp->getOnlyAndsWereUsed(), $selectFieldSpecs, $sortParam, $optionsParam);
    $count_results = array();
    foreach ($dbQuery->getTotalCounts() as $entityName => $count)
        $count_results['total_' . $entityName . '_count'] = $count;
    unset($dbQuery);
//    file_put_contents('php://stderr', print_r($dbResults, TRUE));
//    file_put_contents('php://stderr', print_r("\n", TRUE));
    // Convert the DB result structures to PHO structures. The DB results will be in multiple tables, and the
    // PHP structures will be nested (only one-level) PHP arrays.
    $results = convertDBResultsToNestedStructure($entitySpecs, $dbResults, $selectFieldSpecs);

    foreach ($count_results as $entityName => $count)
        $results[$entityName] = $count;
    return array("status" => "success", "payload" => $results);
}