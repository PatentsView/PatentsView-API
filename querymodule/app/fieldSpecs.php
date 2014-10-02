<?php

// These could be stored in a database instead of hardcoded here.

$FIELD_SPECS = array
(
    'patent_id' => array('table_name' => 'patent', 'field_name' => 'id', 'datatype' => 'string'),
    'inventor_id' => array('table_name' => 'inventor_flat', 'field_name' => 'inventor_id', 'datatype' => 'string'),
    'assignee_id' => array('table_name' => 'assignee_flat', 'field_name' => 'assignee_id', 'datatype' => 'string'),
    'application_id' => array('table_name' => 'application', 'field_name' => 'id', 'datatype' => 'string'),
    'ipc_id' => array('table_name' => 'ipcr', 'field_name' => 'uuid', 'datatype' => 'string'),
    'applicationcitation_id' => array('table_name' => 'usapplicationcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    'patentcitation_id' => array('table_name' => 'uspatentcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    'uspc_id' => array('table_name' => 'uspc_flat', 'field_name' => 'uspc_id', 'datatype' => 'string'),


    // If you need to replace these entries, you can copy paste from a table in MSWord and then run this regex replace:
    // ^(.+)\t(.+)\t(.+)$
    // \t'$1' => array('table_name' => '$2', 'field_name' => '$3', 'datatype' => 'string'),
    'application_country' => array('table_name' => 'application', 'field_name' => 'country', 'datatype' => 'string'),
    'application_date' => array('table_name' => 'application', 'field_name' => 'date', 'datatype' => 'string'),
    'application_number' => array('table_name' => 'application', 'field_name' => 'number', 'datatype' => 'string'),
    'application_type' => array('table_name' => 'application', 'field_name' => 'type', 'datatype' => 'string'),
    'applicationcitation_category' => array('table_name' => 'usapplicationcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'applicationcitation_date' => array('table_name' => 'usapplicationcitation', 'field_name' => 'date', 'datatype' => 'string'),
    'applicationcitation_kind' => array('table_name' => 'usapplicationcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'applicationcitation_name' => array('table_name' => 'usapplicationcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'applicationcitation_sequence' => array('table_name' => 'usapplicationcitation', 'field_name' => 'sequence', 'datatype' => 'string'),
    'assignee_city' => array('table_name' => 'assignee_flat', 'field_name' => 'city', 'datatype' => 'string'),
    'assignee_country' => array('table_name' => 'assignee_flat', 'field_name' => 'country', 'datatype' => 'string'),
    'assignee_country_of_residence' => array('table_name' => 'assignee_flat', 'field_name' => 'residence', 'datatype' => 'string'),
    'assignee_first_name' => array('table_name' => 'assignee_flat', 'field_name' => 'name_first', 'datatype' => 'string'),
    'assignee_last_name' => array('table_name' => 'assignee_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
    'assignee_nationality' => array('table_name' => 'assignee_flat', 'field_name' => 'nationality', 'datatype' => 'string'),
    'assignee_organization' => array('table_name' => 'assignee_flat', 'field_name' => 'organization', 'datatype' => 'string'),
    'assignee_state' => array('table_name' => 'assignee_flat', 'field_name' => 'state', 'datatype' => 'string'),
    'assignee_type' => array('table_name' => 'assignee_flat', 'field_name' => 'type', 'datatype' => 'string'),
    'inventor_city' => array('table_name' => 'inventor_flat', 'field_name' => 'city', 'datatype' => 'string'),
    'inventor_country' => array('table_name' => 'inventor_flat', 'field_name' => 'country', 'datatype' => 'string'),
    'inventor_first_name' => array('table_name' => 'inventor_flat', 'field_name' => 'name_first', 'datatype' => 'string'),
    'inventor_last_name' => array('table_name' => 'inventor_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
//    'inventor_nationality' => array('table_name' => 'inventor_flat', 'field_name' => 'inventor_nationality', 'datatype' => 'string'),
    'inventor_state' => array('table_name' => 'inventor_flat', 'field_name' => 'state', 'datatype' => 'string'),
    'ipc_action_date' => array('table_name' => 'ipcr', 'field_name' => 'action_date', 'datatype' => 'string'),
    'ipc_classification_data_source' => array('table_name' => 'ipcr', 'field_name' => 'classification_data_source', 'datatype' => 'string'),
    'ipc_classification_value' => array('table_name' => 'ipcr', 'field_name' => 'classification_value', 'datatype' => 'string'),
    'ipc_main_group' => array('table_name' => 'ipcr', 'field_name' => 'main_group', 'datatype' => 'string'),
    'ipc_section' => array('table_name' => 'ipcr', 'field_name' => 'section', 'datatype' => 'string'),
    'ipc_sequence' => array('table_name' => 'ipcr', 'field_name' => 'sequence', 'datatype' => 'string'),
    'ipc_subclass' => array('table_name' => 'ipcr', 'field_name' => 'subclass', 'datatype' => 'string'),
    'ipc_subgroup' => array('table_name' => 'ipcr', 'field_name' => 'subgroup', 'datatype' => 'string'),
    'ipc_symbol_position' => array('table_name' => 'ipcr', 'field_name' => 'symbol_position', 'datatype' => 'string'),
    'ipc_version_indicator' => array('table_name' => 'ipcr', 'field_name' => 'ipc_version_indicator', 'datatype' => 'string'),
    'patent_abstract' => array('table_name' => 'patent', 'field_name' => 'abstract', 'datatype' => 'string'),
    'patent_country' => array('table_name' => 'patent', 'field_name' => 'country', 'datatype' => 'string'),
    'patent_date' => array('table_name' => 'patent', 'field_name' => 'date', 'datatype' => 'string'),
    'patent_kind' => array('table_name' => 'patent', 'field_name' => 'Kind', 'datatype' => 'string'),
    'patent_num_claims' => array('table_name' => 'patent', 'field_name' => 'num_claims', 'datatype' => 'string'),
    'patent_number' => array('table_name' => 'patent', 'field_name' => 'number', 'datatype' => 'string'),
    'patent_title' => array('table_name' => 'patent', 'field_name' => 'title', 'datatype' => 'string'),
    'patent_type' => array('table_name' => 'patent', 'field_name' => 'type', 'datatype' => 'string'),
    'patentcitation_category' => array('table_name' => 'uspatentcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'patentcitation_date' => array('table_name' => 'uspatentcitation', 'field_name' => 'date', 'datatype' => 'string'),
    'patentcitation_kind' => array('table_name' => 'uspatentcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'patentcitation_name' => array('table_name' => 'uspatentcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'patentcitation_sequence' => array('table_name' => 'uspatentcitation', 'field_name' => 'sequence', 'datatype' => 'string'),
    'uspc_mainclass_id' => array('table_name' => 'uspc_flat', 'field_name' => 'mainclass_id', 'datatype' => 'string'),
    'uspc_mainclass_text' => array('table_name' => 'uspc_flat', 'field_name' => 'mainclass_text', 'datatype' => 'string'),
    'uspc_mainclass_title' => array('table_name' => 'uspc_flat', 'field_name' => 'mainclass_title', 'datatype' => 'string'),
    'uspc_sequence' => array('table_name' => 'uspc_flat', 'field_name' => 'sequence', 'datatype' => 'string'),
    'uspc_subclass_id' => array('table_name' => 'uspc_flat', 'field_name' => 'subclass_id', 'datatype' => 'string'),
    'uspc_subclass_text' => array('table_name' => 'uspc_flat', 'field_name' => 'subclass_text', 'datatype' => 'string'),
    'uspc_subclass_title' => array('table_name' => 'uspc_flat', 'field_name' => 'subclass_title', 'datatype' => 'string')

);


function getDBField($apiFieldName)
{
    global $FIELD_SPECS;
    try {
        return $FIELD_SPECS[$apiFieldName]['table_name'] . '.' . $FIELD_SPECS[$apiFieldName]['field_name'];
    } catch (Exception $e) {
        // TODO Need to figure out what to do with these exceptions. For the API, it should return as a HTTP error code with a message.
        return 'FIELD_NOT_FOUND';
    }
}