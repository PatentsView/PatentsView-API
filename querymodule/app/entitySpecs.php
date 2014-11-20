<?php
require_once dirname(__FILE__) . '/ErrorHandler.php';

$PATENT_ENTITY_SPECS = array(
    array('entity_name'=>'patent', 'group_name'=>'patents', 'keyId'=>'patent_id', 'table'=>'patent', 'join'=>'patent'),
    array('entity_name'=>'inventor', 'group_name'=>'inventors', 'keyId'=>'inventor_id', 'table'=>'inventor', 'join'=>'left outer JOIN patent_inventor ON patent.patent_id=patent_inventor.patent_id left outer JOIN inventor ON patent_inventor.inventor_id=inventor.inventor_id left outer join inventor_location ON inventor.inventor_id=inventor_location.inventor_id'),
    array('entity_name'=>'assignee', 'group_name'=>'assignees', 'keyId'=>'assignee_id', 'table'=>'assignee', 'join'=>'left outer join patent_assignee on patent.patent_id=patent_assignee.patent_id left outer join assignee ON patent_assignee.assignee_id=assignee.assignee_id left outer JOIN assignee_location on assignee.assignee_id=assignee_location.assignee_id'),
    array('entity_name'=>'application', 'group_name'=>'applications', 'keyId'=>'app_id', 'table'=>'application', 'join'=>'left outer join application on patent.patent_id=application.patent_id'),
    array('entity_name'=>'ipc', 'group_name'=>'IPCs', 'keyId'=>'', 'table'=>'ipcr', 'join'=>'left outer join ipcr on patent.patent_id=ipcr.patent_id'),
    array('entity_name'=>'applicationcitation', 'group_name'=>'application_citations', 'keyId'=>'', 'table'=>'usapplicationcitation', 'join'=>'left outer join usapplicationcitation on patent.patent_id=usapplicationcitation.patent_id'),
    array('entity_name'=>'cited_patent', 'group_name'=>'cited_patents', 'keyId'=>'', 'table'=>'uspatentcitation', 'join'=>'left outer join uspatentcitation on patent.patent_id=uspatentcitation.citing_patent_id'),
    //Todo: Figure out difference between uspatentcitation and uscitedpatent and if both are needed.
    array('entity_name'=>'citedby_patent', 'group_name'=>'citedby_patents', 'keyId'=>'', 'table'=>'uscitedpatent', 'join'=>'left outer join uscitedpatent as uscitedpatent on patent.patent_id=uscitedpatent.cited_patent_id'),
    //Todo: Do we need both the uspc and uspc_current?
    array('entity_name'=>'uspc', 'group_name'=>'uspcs', 'keyId'=>'uspc_id', 'table'=>'uspc_current', 'join'=>'left outer join uspc_current on patent.patent_id=uspc_current.patent_id')
);

