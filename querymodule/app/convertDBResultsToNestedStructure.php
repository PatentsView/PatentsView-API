<?php
require_once dirname(__FILE__) . '/entitySpecs.php';

function validateDate($date, $format = 'Y-m-d\TH:i:s\Z')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * This function will convert an array of rows of columns of data into an array of primary entities, with each element
 * containing the primary entity fields and arrays of secondary entities (as needed).
 * @param array $dbResults
 * @param array $selectFieldSpecs
 * @return array
 */
function convertDBResultsToNestedStructure(array $entitySpecs, array $fieldSpecs, array $resultGroup = null, array $selectFieldSpecs = null)
{
    // This function relies heavily on the concept of grouped items. The results from the API is expected to be an array of
    // primary entities, and within each primary entity there may be an array of entities (inventors, assignees, classes, etc.).

    // This will be used to created dynamic variables. By using dynamic variables we are able to save well over
    // a hundred lines of code, but some readability is lost. For debugging in PHPStorm, which doesn't show
    // dynamic variables in its debugger window, you need to create them as watch variables to see the values.
    // For example, ${$group['priorId']} gets resolved to $priorinventorId; ${$group['group_name']} to $inventors
    $dbResults = $resultGroup["db_results"];
    $count_results = $resultGroup["count_results"];
    $return_array = array();

    $main_group = $entitySpecs[0]["group_name"];
    foreach (array_unique(array_keys($dbResults[$entitySpecs[0]["entity_name"]])) as $entityIdValue) {

        $currDocEntityArray = array();
        foreach ($entitySpecs as $entitySpec) {
            $isPrimaryEntity = false;
            if ($entitySpec["entity_name"] == $entitySpecs[0]["entity_name"]) {
                $isPrimaryEntity = true;
            }
            if (array_key_exists($entitySpec["entity_name"], $selectFieldSpecs)) {

                if (array_key_exists($entityIdValue, $dbResults[$entitySpec["entity_name"]])) {


                    $currentSolrDocs = $dbResults[$entitySpec["entity_name"]][$entityIdValue];

                    foreach ($currentSolrDocs as $currentSolrDoc) {
                        $currentSubDocArray = array();
                        foreach (array_keys($selectFieldSpecs[$entitySpec["entity_name"]]) as $field) {
                            $field_name = $entitySpec["group_name"] . "." . $field;
                            if ($isPrimaryEntity) {
                                $field_name = $field;
                                $field_key = $field;
                            }
                            if ($field == $entitySpecs[0]["solr_fetch_id"]) {
                                //$field_name = $field;
                                //if ($entitySpec["entity_name"] != $entitySpecs[0]["entity_name"])
                                continue;
                            }
                            try {
                                $currentSubDocArray[$field] = $currentSolrDoc->$field_name;
                            } catch (ErrorException $e) {
                                $currentSubDocArray[$field] = null;
                            }
                        }
                        if ($entitySpec["entity_name"] == $entitySpecs[0]["entity_name"]) {
                            $currDocEntityArray = array_merge($currentSubDocArray, $currentSubDocArray);
                        } else {
                            $currDocEntityArray[$entitySpec["group_name"]][] = $currentSubDocArray;

                        }

                    }

                }
                //$currDocArray[] = $currDocEntityArray;
            }

        }
        if (count($currDocEntityArray) > 0)
            $return_array[$main_group][] = $currDocEntityArray;
    }


    $doc_count = 0;
    if (array_key_exists($main_group, $return_array)) {
        $doc_count = count($return_array[$main_group]);
    }
    $return_array["count"] = $doc_count;
    foreach (array_keys($count_results) as $count_key) {
        $return_array[$count_key] = $count_results[$count_key];
    }
    return $return_array;

}
