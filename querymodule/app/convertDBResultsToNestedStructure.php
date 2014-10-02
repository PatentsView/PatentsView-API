<?php
require_once dirname(__FILE__) . '/fieldSpecs.php';


/**
 * This function will convert an array of rows of columns of data into an array of patents, with each element
 * containing the patent fields and an array of inventors and assignees (as needed).
 * @param array $dbResults
 * @return array
 */
function convertDBResultsToNestedStructure(array $dbResults=null, array $selectFieldSpecs=null)
{
    global $FIELD_SPECS;

    // This function relies heavily on the concept of grouped items. The results from the API is expected to be an array of
    // patents, and within each patent there may be an array of entities (inventors, assignees, classes, etc.).

    // This will be used to created dynamic variables. By using dynamic variables we are able to save well over
    // a hundred lines of code, but some readability is lost. For debugging in PHPStorm, which doesn't show
    // dynamic variables in its debugger window, you need to create them as watch variables to see the values.
    $groupVars = array(
        array('single'=>'inventor', 'array'=>'inventors', 'priorId'=>'priorInventorId', 'thisId'=>'thisInventorId', 'has'=>'hasInventor', 'keyId'=>'inventor_id', 'table'=>'inventor_flat'),
        array('single'=>'assignee', 'array'=>'assignees', 'priorId'=>'priorAssigneeId', 'thisId'=>'thisAssigneeId', 'has'=>'hasAssignee', 'keyId'=>'assignee_id', 'table'=>'assignee_flat'),
        array('single'=>'application', 'array'=>'applications', 'priorId'=>'priorApplicationId', 'thisId'=>'thisApplicationId', 'has'=>'hasApplication', 'keyId'=>'application_id', 'table'=>'application'),
        array('single'=>'IPC', 'array'=>'IPCs', 'priorId'=>'priorIPCId', 'thisId'=>'thisIPCId', 'has'=>'hasIPC', 'keyId'=>'ipc_id', 'table'=>'ipcr'),
        array('single'=>'applicationcitation', 'array'=>'application_citations', 'priorId'=>'priorApplicationCitationId', 'thisId'=>'thisApplicationCitationId', 'has'=>'hasApplicationCitation', 'keyId'=>'applicationcitation_id', 'table'=>'usapplicationcitation'),
        array('single'=>'patentcitation', 'array'=>'patent_citations', 'priorId'=>'priorPatentCitationId', 'thisId'=>'thisPatentCitationId', 'has'=>'hasPatentCitation', 'keyId'=>'patentcitation_id', 'table'=>'uspatentcitation'),
        array('single'=>'uspc', 'array'=>'uspcs', 'priorId'=>'priorUSPCId', 'thisId'=>'thisUSPCId', 'has'=>'hasUSPC', 'keyId'=>'uspc_id', 'table'=>'uspc_flat')
    );

    if (!$dbResults) {
        return array('patents' => null, 'count' => 0);
    }

    // Start with totally empty structures
    $priorPatentId = '';  // used to keep track of when a row represents a different patent from the prior one
    $thisPatentId = null;
    $patents = array();    // our top-level structure will be a list of patents
    $patent = array();     // dictionary of patent field/value pairs

    foreach ($groupVars as $group) {
        ${$group['priorId']} = '';      // the id of an entity in the prior row
        ${$group['thisId']} = null;     // the id of an entity in this row
        ${$group['single']} = array();  // dictionary of an entities field/value pairs
        ${$group['array']} = array();   // array of entities
        // Check once to see if there are any fields for an entity
        ${$group['has']} = isset($dbResults[0][$group['keyId']]);
    }

    // Iterate through the DB results one row at a time. It might be possible to slice or subset the rows by
    // patent ID, but iterating straight through the list is probably most efficient
    foreach ($dbResults as $row) {
        $thisPatentId = $row['patent_id'];
        foreach ($groupVars as $group) {
            if (${$group['has']})
                ${$group['thisId']} = $row[$group['keyId']];
        }

        // Find out if the patent ID on this row is the same as the previous row. If not, then we need to add the
        // existing entity arrays to our results and then empty them out to start a new patent.
        if ($thisPatentId != $priorPatentId) {
            if ($priorPatentId != '') { //Need to skip the first time so we don't add the original empty structures
                foreach ($groupVars as $group) {
                    if (${$group['has']}) {
                        // Only add the entity to the entity list if it's not already in there
                        if (!in_array(${$group['single']}, ${$group['array']})) {
                            ${$group['array']}[] = ${$group['single']};
                        }
                        $patent[$group['array']] = ${$group['array']};
                    }
                }
                $patents[] = $patent;
            }
            $patent = array();
            // Clear out the entity for the next patent (which is actually the one in this row, which we haven't processed yet
            foreach ($groupVars as $group) {
                ${$group['array']} = array();
                ${$group['priorId']} = '';
            }
        }

        foreach ($row as $apiField => $val) {               // Look through all the field/value pairs
            $fieldSpec = $FIELD_SPECS[$apiField];           // Get the field spec for it
            $tableName = $fieldSpec['table_name'];
            $fieldValuePair = array($apiField, $val);       // Create an array for it
            if ($tableName == 'patent') {                   // If the field is for a patent.
                if (!in_array($fieldValuePair, $patent))
                    $patent[$apiField] = $val;              // add the field/value to the patent
            }
            foreach ($groupVars as $group) {
                if ($tableName == $group['table']) {
                    // If this row has different entity ID than the prior row, we need to add
                    // the prior entity to the entity list
                    if (${$group['thisId']} != ${$group['priorId']}) {
                        if (${$group['priorId']} != '') {       // Make sure we actually have a prior entity
                            // Only add the entity to the entity list if it's not already in there
                            if (!in_array(${$group['single']}, ${$group['array']})) {
                                ${$group['array']}[] = ${$group['single']};
                            }
                        }
                        ${$group['single']} = array();
                        ${$group['priorId']} = ${$group['thisId']};
                    }
                    ${$group['single']}[$apiField] = $val;
                }
            }
        }
        $priorPatentId = $thisPatentId;
    }

    // Check each entity type and see if there is an existing one that needs to be added to the entity array
    foreach ($groupVars as $group) {
        if (${$group['has']}) {
            // Only add the entity to the entity list if it's not already in there
            if (!in_array(${$group['single']}, ${$group['array']})) {
                ${$group['array']}[] = ${$group['single']};
            }
            $patent[$group['array']] = ${$group['array']};
        }
    }

    $patents[] = $patent;

    // Now we go through all the field/value pairs and remove the ones that do not appear in the list of
    // selected fields. This is because when we build the query we need to add ID fields. And we need to keep those
    // when adding the field/value pairs (otherwise we would not get all the entities added, for example when the
    // only has a last name and two different entities, determined by ID, might have the same name).

    for ($p=0; $p<count($patents); $p++) {                                  // For each patent
        foreach ($patents[$p] as $patentkey=>$patentval){                   // For each key/value pair of the patent
            if (gettype($patentval) != "array") {                           // If not an array, then a patent attribute
                if (!array_key_exists($patentkey, $selectFieldSpecs)) {     // If it's not in the field list...
                    unset($patents[$p][$patentkey]);                        // ...delete it.
                }
            }
            else {                                                          // Else it is an array of entities
                for ($g=0; $g<count($patentval); $g++) {                    // For each entity
                    foreach ($patentval[$g] as $groupkey=>$groupval) {      // For each key/value pair of the entity
                        if (!array_key_exists($groupkey, $selectFieldSpecs)) {
                            unset($patents[$p][$patentkey][$g][$groupkey]);
                        }
                    }
                }
            }
        }
    }

    return array('patents' => $patents, 'count' => count($patents));
}