// These could be stored in a database instead of hardcoded here.
$PATENT_FIELD_SPECS = array
(
    'patent_id' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'patent_id', 'datatype' => 'string'),
    'inventor_id' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'inventor_id', 'datatype' => 'string'),
    'assignee_id' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'assignee_id', 'datatype' => 'string'),
    'app_id' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'application_id', 'datatype' => 'string'),
    //'ipc_id' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => '', 'datatype' => 'string'),
    //'appcit_id' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    //'cited_patent_uuid' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    //'citedby_patent_uuid' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'uuid', 'datatype' => 'string'),
    'uspc_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'id', 'datatype' => 'string'),


    // If you need to replace these entries, you can copy paste from a table in MSWord and then run this regex replace:
    // ^(.+)\t(.+)\t(.+)$
    // \t'$1' => array('table' => '$2', 'field_name' => '$3', 'datatype' => 'string'),
    'app_country' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'country', 'datatype' => 'string'),
    'app_date' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'date', 'datatype' => 'date'),
    'app_number' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'number', 'datatype' => 'string'),
    'app_type' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'type', 'datatype' => 'string'),
    'appcit_category' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'appcit_date' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'date', 'datatype' => 'date'),
    'appcit_kind' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'appcit_name' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'appcit_sequence' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'sequence', 'datatype' => 'int'),
    'assignee_city' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'field_name' => 'city', 'datatype' => 'string'),
    'assignee_country' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'field_name' => 'country', 'datatype' => 'string'),
    'assignee_first_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'name_first', 'datatype' => 'string'),
    'assignee_lastknown_city' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'lastknown_city', 'datatype' => 'string'),
    'assignee_lastknown_country' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'lastknown_country', 'datatype' => 'string'),
    'assignee_lastknown_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'lastknown_latitude', 'datatype' => 'float'),
    'assignee_lastknown_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'lastknown_longitude', 'datatype' => 'float'),
    'assignee_lastknown_state' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'lastknown_state', 'datatype' => 'string'),
    'assignee_last_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'name_last', 'datatype' => 'string'),
    'assignee_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'field_name' => 'latitude', 'datatype' => 'float'),
    'assignee_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'field_name' => 'longitude', 'datatype' => 'float'),
    'assignee_num_patents' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'num_patents', 'datatype' => 'int'),
    'assignee_organization' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'organization', 'datatype' => 'string'),
    'assignee_state' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'field_name' => 'state', 'datatype' => 'string'),
    'assignee_type' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'type', 'datatype' => 'string'),
    //Todo: assignee years active
    //'assignee_years_active' => array('entity_name'=>'assignee', 'table' => 'assignee', 'field_name' => 'years_active', 'datatype' => 'int'),
    'citedby_patent_category' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'category', 'datatype' => 'string'),
    'citedby_patent_date' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'date', 'datatype' => 'date'),
    'citedby_patent_kind' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'kind', 'datatype' => 'string'),
    'citedby_patent_name' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'name', 'datatype' => 'string'),
    'citedby_patent_number' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'citing_patent_id', 'datatype' => 'string'),
    //Todo: What happened to citedby sequence?
    //'citedby_patent_sequence' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'sequence', 'datatype' => 'int'),
    'citedby_patent_title' => array('entity_name'=>'citedby_patent', 'table' => 'uscitedpatent', 'field_name' => 'title', 'datatype' => 'string'),
    'cited_patent_category' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'cited_patent_date' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'date', 'datatype' => 'date'),
    'cited_patent_kind' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'cited_patent_name' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'cited_patent_number' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'cited_patent_id', 'datatype' => 'string'),
    'cited_patent_sequence' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'sequence', 'datatype' => 'int'),
    'inventor_city' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'field_name' => 'city', 'datatype' => 'string'),
    'inventor_country' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'field_name' => 'country', 'datatype' => 'string'),
    'inventor_first_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'name_first', 'datatype' => 'string'),
    'inventor_last_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'name_last', 'datatype' => 'string'),
    'inventor_lastknown_city' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'lastknown_city', 'datatype' => 'string'),
    'inventor_lastknown_country' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'lastknown_country', 'datatype' => 'string'),
    'inventor_lastknown_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'lastknown_latitude', 'datatype' => 'float'),
    'inventor_lastknown_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'lastknown_longitude', 'datatype' => 'float'),
    'inventor_lastknown_state' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'lastknown_state', 'datatype' => 'string'),
    'inventor_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'field_name' => 'latitude', 'datatype' => 'float'),
    'inventor_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'field_name' => 'longitude', 'datatype' => 'float'),
    'inventor_num_patents' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'num_patents', 'datatype' => 'int'),
    'inventor_state' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'field_name' => 'state', 'datatype' => 'string'),
    //Todo: inventor years active
    //'inventor_years_active' => array('entity_name'=>'inventor', 'table' => 'inventor', 'field_name' => 'years_active', 'datatype' => 'int'),
    'ipc_action_date' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'action_date', 'datatype' => 'date'),
    'ipc_classification_data_source' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'classification_data_source', 'datatype' => 'string'),
    'ipc_classification_value' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'classification_value', 'datatype' => 'string'),
    'ipc_main_group' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'main_group', 'datatype' => 'string'),
    //Todo: ipcr num inventors/assignees
    //'ipc_num_assignees' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'main_group', 'datatype' => 'string'),
    //'ipc_num_inventors' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'main_group', 'datatype' => 'string'),
    'ipc_section' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'section', 'datatype' => 'string'),
    'ipc_sequence' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'sequence', 'datatype' => 'int'),
    'ipc_subclass' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'subclass', 'datatype' => 'string'),
    'ipc_subgroup' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'subgroup', 'datatype' => 'string'),
    'ipc_symbol_position' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'symbol_position', 'datatype' => 'string'),
    'ipc_version_indicator' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'ipc_version_indicator', 'datatype' => 'string'),
    //Todo: ipcr years active
    'ipc_years_active' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'ipc_version_indicator', 'datatype' => 'string'),
    'patent_abstract' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'abstract', 'datatype' => 'fulltext'),
    'patent_date' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'date', 'datatype' => 'date'),
    'patent_firstnamed_city' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'firstnamed_city', 'datatype' => 'string'),
    'patent_firstnamed_country' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'firstnamed_country', 'datatype' => 'string'),
    'patent_firstnamed_latitude' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'firstnamed_latitude', 'datatype' => 'float'),
    'patent_firstnamed_longitude' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'firstnamed_longitude', 'datatype' => 'float'),
    'patent_firstnamed_state' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'firstnamed_state', 'datatype' => 'string'),
    'patent_kind' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'kind', 'datatype' => 'string'),
    'patent_num_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'num_us_patents_cited', 'datatype' => 'int'),
    'patent_num_claims' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'num_claims', 'datatype' => 'int'),
    'patent_number' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'number', 'datatype' => 'string'),
    'patent_title' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'title', 'datatype' => 'fulltext'),
    'patent_type' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'type', 'datatype' => 'string'),
    'uspc_mainclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'mainclass_id', 'datatype' => 'string'),
    'uspc_mainclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'mainclass_title', 'datatype' => 'string'),
    'uspc_num_assignees' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'num_assignees', 'datatype' => 'int'),
    'uspc_num_inventors' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'num_inventors', 'datatype' => 'int'),
    'uspc_num_patents' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'num_patents', 'datatype' => 'int'),
    //Todo: What happened to uspc sequence?
    //'uspc_sequence' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'sequence', 'datatype' => 'int'),
    'uspc_subclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'subclass_id', 'datatype' => 'string'),
    'uspc_subclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'subclass_title', 'datatype' => 'string')
    //Todo: uspc years active
    //'uspc_years_active' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'field_name' => 'subclass_title', 'datatype' => 'string')

);

