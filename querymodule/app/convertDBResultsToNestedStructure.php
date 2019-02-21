<?php
require_once dirname(__FILE__) . '/entitySpecs.php';


/**
 * This function will convert an array of rows of columns of data into an array of primary entities, with each element
 * containing the primary entity fields and arrays of secondary entities (as needed).
 * @param array $dbResults
 * @param array $selectFieldSpecs
 * @return array
 */
function convertDBResultsToNestedStructure(array $entitySpecs, array $dbResults = null, array $selectFieldSpecs = null)
{
    // This function relies heavily on the concept of grouped items. The results from the API is expected to be an array of
    // primary entities, and within each primary entity there may be an array of entities (inventors, assignees, classes, etc.).

    // This will be used to created dynamic variables. By using dynamic variables we are able to save well over
    // a hundred lines of code, but some readability is lost. For debugging in PHPStorm, which doesn't show
    // dynamic variables in its debugger window, you need to create them as watch variables to see the values.
    // For example, ${$group['priorId']} gets resolved to $priorinventorId; ${$group['group_name']} to $inventors


    $groupVars = $entitySpecs;
    foreach ($groupVars as &$group) {
        $name = $group['entity_name'];
        $group['thisId'] = "this{$name}Id";
        $group['has'] = "has{$name}";
        $group['byPrimaryEntityId'] = "{$name}ByPrimaryEntityId";
    }
    unset($group); // Need to do this since it was used by reference in the above foreach

    if (!$dbResults[$groupVars[0]['group_name']]) {
        return array($groupVars[0]['group_name'] => null, 'count' => 0);
    }

    $primaryEntityGroup = $groupVars[0];
    // Start with totally empty primary entity structures
    ${$primaryEntityGroup['thisId']} = null;
    ${$primaryEntityGroup['group_name']} = array();    // our top-level structure will be a list of primary entities
    ${$primaryEntityGroup['entity_name']} = array();     // dictionary of primary entity field/value pairs

    foreach (array_slice($groupVars, 1) as $group) {
        ${$group['thisId']} = null;     // the id of an entity in this row
        ${$group['entity_name']} = array();    // dictionary of an entities field/value pairs
        ${$group['group_name']} = array();   // array of entities
        ${$group['byPrimaryEntityId']} = array();
        // Check once to see if there are any fields for an entity
        ${$group['has']} = isset($dbResults[$group['group_name']]);
    }

    // Slice the groups into associative arrays with the primary entity ID as the key. We do this so we don't
    // have to iterate through every group row for every primary entity instance.
    foreach (array_slice($groupVars, 1) as $group)
        if (isset($dbResults[$group['group_name']]))
            foreach ($dbResults[$group['group_name']] as $groupRow) {
                if (!isset(${$group['byPrimaryEntityId']}[$groupRow[$primaryEntityGroup['keyId']]]))
                    ${$group['byPrimaryEntityId']}[$groupRow[$primaryEntityGroup['keyId']]] = array();
                ${$group['byPrimaryEntityId']}[$groupRow[$primaryEntityGroup['keyId']]][] = $groupRow;
            }


    // Iterate through the primary entity DB results one row at a time. It might be possible to slice or subset the rows by
    // primary entity ID, but iterating straight through the list is probably most efficient

    foreach ($dbResults[$primaryEntityGroup['group_name']] as $row) {
        ${$primaryEntityGroup['thisId']} = $row[$primaryEntityGroup['keyId']];
        ${$primaryEntityGroup['entity_name']} = array();
        foreach ($row as $apiField => $val) {
            // Check to make sure the field was in the original field list, and not a field we added

            if (isset($selectFieldSpecs[$apiField])) {

                ${$primaryEntityGroup['entity_name']}[$apiField] = $val;    // add the field/value to the primary entity
            }
        }
        foreach (array_slice($groupVars, 1) as $group) {

            if (isset($dbResults[$group['group_name']])) {
                ${$group['group_name']} = array();
                if (array_key_exists(${$primaryEntityGroup['thisId']}, ${$group['byPrimaryEntityId']})) {
                    foreach (${$group['byPrimaryEntityId']}[${$primaryEntityGroup['thisId']}] as $groupRow) {
                        ${$group['entity_name']} = $groupRow;
                        unset(${$group['entity_name']}[$primaryEntityGroup['keyId']]);
                        // Check to make sure the field was in the original field list, and not a field we added
//                        foreach ($groupRow as $apiField => $val){
////                            file_put_contents('php://stderr', print_r($selectFieldSpecs, TRUE));
////                            file_put_contents('php://stderr', print_r("\n", TRUE));
////                            file_put_contents('php://stderr', print_r($apiField, TRUE));
////                            file_put_contents('php://stderr', print_r("\n", TRUE));
////                            file_put_contents('php://stderr', print_r($group['entity_name'], TRUE));
////                            file_put_contents('php://stderr', print_r("\n", TRUE));
//
//                            if (!isset($selectFieldSpecs[$apiField]))
//                                unset(${$group['entity_name']}[$apiField]);
//                        }
                        ${$group['group_name']}[] = ${$group['entity_name']};
                    }
                    ${$primaryEntityGroup['entity_name']}[$group['group_name']] = ${$group['group_name']};
                }
            }
        }
        ${$primaryEntityGroup['group_name']}[] = ${$primaryEntityGroup['entity_name']};
    }


    return array("status" => "success", "payload" => array($primaryEntityGroup['group_name'] => ${$primaryEntityGroup['group_name']}, 'count' => count(${$primaryEntityGroup['group_name']})));
}