function convertDBResultsToNestedStructure_Saved(array $dbResults=null)
{
    global $FIELD_SPECS;

    if (!$dbResults) {
        return array('patents' => null, 'count' => 0);
    }

    // Start with totally empty structures
    $priorPatentId = '';  // used to keep track of when a row represents a different patent from the prior one
    $priorInventorId = '';  // used to keep track of when a row represents a different inventor from the prior one
    $priorAssigneeId = ''; // used to keep track of when a row represents a different assignee from the prior one
    $priorIPCId = ''; // used to keep track of when a row represents a different IPC from the prior one
    $priorApplicationCitationId = '';
    $thisPatentId = null;
    $thisInventorId = null;
    $thisAssigneeId = null;
    $thisIPCId = null;
    $thisApplicationCitationId = null;
    $patents = array();    // our top-level structure will be a list of patents
    $patent = array();     // dictionary of patent field/value pairs
    $inventors = array();  // list of inventors
    $inventor = array();   // dictionary of patent field_value pairs
    $assignees = array();  // list of assignees
    $assignee = array();   // dictionary of assignee field_value pairs
    $IPCs = array();  // list of IPCs
    $IPC = array();   // dictionary of IPC field_value pairs
    $applicationcitations = array();
    $applicationcitation = array();

    // Check once to see if there are any inventor or assignee fields
    $hasInventor = isset($dbResults[0]['inventor_id']);
    $hasAssignee = isset($dbResults[0]['assignee_id']);
    $hasIPC = isset($dbResults[0]['ipc_id']);
    $hasApplicationCitation = isset($dbResults[0]['applicationcitation_id']);

    // Iterate through the DB results one row at a time. It might be possible to slice or subset the rows by
    // patent ID, but iterating straight through the list is probably most efficient
    foreach ($dbResults as $row) {
        $thisPatentId = $row['patent_id'];
        if ($hasInventor)
            $thisInventorId = $row['inventor_id'];
        if ($hasAssignee)
            $thisAssigneeId = $row['assignee_id'];
        if ($hasIPC)
            $thisIPCId = $row['ipc_id'];
        if ($hasApplicationCitation)
            $thisApplicationCitationId = $row['applicationcitation_id'];

        // Find out if the patent ID on this row is the same as the previous row. If not, then we need to add the
        // existing structures to our results and then empty them out to start a new patent.
        if ($thisPatentId != $priorPatentId) {
            if ($priorPatentId != '') { //Need to skip the first time so we don't add the original empty structures
                if ($hasInventor) {
                    // Only add the inventor to the inventor list if it's not already in there
                    if (!in_array($inventor, $inventor))
                        $inventors[] = $inventor;
                    $patent['inventors'] = $inventors;
                }
                if ($hasAssignee) {
                    // Only add the assignee to the assignee list if it's not already in there
                    if (!in_array($inventor, $inventors))
                        $assignees[] = $assignee;
                    $patent['assignees'] = $assignees;
                }
                if ($hasIPC) {
                    // Only add the IPC to the IPC list if it's not already in there
                    if (!in_array($IPC, $IPCs))
                        $IPCs[] = $IPC;
                    $patent['IPCs'] = $IPCs;
                }
                if ($hasApplicationCitation) {
                    if (!in_array($applicationcitation, $applicationcitations))
                        $applicationcitations[] = $applicationcitation;
                    $patent['ApplicationCitations'] = $applicationcitations;
                }
                $patents[] = $patent;
            }
            $patent = array();
            $inventors = array();
            $assignees = array();
            $IPCs = array();
            $applicationcitations = array();
            $priorInventorId = '';
            $priorAssigneeId = '';
            $priorIPCId = '';
            $priorApplicationCitationId = '';
        }

        foreach ($row as $apiField => $val) {             // Look through all the field/value pairs
            $fieldSpec = $FIELD_SPECS[$apiField];       // Get the field spec for it
            $tableName = $fieldSpec['table_name'];
            $fieldValuePair = array($apiField, $val);   // Create an array for it
            if ($tableName == 'patent') {  // If the field is for a patent.
                if (!in_array($fieldValuePair, $patent))
                    $patent[$apiField] = $val;   // add the field/value to the patent
            } elseif ($tableName == 'inventor') {
                // If this row has different inventor ID than the prior row, we need to add
                // the prior inventor to the inventor list
                if ($thisInventorId != $priorInventorId) {
                    if ($priorInventorId != '') {       // Make sure we actually have a prior inventor
                        // Only add the inventor to the inventor list if it's not already in there
                        if (!in_array($inventor, $inventors))
                            $inventors[] = $inventor;
                    }
                    $inventor = array();
                    $priorInventorId = $thisInventorId;
                }
                $inventor[$apiField] = $val;
            } elseif ($tableName == 'assignee') {
                // If this row has different assignee ID than the prior row, we need to add
                // the prior assignee to the assignee list
                if ($thisAssigneeId != $priorAssigneeId) {
                    if ($priorAssigneeId != '') {       // Make sure we actually have a prior assignee
                        // Only add the assignee to the assignee list if it's not already in there
                        if (!in_array($assignee, $assignees))
                            $assignees[] = $assignee;
                    }
                    $assignee = array();
                    $priorAssigneeId = $thisAssigneeId;
                }
                $assignee[$apiField] = $val;
            } elseif ($tableName == 'ipcr') {
                // If this row has different ipc ID than the prior row, we need to add
                // the prior IPC to the IPC list
                if ($thisIPCId != $priorIPCId) {
                    if ($priorIPCId != '') {       // Make sure we actually have a prior IPC
                        // Only add the IPC to the IPC list if it's not already in there
                        if (!in_array($IPC, $IPCs))
                            $IPCs[] = $IPC;
                    }
                    $IPC= array();
                    $priorIPCId = $thisIPCId;
                }
                $IPC[$apiField] = $val;
            } elseif ($tableName == 'usapplicationcitation') {
                if ($thisApplicationCitationId != $priorApplicationCitationId) {
                    if ($priorApplicationCitationId != '') {
                        if (!in_array($applicationcitation, $applicationcitations))
                            $applicationcitations[] = $applicationcitation;
                    }
                    $applicationcitation= array();
                    $priorApplicationCitationId = $thisApplicationCitationId;
                }
                $applicationcitation[$apiField] = $val;
            }
        }

        $priorPatentId = $thisPatentId;
    }

    if ($hasInventor) {
        // Only add the inventor to the inventor list if it's not already in there
        if (!in_array($inventor, $inventors)) {
            $inventors[] = $inventor;
        }
        $patent['inventors'] = $inventors;
    }
    if ($hasAssignee) {
        // Only add the assignee to the assignee list if it's not already in there
        if (!in_array($assignee, $assignees)) {
            $assignees[] = $assignee;
        }
        $patent['assignees'] = $assignees;
    }
    if ($hasIPC) {
        // Only add the IPC to the IPC list if it's not already in there
        if (!in_array($IPC, $IPCs)) {
            $IPCs[] = $IPC;
        }
        $patent['IPCs'] = $IPCs;
    }
    if ($hasApplicationCitation) {
        if (!in_array($applicationcitation, $applicationcitations)) {
            $applicationcitations[] = $applicationcitation;
        }
        $patent['ApplicationCitations'] = $applicationcitations;
    }

    $patents[] = $patent;

    return array('patents' => $patents, 'count' => count($patents));
}