$INVENTOR_ENTITY_SPECS = array(
    array('entity_name'=>'inventor', 'group_name'=>'inventors', 'keyId'=>'inventor_id', 'table'=>'inventor_flat', 'join'=>'inventor_flat left outer join patent_inventor on inventor_flat.inventor_id=patent_inventor.inventor_id'),
    array('entity_name'=>'patent', 'group_name'=>'patents', 'keyId'=>'patent_id', 'table'=>'patent', 'join'=>'left outer join patent on patent_inventor.patent_id=patent.id'),
    array('entity_name'=>'assignee', 'group_name'=>'assignees', 'keyId'=>'assignee_id', 'table'=>'assignee_flat', 'join'=>'left outer join patent_assignee on patent_inventor.patent_id=patent_assignee.patent_id left outer join assignee_flat on patent_assignee.assignee_id=assignee_flat.assignee_id'),
    array('entity_name'=>'application', 'group_name'=>'applications', 'keyId'=>'app_id', 'table'=>'application', 'join'=>'left outer join application on patent_inventor.patent_id=application.patent_id'),
    array('entity_name'=>'ipc', 'group_name'=>'IPCs', 'keyId'=>'ipc_id', 'table'=>'ipcr', 'join'=>'left outer join ipcr on patent_inventor.patent_id=ipcr.patent_id'),
    array('entity_name'=>'applicationcitation', 'group_name'=>'application_citations', 'keyId'=>'appcit_id', 'table'=>'usapplicationcitation', 'join'=>'left outer join usapplicationcitation on patent_inventor.patent_id=usapplicationcitation.patent_id'),
    array('entity_name'=>'cited_patent', 'group_name'=>'cited_patents', 'keyId'=>'cited_patent_id', 'table'=>'uspatentcitation', 'join'=>'left outer join uspatentcitation on patent_inventor.patent_idid=uspatentcitation.patent_id'),
    //Todo: When the new "cited by" table is ready, this needs to change.
    array('entity_name'=>'citedby_patent', 'group_name'=>'citedby_patents', 'keyId'=>'citedby_patent_uuid', 'table'=>'uspatentcitation2', 'join'=>'left outer join uspatentcitation as uspatentcitation2 on patent.id=uspatentcitation2.citation_id'),
    array('entity_name'=>'uspc', 'group_name'=>'uspcs', 'keyId'=>'uspc_id', 'table'=>'uspc_flat', 'join'=>'left outer join uspc_flat on patent_inventor.patent_id=uspc_flat.uspc_patent_id')
);

