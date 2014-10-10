<?php
require_once dirname(__FILE__) . '/ErrorHandler.php';

$PATENT_ENTITY_SPECS = array(
    array('name'=>'patent', 'groupName'=>'patents', 'keyId'=>'patent_id', 'table'=>'patent', 'join'=>'patent'),
    array('name'=>'inventor', 'groupName'=>'inventors', 'keyId'=>'inventor_id', 'table'=>'inventor_flat', 'join'=>'left outer JOIN patent_inventor ON patent.id=patent_inventor.patent_id left outer JOIN inventor_flat ON patent_inventor.inventor_id=inventor_flat.inventor_id'),
    array('name'=>'assignee', 'groupName'=>'assignees', 'keyId'=>'assignee_id', 'table'=>'assignee_flat', 'join'=>'left outer join patent_assignee on patent.id=patent_assignee.patent_id left outer join assignee_flat on patent_assignee.assignee_id=assignee_flat.assignee_id'),
    array('name'=>'application', 'groupName'=>'applications', 'keyId'=>'application_id', 'table'=>'application', 'join'=>'left outer join application on patent.id=application.patent_id'),
    array('name'=>'ipc', 'groupName'=>'IPCs', 'keyId'=>'ipc_id', 'table'=>'ipcr', 'join'=>'left outer join ipcr on patent.id=ipcr.patent_id'),
    array('name'=>'applicationcitation', 'groupName'=>'application_citations', 'keyId'=>'applicationcitation_id', 'table'=>'usapplicationcitation', 'join'=>'left outer join usapplicationcitation on patent.id=usapplicationcitation.patent_id'),
    array('name'=>'patentcitation', 'groupName'=>'patent_citations', 'keyId'=>'patentcitation_id', 'table'=>'uspatentcitation', 'join'=>'left outer join uspatentcitation on patent.id=uspatentcitation.patent_id'),
    array('name'=>'uspc', 'groupName'=>'uspcs', 'keyId'=>'uspc_id', 'table'=>'uspc_flat', 'join'=>'left outer join uspc_flat on patent.id=uspc_flat.uspc_patent_id')
);

