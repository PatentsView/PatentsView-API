<?php
require_once dirname(__FILE__) . '/entitySpecs.php';


/**
 * This function will convert an array of rows of columns of data into an array of primary entities, with each element
 * containing the primary entity fields and arrays of secondary entities (as needed).
 * @param array $dbResults
 * @param array $selectFieldSpecs
 * @return array
 */
function convertDBResultsToNestedStructure(array $entitySpecs, array $dbResults=null, array $selectFieldSpecs=null)
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

    foreach (array_slice($groupVars,1) as $group) {
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
        foreach ($row as $apiField => $val)
            // Check to make sure the field was in the original field list, and not a field we added
            if (isset($selectFieldSpecs[$apiField]))
                ${$primaryEntityGroup['entity_name']}[$apiField] = $val;    // add the field/value to the primary entity

        foreach (array_slice($groupVars, 1) as $group) {
            if (isset($dbResults[$group['group_name']])) {
                ${$group['group_name']} = array();
                foreach (${$group['byPrimaryEntityId']}[${$primaryEntityGroup['thisId']}] as $groupRow) {
                    ${$group['entity_name']} = $groupRow;
                    unset(${$group['entity_name']}[$primaryEntityGroup['keyId']]);
                    // Check to make sure the field was in the original field list, and not a field we added
                    foreach ($groupRow as $apiField=>$val)
                        if (!isset($selectFieldSpecs[$apiField]))
                            unset(${$group['entity_name']}[$apiField]);
                    ${$group['group_name']}[] = ${$group['entity_name']};
                }
                ${$primaryEntityGroup['entity_name']}[$group['group_name']] = ${$group['group_name']};
            }
        }
        ${$primaryEntityGroup['group_name']}[] = ${$primaryEntityGroup['entity_name']};
    }

    return array($primaryEntityGroup['group_name'] => ${$primaryEntityGroup['group_name']}, 'count' => count(${$primaryEntityGroup['group_name']}));
}