// These could be stored in a database instead of hardcoded here.
$INVENTOR_FIELD_SPECS = array
(
    'inventor_id' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'inventor_id', 'datatype' => 'string'),
    'patent_id' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'id', 'datatype' => 'string'),
    'assignee_id' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'assignee_id', 'datatype' => 'string'),
    'app_id' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'id', 'datatype' => 'string'),
    'ipc_id' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'uuid', 'datatype' => 'string'),
    'appcit_id' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    'cited_patent_id' => array('entity_name'=>'cited_patent', 'table' => 'uspatentcitation', 'field_name' => 'uuid', 'datatype' => 'string'),
    //Todo: When the new "cited by" table is ready, this needs to change.
    'citedby_patent_uuid' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'uuid', 'datatype' => 'string'),
    'uspc_id' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'uspc_id', 'datatype' => 'string'),


    // If you need to replace these entries, you can copy paste from a table in MSWord and then run this regex replace:
    // ^(.+)\t(.+)\t(.+)$
    // \t'$1' => array('table' => '$2', 'field_name' => '$3', 'datatype' => 'string'),
    'app_country' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'country', 'datatype' => 'string'),
    'app_date' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'date', 'datatype' => 'date'),
    'app_number' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'number', 'datatype' => 'string'),
    'app_type' => array('entity_name'=>'application', 'table' => 'application', 'field_name' => 'type', 'datatype' => 'string'),
    'appcit_category' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'appcit_date' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'date', 'datatype' => 'date'),
    'appcit_kind' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'appcit_name' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'appcit_sequence' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'field_name' => 'sequence', 'datatype' => 'int'),
    'assignee_city' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'city', 'datatype' => 'string'),
    'assignee_country' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'country', 'datatype' => 'string'),
    'assignee_country_of_residence' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'residence', 'datatype' => 'string'),
    'assignee_first_name' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'name_first', 'datatype' => 'string'),
    'assignee_last_name' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
    'assignee_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'latitude', 'datatype' => 'float'),
    'assignee_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'longitude', 'datatype' => 'float'),
    'assignee_nationality' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'nationality', 'datatype' => 'string'),
    'assignee_organization' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'organization', 'datatype' => 'string'),
    'assignee_state' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'state', 'datatype' => 'string'),
    'assignee_type' => array('entity_name'=>'assignee', 'table' => 'assignee_flat', 'field_name' => 'type', 'datatype' => 'string'),
    'inventor_city' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'city', 'datatype' => 'string'),
    'inventor_country' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'country', 'datatype' => 'string'),
    'inventor_first_name' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'name_first', 'datatype' => 'string'),
    'inventor_last_name' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'name_last', 'datatype' => 'string'),
    'inventor_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'latitude', 'datatype' => 'float'),
    'inventor_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'longitude', 'datatype' => 'float'),
    'inventor_state' => array('entity_name'=>'inventor', 'table' => 'inventor_flat', 'field_name' => 'state', 'datatype' => 'string'),
    'ipc_action_date' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'action_date', 'datatype' => 'date'),
    'ipc_classification_data_source' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'classification_data_source', 'datatype' => 'string'),
    'ipc_classification_value' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'classification_value', 'datatype' => 'string'),
    'ipc_main_group' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'main_group', 'datatype' => 'string'),
    'ipc_section' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'section', 'datatype' => 'string'),
    'ipc_sequence' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'sequence', 'datatype' => 'int'),
    'ipc_subclass' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'subclass', 'datatype' => 'string'),
    'ipc_subgroup' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'subgroup', 'datatype' => 'string'),
    'ipc_symbol_position' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'symbol_position', 'datatype' => 'string'),
    'ipc_version_indicator' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'field_name' => 'ipc_version_indicator', 'datatype' => 'string'),
    'patent_abstract' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'abstract', 'datatype' => 'fulltext'),
    'patent_country' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'country', 'datatype' => 'string'),
    'patent_date' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'date', 'datatype' => 'date'),
    'patent_kind' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'Kind', 'datatype' => 'string'),
    'patent_num_claims' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'num_claims', 'datatype' => 'int'),
    'patent_number' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'number', 'datatype' => 'string'),
    'patent_title' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'title', 'datatype' => 'fulltext'),
    'patent_type' => array('entity_name'=>'patent', 'table' => 'patent', 'field_name' => 'type', 'datatype' => 'string'),
    'cited_patent_category' => array('entity_name'=>'patentcitation', 'table' => 'uspatentcitation', 'field_name' => 'category', 'datatype' => 'string'),
    'cited_patent_date' => array('entity_name'=>'patentcitation', 'table' => 'uspatentcitation', 'field_name' => 'date', 'datatype' => 'date'),
    'cited_patent_kind' => array('entity_name'=>'patentcitation', 'table' => 'uspatentcitation', 'field_name' => 'kind', 'datatype' => 'string'),
    'cited_patent_name' => array('entity_name'=>'patentcitation', 'table' => 'uspatentcitation', 'field_name' => 'name', 'datatype' => 'string'),
    'cited_patent_sequence' => array('entity_name'=>'patentcitation', 'table' => 'uspatentcitation', 'field_name' => 'sequence', 'datatype' => 'int'),
    //Todo: When the new "cited by" table is ready, these need to change.
    'citedby_patent_category' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'category', 'datatype' => 'string'),
    'citedby_patent_date' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'date', 'datatype' => 'date'),
    'citedby_patent_id' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'patent_id', 'datatype' => 'string'),
    'citedby_patent_kind' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'kind', 'datatype' => 'string'),
    'citedby_patent_name' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'name', 'datatype' => 'string'),
    'citedby_patent_sequence' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'sequence', 'datatype' => 'int'),
    //Todo: This is not using the right field - need title added to the citedby table
    'citedby_patent_title' => array('entity_name'=>'citedby_patent', 'table' => 'uspatentcitation2', 'field_name' => 'name', 'datatype' => 'string'),
    'uspc_mainclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'mainclass_id', 'datatype' => 'string'),
    'uspc_mainclass_text' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'mainclass_text', 'datatype' => 'string'),
    'uspc_mainclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'mainclass_title', 'datatype' => 'string'),
    'uspc_sequence' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'sequence', 'datatype' => 'int'),
    'uspc_subclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'subclass_id', 'datatype' => 'string'),
    'uspc_subclass_text' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'subclass_text', 'datatype' => 'string'),
    'uspc_subclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_flat', 'field_name' => 'subclass_title', 'datatype' => 'string')

);


function getDBField(array $fieldSpecs, $apiFieldName)
{
    if (!array_key_exists($apiFieldName, $fieldSpecs)) {
        ErrorHandler::getHandler()->sendError(400, "Field name is invalid: $apiFieldName.",
            "Field name not in FIELD_SPECS: $apiFieldName.");
    }
    return $fieldSpecs[$apiFieldName]['table'] . '.' . $fieldSpecs[$apiFieldName]['field_name'];
}