// These could be stored in a database instead of hardcoded here.
$FIELD_SPECS = array
(
    'patent_id' => array('table' => 'patent', 'field_name' => 'id', 'datatype' => 'string'),
    'inventor_id' => array('table' => 'inventor_flat', 'field_name' => 'inventor_id', 'datatype' => 'string'),
    'assignee_id' => array('table' => 'assignee_flat', 'field_name' => 'assignee_id', 'datatype' => 'string'),
    'application_id' => array('table' => 'application', 'field_name' => 'id', 'datatype' => 'string'),
    'ipc_id' => array('table' => 'ipcr', 'field_name' => 'uuid', 'datatype' => 'string'),
    'applicationcitation_id' => array('table' => 'usapplicationcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    'patentcitation_id' => array('table' => 'uspatentcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    'uspc_id' => array('table' => 'uspc_flat', 'field_name' => 'uspc_id', 'datatype' => 'string'),


    // If you need to replace these entries, you can copy paste from a table in MSWord and then run this regex replace:
    // ^(.+)\t(.+)\t(.+)$
    // \t'$1' => array('table' => '$2', 'field_name' => '$3', 'datatype' => 'string'),
    'application_country' => array('table' => 'application', 'field_name' => 'country', 'datatype' => 'string'),
    'application_date' => array('table' => 'application', 'field_name' => 'date', 'datatype' => 'date'),
    'application_number' => array('table' => 'application', 'field_name' => 'number', 'datatype' => 'string'),
    'application_type' => array('table' => 'application', 'field_name' => 'type', 'datatype' => 'string'),
    'applicationcitation_category' => array('table' => 'usapplicationcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'applicationcitation_date' => array('table' => 'usapplicationcitation', 'field_name' => 'date', 'datatype' => 'date'),
    'applicationcitation_kind' => array('table' => 'usapplicationcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'applicationcitation_name' => array('table' => 'usapplicationcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'applicationcitation_sequence' => array('table' => 'usapplicationcitation', 'field_name' => 'sequence', 'datatype' => 'int'),
    'assignee_city' => array('table' => 'assignee_flat', 'field_name' => 'city', 'datatype' => 'string'),
    'assignee_country' => array('table' => 'assignee_flat', 'field_name' => 'country', 'datatype' => 'string'),
    'assignee_country_of_residence' => array('table' => 'assignee_flat', 'field_name' => 'residence', 'datatype' => 'string'),
    'assignee_first_name' => array('table' => 'assignee_flat', 'field_name' => 'name_first', 'datatype' => 'string'),
    'assignee_last_name' => array('table' => 'assignee_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
    'assignee_latitude' => array('table' => 'assignee_flat', 'field_name' => 'latitude', 'datatype' => 'float'),
    'assignee_longitude' => array('table' => 'assignee_flat', 'field_name' => 'longitude', 'datatype' => 'float'),
    'assignee_last_name' => array('table' => 'assignee_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
    'assignee_nationality' => array('table' => 'assignee_flat', 'field_name' => 'nationality', 'datatype' => 'string'),
    'assignee_organization' => array('table' => 'assignee_flat', 'field_name' => 'organization', 'datatype' => 'string'),
    'assignee_state' => array('table' => 'assignee_flat', 'field_name' => 'state', 'datatype' => 'string'),
    'assignee_type' => array('table' => 'assignee_flat', 'field_name' => 'type', 'datatype' => 'string'),
    'inventor_city' => array('table' => 'inventor_flat', 'field_name' => 'city', 'datatype' => 'string'),
    'inventor_country' => array('table' => 'inventor_flat', 'field_name' => 'country', 'datatype' => 'string'),
    'inventor_first_name' => array('table' => 'inventor_flat', 'field_name' => 'name_first', 'datatype' => 'string'),
    'inventor_last_name' => array('table' => 'inventor_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
    'inventor_latitude' => array('table' => 'inventor_flat', 'field_name' => 'latitude', 'datatype' => 'float'),
    'inventor_longitude' => array('table' => 'inventor_flat', 'field_name' => 'longitude', 'datatype' => 'float'),
    'inventor_state' => array('table' => 'inventor_flat', 'field_name' => 'state', 'datatype' => 'string'),
    'ipc_action_date' => array('table' => 'ipcr', 'field_name' => 'action_date', 'datatype' => 'date'),
    'ipc_classification_data_source' => array('table' => 'ipcr', 'field_name' => 'classification_data_source', 'datatype' => 'string'),
    'ipc_classification_value' => array('table' => 'ipcr', 'field_name' => 'classification_value', 'datatype' => 'string'),
    'ipc_main_group' => array('table' => 'ipcr', 'field_name' => 'main_group', 'datatype' => 'string'),
    'ipc_section' => array('table' => 'ipcr', 'field_name' => 'section', 'datatype' => 'string'),
    'ipc_sequence' => array('table' => 'ipcr', 'field_name' => 'sequence', 'datatype' => 'int'),
    'ipc_subclass' => array('table' => 'ipcr', 'field_name' => 'subclass', 'datatype' => 'string'),
    'ipc_subgroup' => array('table' => 'ipcr', 'field_name' => 'subgroup', 'datatype' => 'string'),
    'ipc_symbol_position' => array('table' => 'ipcr', 'field_name' => 'symbol_position', 'datatype' => 'string'),
    'ipc_version_indicator' => array('table' => 'ipcr', 'field_name' => 'ipc_version_indicator', 'datatype' => 'string'),
    'patent_abstract' => array('table' => 'patent', 'field_name' => 'abstract', 'datatype' => 'fulltext'),
    'patent_country' => array('table' => 'patent', 'field_name' => 'country', 'datatype' => 'string'),
    'patent_date' => array('table' => 'patent', 'field_name' => 'date', 'datatype' => 'date'),
    'patent_kind' => array('table' => 'patent', 'field_name' => 'Kind', 'datatype' => 'string'),
    'patent_num_claims' => array('table' => 'patent', 'field_name' => 'num_claims', 'datatype' => 'int'),
    'patent_number' => array('table' => 'patent', 'field_name' => 'number', 'datatype' => 'string'),
    'patent_title' => array('table' => 'patent', 'field_name' => 'title', 'datatype' => 'fulltext'),
    'patent_type' => array('table' => 'patent', 'field_name' => 'type', 'datatype' => 'string'),
    'patentcitation_category' => array('table' => 'uspatentcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'patentcitation_date' => array('table' => 'uspatentcitation', 'field_name' => 'date', 'datatype' => 'date'),
    'patentcitation_kind' => array('table' => 'uspatentcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'patentcitation_name' => array('table' => 'uspatentcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'patentcitation_sequence' => array('table' => 'uspatentcitation', 'field_name' => 'sequence', 'datatype' => 'int'),
    'uspc_mainclass_id' => array('table' => 'uspc_flat', 'field_name' => 'mainclass_id', 'datatype' => 'string'),
    'uspc_mainclass_text' => array('table' => 'uspc_flat', 'field_name' => 'mainclass_text', 'datatype' => 'string'),
    'uspc_mainclass_title' => array('table' => 'uspc_flat', 'field_name' => 'mainclass_title', 'datatype' => 'string'),
    'uspc_sequence' => array('table' => 'uspc_flat', 'field_name' => 'sequence', 'datatype' => 'int'),
    'uspc_subclass_id' => array('table' => 'uspc_flat', 'field_name' => 'subclass_id', 'datatype' => 'string'),
    'uspc_subclass_text' => array('table' => 'uspc_flat', 'field_name' => 'subclass_text', 'datatype' => 'string'),
    'uspc_subclass_title' => array('table' => 'uspc_flat', 'field_name' => 'subclass_title', 'datatype' => 'string')

);


function getDBField($apiFieldName)
{
    global $FIELD_SPECS;
    if (!array_key_exists($apiFieldName, $FIELD_SPECS)) {
        ErrorHandler::getHandler()->sendError(400, "Field name is invalid: $apiFieldName.",
            "Field name not in FIELD_SPECS: $apiFieldName.");
    }
    return $FIELD_SPECS[$apiFieldName]['table'] . '.' . $FIELD_SPECS[$apiFieldName]['field_name'];
}