// This was the function that was used when we did one large all combined join with all fields.
// At that time $dbResults would be one array of rows. The problem was that the multiplier effect of making
// all those joins would mean we could have more than 10k rows for some single patents. Just too much data.
// GY 2014-10-11: Just keeping it here for reference for a short time. If we don't come back to it, then we can delete it.
/*
function convertDBResultsToNestedStructure_SingleQuery(array $entitySpecs, array $dbResults=null, array $selectFieldSpecs=null)
{
    global $PATENT_FIELD_SPECS;

    // This function relies heavily on the concept of grouped items. The results from the API is expected to be an array of
    // primary entities, and within each primary entity there may be an array of entities (inventors, assignees, classes, etc.).

    // This will be used to created dynamic variables. By using dynamic variables we are able to save well over
    // a hundred lines of code, but some readability is lost. For debugging in PHPStorm, which doesn't show
    // dynamic variables in its debugger window, you need to create them as watch variables to see the values.
    // For example, ${$group['priorId']} gets resolved to $priorinventorId; ${$group['group_name']} to $inventors
    $groupVars = $entitySpecs;
    foreach ($groupVars as &$group) {
        $name = $group['entity_name'];
        $group['priorId'] = "prior{$name}Id";
        $group['thisId'] = "this{$name}Id";
        $group['has'] = "has{$name}";
    }
    unset($group); // Need to do this since it was used by reference in the above foreach

    if (!$dbResults) {
        return array($groupVars[0]['group_name'] => null, 'count' => 0);
    }

    $primaryEntity = $groupVars[0];
    // Start with totally empty primary entity structures
    ${$primaryEntity['priorId']} = '';  // used to keep track of when a row represents a different primary entity from the prior one
    ${$primaryEntity['thisId']} = null;
    ${$primaryEntity['group_name']} = array();    // our top-level structure will be a list of primary entities
    ${$primaryEntity['entity_name']} = array();     // dictionary of primary entity field/value pairs

    foreach (array_slice($groupVars,1) as $group) {
        ${$group['priorId']} = '';      // the id of an entity in the prior row
        ${$group['thisId']} = null;     // the id of an entity in this row
        ${$group['entity_name']} = array();    // dictionary of an entities field/value pairs
        ${$group['group_name']} = array();   // array of entities
        // Check once to see if there are any fields for an entity
        ${$group['has']} = isset($dbResults[0][$group['keyId']]);
    }

    // Iterate through the DB results one row at a time. It might be possible to slice or subset the rows by
    // primary entity ID, but iterating straight through the list is probably most efficient
    foreach ($dbResults as $row) {
        ${$primaryEntity['thisId']} = $row[$primaryEntity['keyId']];
        foreach (array_slice($groupVars,1) as $group) {
            if (${$group['has']})
                ${$group['thisId']} = $row[$group['keyId']];
        }

        // Find out if the primary entity ID on this row is the same as the previous row. If not, then we need to add the
        // existing entity arrays to our results and then empty them out to start a new primary entity.
        if (${$primaryEntity['thisId']} != ${$primaryEntity['priorId']}) {
            if (${$primaryEntity['priorId']} != '') { //Need to skip the first time so we don't add the original empty structures
                foreach (array_slice($groupVars,1) as $group) {
                    if (${$group['has']}) {
                        // Only add the entity to the entity list if it's not already in there
                        if (!in_array(${$group['entity_name']}, ${$group['group_name']})) {
                            ${$group['group_name']}[] = ${$group['entity_name']};
                        }
                        ${$primaryEntity['entity_name']}[$group['group_name']] = ${$group['group_name']};
                    }
                }
                ${$primaryEntity['group_name']}[] = ${$primaryEntity['entity_name']};
            }
            ${$primaryEntity['entity_name']} = array();
            // Clear out the entity for the next primary entity (which is actually the one in this row, which we haven't processed yet
            foreach (array_slice($groupVars,1) as $group) {
                ${$group['group_name']} = array();
                ${$group['priorId']} = '';
            }
        }

        foreach ($row as $apiField => $val) {               // Look through all the field/value pairs
            $fieldSpec = $PATENT_FIELD_SPECS[$apiField];           // Get the field spec for it
            $tableName = $fieldSpec['table'];
            $fieldValuePair = array($apiField, $val);       // Create an array for it
            if ($tableName == $primaryEntity['table']) {   // If the field is for a primary entity.
                if (!in_array($fieldValuePair, ${$primaryEntity['entity_name']}))
                    ${$primaryEntity['entity_name']}[$apiField] = $val;    // add the field/value to the primary entity
            }
            foreach (array_slice($groupVars,1) as $group) {
                if ($tableName == $group['table']) {
                    // If this row has different entity ID than the prior row, we need to add
                    // the prior entity to the entity list
                    if (${$group['thisId']} != ${$group['priorId']}) {
                        if (${$group['priorId']} != '') {   // Make sure we actually have a prior entity
                            // Only add the entity to the entity list if it's not already in there
                            if (!in_array(${$group['entity_name']}, ${$group['group_name']})) {
                                ${$group['group_name']}[] = ${$group['entity_name']};
                            }
                        }
                        ${$group['entity_name']} = array();
                        ${$group['priorId']} = ${$group['thisId']};
                    }
                    ${$group['entity_name']}[$apiField] = $val;
                }
            }
        }
        ${$primaryEntity['priorId']} = ${$primaryEntity['thisId']};
    }

    // Check each entity type and see if there is an existing one that needs to be added to the entity array
    foreach (array_slice($groupVars,1) as $group) {
        if (${$group['has']}) {
            // Only add the entity to the entity list if it's not already in there
            if (!in_array(${$group['entity_name']}, ${$group['group_name']})) {
                ${$group['group_name']}[] = ${$group['entity_name']};
            }
            ${$primaryEntity['entity_name']}[$group['group_name']] = ${$group['group_name']};
        }
    }

    ${$primaryEntity['group_name']}[] = ${$primaryEntity['entity_name']};

    // Now we go through all the field/value pairs and remove the ones that do not appear in the list of
    // selected fields. This is because when we build the query we need to add ID fields. And we need to keep those
    // when adding the field/value pairs (otherwise we would not get all the entities added, for example when the
    // only has a last name and two different entities, determined by ID, might have the same name).

    for ($p=0; $p<count(${$primaryEntity['group_name']}); $p++) {            // For each primary entity
        foreach (${$primaryEntity['group_name']}[$p] as $primaryKey=>$primaryVal){   // For each key/value pair of the primary entity
            if (gettype($primaryVal) != "array") {                           // If not an array, then a primary entity attribute
                if (!array_key_exists($primaryKey, $selectFieldSpecs)) {     // If it's not in the field list...
                    unset(${$primaryEntity['group_name']}[$p][$primaryKey]); // ...delete it.
                }
            }
            else {                                                          // Else it is an array of entities
                for ($g=0; $g<count($primaryVal); $g++) {                    // For each entity
                    foreach ($primaryVal[$g] as $groupKey=>$groupVal) {      // For each key/value pair of the entity
                        if (!array_key_exists($groupKey, $selectFieldSpecs)) {
                            unset(${$primaryEntity['group_name']}[$p][$primaryKey][$g][$groupKey]);
                        }
                    }
                }
            }
        }
    }

    return array($primaryEntity['group_name'] => ${$primaryEntity['group_name']}, 'count' => count(${$primaryEntity['group_name']}));
}
*/

