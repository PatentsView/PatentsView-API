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

    $count_results = $resultGroup["count_results"];
    $return_array = array();

    $main_group = $entitySpecs[0]["group_name"];
    // Loops through each key value of base entity to be returned
    foreach (array_unique(array_keys($resultGroup["db_results"][$entitySpecs[0]["entity_name"]])) as $entityIdValue) {
        $currDocEntityArray = array();
        foreach ($entitySpecs as $entitySpec) {
            $isPrimaryEntity = false;
            if ($entitySpec["entity_name"] == $entitySpecs[0]["entity_name"]) {
                $isPrimaryEntity = true;
            }
            // Are fields from current entity requested?
            if (array_key_exists($entitySpec["entity_name"], $selectFieldSpecs)) {
                // Does the current primary entity value exist in fetched result set
                if (array_key_exists($entityIdValue, $resultGroup["db_results"][$entitySpec["entity_name"]])) {
                    $currentEntityDistinctKey = $entitySpec["distinctCountId"];
                    $entityKeys = array();
                    $fullNullExists = false;
                    $docCounter = 0;
                    // Loop through sub entity document list for current primary entity key value
                    foreach ($resultGroup["db_results"][$entitySpec["entity_name"]][$entityIdValue] as $currentSolrDoc) {
                        $currentEntityDistinctKeyField = $entitySpec["group_name"] . "." . $currentEntityDistinctKey;
                        if (property_exists($currentSolrDoc, $currentEntityDistinctKeyField)) {
                            if (array_key_exists($currentSolrDoc->$currentEntityDistinctKeyField, $entityKeys)) {
                                continue;
                            }
                        }
                        $docCounter += 1;
                        if ($docCounter % 1000 == 0) {
                            $docCounter = $docCounter;
                        }
                        $allNulls = true;
                        $currentSubDocArray = array();
                        foreach (array_keys($selectFieldSpecs[$entitySpec["entity_name"]]) as $field) {
                            $field_name = $entitySpec["group_name"] . "." . $field;
                            if ($isPrimaryEntity) {
                                $field_name = $field;
                            }
                            if ($field == $entitySpecs[0]["solr_key_id"]) {
                                //$field_name = $field;
                                //if ($entitySpec["entity_name"] != $entitySpecs[0]["entity_name"])
                                continue;
                            }
                            try {
                                $currentSubDocArray[$field] = $currentSolrDoc->$field_name;
                                $allNulls = false;
                            } catch (ErrorException $e) {
                                $currentSubDocArray[$field] = null;
                            }
                        }
                        if ($entitySpec["entity_name"] == $entitySpecs[0]["entity_name"]) {
                            $currDocEntityArray = array_merge($currDocEntityArray, $currentSubDocArray);
                        } else {
                            if (!$allNulls || ($allNulls && !$fullNullExists)) {
                                $currDocEntityArray[$entitySpec["group_name"]][] = $currentSubDocArray;
                                if (property_exists($currentSolrDoc, $currentEntityDistinctKeyField)) {
                                    $entityKeys[$currentSolrDoc->$currentEntityDistinctKeyField] = 1;
                                }
                                if ($allNulls) {
                                    $fullNullExists = true;
                                }
                            }
                        }
                    }
                }
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
