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
    global $FIELD_SPECS;

    // This function relies heavily on the concept of grouped items. The results from the API is expected to be an array of
    // primary entities, and within each primary entity there may be an array of entities (inventors, assignees, classes, etc.).

    // This will be used to created dynamic variables. By using dynamic variables we are able to save well over
    // a hundred lines of code, but some readability is lost. For debugging in PHPStorm, which doesn't show
    // dynamic variables in its debugger window, you need to create them as watch variables to see the values.
    // For example, ${$group['priorId']} gets resolved to $priorinventorId; ${$group['groupName']} to $inventors
    $groupVars = $entitySpecs;
    foreach ($groupVars as &$group) {
        $name = $group['name'];
        $group['priorId'] = "prior{$name}Id";
        $group['thisId'] = "this{$name}Id";
        $group['has'] = "has{$name}";
    }
    unset($group); // Need to do this since it was used by reference in the above foreach

    if (!$dbResults) {
        return array($groupVars[0]['groupName'] => null, 'count' => 0);
    }

    $primaryEntity = $groupVars[0];
    // Start with totally empty primary entity structures
    ${$primaryEntity['priorId']} = '';  // used to keep track of when a row represents a different primary entity from the prior one
    ${$primaryEntity['thisId']} = null;
    ${$primaryEntity['groupName']} = array();    // our top-level structure will be a list of primary entities
    ${$primaryEntity['name']} = array();     // dictionary of primary entity field/value pairs

    foreach (array_slice($groupVars,1) as $group) {
        ${$group['priorId']} = '';      // the id of an entity in the prior row
        ${$group['thisId']} = null;     // the id of an entity in this row
        ${$group['name']} = array();    // dictionary of an entities field/value pairs
        ${$group['groupName']} = array();   // array of entities
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
                        if (!in_array(${$group['name']}, ${$group['groupName']})) {
                            ${$group['groupName']}[] = ${$group['name']};
                        }
                        ${$primaryEntity['name']}[$group['groupName']] = ${$group['groupName']};
                    }
                }
                ${$primaryEntity['groupName']}[] = ${$primaryEntity['name']};
            }
            ${$primaryEntity['name']} = array();
            // Clear out the entity for the next primary entity (which is actually the one in this row, which we haven't processed yet
            foreach (array_slice($groupVars,1) as $group) {
                ${$group['groupName']} = array();
                ${$group['priorId']} = '';
            }
        }

        foreach ($row as $apiField => $val) {               // Look through all the field/value pairs
            $fieldSpec = $FIELD_SPECS[$apiField];           // Get the field spec for it
            $tableName = $fieldSpec['table'];
            $fieldValuePair = array($apiField, $val);       // Create an array for it
            if ($tableName == $primaryEntity['table']) {   // If the field is for a primary entity.
                if (!in_array($fieldValuePair, ${$primaryEntity['name']}))
                    ${$primaryEntity['name']}[$apiField] = $val;    // add the field/value to the primary entity
            }
            foreach (array_slice($groupVars,1) as $group) {
                if ($tableName == $group['table']) {
                    // If this row has different entity ID than the prior row, we need to add
                    // the prior entity to the entity list
                    if (${$group['thisId']} != ${$group['priorId']}) {
                        if (${$group['priorId']} != '') {   // Make sure we actually have a prior entity
                            // Only add the entity to the entity list if it's not already in there
                            if (!in_array(${$group['name']}, ${$group['groupName']})) {
                                ${$group['groupName']}[] = ${$group['name']};
                            }
                        }
                        ${$group['name']} = array();
                        ${$group['priorId']} = ${$group['thisId']};
                    }
                    ${$group['name']}[$apiField] = $val;
                }
            }
        }
        ${$primaryEntity['priorId']} = ${$primaryEntity['thisId']};
    }

    // Check each entity type and see if there is an existing one that needs to be added to the entity array
    foreach (array_slice($groupVars,1) as $group) {
        if (${$group['has']}) {
            // Only add the entity to the entity list if it's not already in there
            if (!in_array(${$group['name']}, ${$group['groupName']})) {
                ${$group['groupName']}[] = ${$group['name']};
            }
            ${$primaryEntity['name']}[$group['groupName']] = ${$group['groupName']};
        }
    }

    ${$primaryEntity['groupName']}[] = ${$primaryEntity['name']};

    // Now we go through all the field/value pairs and remove the ones that do not appear in the list of
    // selected fields. This is because when we build the query we need to add ID fields. And we need to keep those
    // when adding the field/value pairs (otherwise we would not get all the entities added, for example when the
    // only has a last name and two different entities, determined by ID, might have the same name).

    for ($p=0; $p<count(${$primaryEntity['groupName']}); $p++) {            // For each primary entity
        foreach (${$primaryEntity['groupName']}[$p] as $primaryKey=>$primaryVal){   // For each key/value pair of the primary entity
            if (gettype($primaryVal) != "array") {                           // If not an array, then a primary entity attribute
                if (!array_key_exists($primaryKey, $selectFieldSpecs)) {     // If it's not in the field list...
                    unset(${$primaryEntity['groupName']}[$p][$primaryKey]); // ...delete it.
                }
            }
            else {                                                          // Else it is an array of entities
                for ($g=0; $g<count($primaryVal); $g++) {                    // For each entity
                    foreach ($primaryVal[$g] as $groupKey=>$groupVal) {      // For each key/value pair of the entity
                        if (!array_key_exists($groupKey, $selectFieldSpecs)) {
                            unset(${$primaryEntity['groupName']}[$p][$primaryKey][$g][$groupKey]);
                        }
                    }
                }
            }
        }
    }

    return array($primaryEntity['groupName'] => ${$primaryEntity['groupName']}, 'count' => count(${$primaryEntity['groupName']}));
}

