<?php
require_once dirname(__FILE__) . '/ErrorHandler.php';

function getDBField(array $fieldSpecs, $apiFieldName)
{
    if (!array_key_exists($apiFieldName, $fieldSpecs)) {
        ErrorHandler::getHandler()->sendError(400, "Field name is invalid: $apiFieldName.",
            "Field name not in FIELD_SPECS: $apiFieldName.");
    }
    return $fieldSpecs[$apiFieldName]['table'] . '.' . $fieldSpecs[$apiFieldName]['column_name'];
}

/*
 * ENTITY_SPECS: defines the entities we are dealing with. The first one is the primary entity; the others are
 *              subentities.
 * entity_name: name for this entity type. Used internal for an individual entity. Used externally as the name for
 *              individual units of this entity
 * group_name:  plural name for the group. Used internal for aggregating into groups. Must end with an 's' due to a
 *              piece of code depending on that.
 * keyId:       The field in the FIELD_SPECS that is a unique identifier. Could be blank for those entity types
 *              that are not really considered individual entities, but are more of a list of grouped attributes
 * join:        The SQL join statement that will bring in the data for this entity type when connected with the
 *              primary entity's JOIN statement. Note: these must be able to be used in aggregate with all the
 *              other joins in the ENTITY_SPECS, so any duplicate joins to the same table must use a unique
 *              alias for that table.
*/

$PATENT_ENTITY_SPECS = array(
    array('entity_name'=>'patent', 'group_name'=>'patents', 'keyId'=>'patent_id', 'join'=>'patent'),
    array('entity_name'=>'inventor', 'group_name'=>'inventors', 'keyId'=>'inventor_id', 'join'=>'left outer JOIN patent_inventor ON patent.patent_id=patent_inventor.patent_id left outer JOIN inventor ON patent_inventor.inventor_id=inventor.inventor_id left outer join inventor_location ON inventor.inventor_id=inventor_location.inventor_id'),
    array('entity_name'=>'assignee', 'group_name'=>'assignees', 'keyId'=>'assignee_id', 'join'=>'left outer join patent_assignee on patent.patent_id=patent_assignee.patent_id left outer join assignee ON patent_assignee.assignee_id=assignee.assignee_id left outer JOIN assignee_location on assignee.assignee_id=assignee_location.assignee_id'),
    array('entity_name'=>'application', 'group_name'=>'applications', 'keyId'=>'app_id', 'join'=>'left outer join application on patent.patent_id=application.patent_id'),
    array('entity_name'=>'ipc', 'group_name'=>'IPCs', 'keyId'=>'', 'join'=>'left outer join ipcr on patent.patent_id=ipcr.patent_id'),
    array('entity_name'=>'applicationcitation', 'group_name'=>'application_citations', 'keyId'=>'', 'join'=>'left outer join usapplicationcitation on patent.patent_id=usapplicationcitation.citing_patent_id left outer join application appcit_app on usapplicationcitation.cited_application_id=appcit_app.application_id'),
    array('entity_name'=>'cited_patent', 'group_name'=>'cited_patents', 'keyId'=>'', 'join'=>'left outer join uspatentcitation as patentcit_fromciting_tocited on patent.patent_id=patentcit_fromciting_tocited.citing_patent_id left outer join patent as citedpatent on patentcit_fromciting_tocited.cited_patent_id=citedpatent.patent_id'),
    array('entity_name'=>'citedby_patent', 'group_name'=>'citedby_patents', 'keyId'=>'', 'join'=>'left outer join uspatentcitation as patentcit_fromcited_tociting on patent.patent_id=patentcit_fromcited_tociting.cited_patent_id left outer join patent as citingpatent on patentcit_fromcited_tociting.citing_patent_id=citingpatent.patent_id'),
    array('entity_name'=>'uspc', 'group_name'=>'uspcs', 'keyId'=>'uspc_id', 'join'=>'left outer join uspc_current on patent.patent_id=uspc_current.patent_id')
);

/*
 * FIELD_SPECS:     defines the fields available for an entity, in what table/column it can be found, and other
 *                  attributes of the field/column.
 * key:             the unique internal and external name for the field
 * entity_name:     the name of the parent entity; must match an 'entity_name' in the ENTITY_SPECS
 * table:           the DB table that contains this field. This table must be in the 'join' statement for the entity
 *                  type in the ENTITY_SPECS
 * column_name:     the SQL name of the column in table
 * datatype:        One of 'string', 'int', 'float', 'date', 'fulltext'
 * sort:            'y' or 'n' depending on if the primary entity can be sorted on this field. Only set to 'y' when
 *                  the field would be unique for that primary entity. If the field is part of a one-to-many, then
 *                  it must be 'n'; otherwise the return results would likely get mixed up.
 */

$PATENT_FIELD_SPECS = array
(
    'app_country' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'y'),
    'app_date' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'y'),
    'app_id' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'application_id', 'datatype' => 'string', 'sort' => 'Y'),
    'app_number' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'y'),
    'app_type' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'y'),
    'appcit_app_number' => array('entity_name'=>'applicationcitation', 'table' => 'appcit_app', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'appcit_category' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'column_name' => 'category', 'datatype' => 'string', 'sort' => 'n'),
    'appcit_date' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'appcit_kind' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'column_name' => 'kind', 'datatype' => 'string', 'sort' => 'n'),
    'appcit_name' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'column_name' => 'name', 'datatype' => 'string', 'sort' => 'n'),
    'appcit_sequence' => array('entity_name'=>'applicationcitation', 'table' => 'usapplicationcitation', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'assignee_city' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'city', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_country' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_first_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'name_first', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_id' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'assignee_id', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_lastknown_city' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_city', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_lastknown_country' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_country', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_lastknown_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_latitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_lastknown_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_longitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_lastknown_state' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_state', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_last_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'name_last', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'latitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'longitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_num_patents' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'assignee_organization' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'organization', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_state' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'state', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_type' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_years_active' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'citedby_patent_category' => array('entity_name'=>'citedby_patent', 'table' => 'patentcit_fromcited_tociting', 'column_name' => 'category', 'datatype' => 'string', 'sort' => 'n'),
    'citedby_patent_date' => array('entity_name'=>'citedby_patent', 'table' => 'citingpatent', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'citedby_patent_kind' => array('entity_name'=>'citedby_patent', 'table' => 'citingpatent', 'column_name' => 'kind', 'datatype' => 'string', 'sort' => 'n'),
    'citedby_patent_number' => array('entity_name'=>'citedby_patent', 'table' => 'citingpatent', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'citedby_patent_title' => array('entity_name'=>'citedby_patent', 'table' => 'citingpatent', 'column_name' => 'title', 'datatype' => 'string', 'sort' => 'n'),
    'cited_patent_category' => array('entity_name'=>'cited_patent', 'table' => 'patentcit_fromciting_tocited', 'column_name' => 'category', 'datatype' => 'string', 'sort' => 'n'),
    'cited_patent_date' => array('entity_name'=>'cited_patent', 'table' => 'citedpatent', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'cited_patent_kind' => array('entity_name'=>'cited_patent', 'table' => 'citedpatent', 'column_name' => 'kind', 'datatype' => 'string', 'sort' => 'n'),
    'cited_patent_number' => array('entity_name'=>'cited_patent', 'table' => 'citedpatent', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'cited_patent_sequence' => array('entity_name'=>'cited_patent', 'table' => 'patentcit_fromciting_tocited', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'cited_patent_title' => array('entity_name'=>'cited_patent', 'table' => 'citedpatent', 'column_name' => 'title', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_city' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'city', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_country' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_first_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'name_first', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_id' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'inventor_id', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_last_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'name_last', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_lastknown_city' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_city', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_lastknown_country' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_country', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_lastknown_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_latitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_lastknown_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_longitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_lastknown_state' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_state', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'latitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'longitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_num_patents' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'inventor_state' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'state', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_years_active' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_action_date' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'action_date', 'datatype' => 'date', 'sort' => 'n'),
    'ipc_class' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'ipc_class', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_classification_data_source' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'classification_data_source', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_classification_value' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'classification_value', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_main_group' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'main_group', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_num_assignees' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'num_assignees', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_num_inventors' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'num_inventors', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_section' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'section', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_sequence' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_subclass' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'subclass', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_subgroup' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'subgroup', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_symbol_position' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'symbol_position', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_version_indicator' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'ipc_version_indicator', 'datatype' => 'date', 'sort' => 'n'),
    'ipc_years_active' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'patent_abstract' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'abstract', 'datatype' => 'fulltext', 'sort' => 'n'),
    'patent_date' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'y'),
    'patent_firstnamed_city' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_city', 'datatype' => 'string', 'sort' => 'y'),
    'patent_firstnamed_country' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_country', 'datatype' => 'string', 'sort' => 'y'),
    'patent_firstnamed_latitude' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_latitude', 'datatype' => 'float', 'sort' => 'y'),
    'patent_firstnamed_longitude' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_longitude', 'datatype' => 'float', 'sort' => 'y'),
    'patent_firstnamed_state' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_state', 'datatype' => 'string', 'sort' => 'y'),
    'patent_id' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'patent_id', 'datatype' => 'string', 'sort' => 'y'),
    'patent_kind' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'kind', 'datatype' => 'string', 'sort' => 'y'),
    'patent_num_cited_by_us_patents' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_times_cited_by_us_patents', 'datatype' => 'int', 'sort' => 'y'),
    'patent_num_combined_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_total_documents_cited', 'datatype' => 'int', 'sort' => 'y'),
    'patent_num_foreign_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_foreign_documents_cited', 'datatype' => 'int', 'sort' => 'y'),
    'patent_num_us_application_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_us_applications_cited', 'datatype' => 'int', 'sort' => 'y'),
    'patent_num_us_patent_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_us_patents_cited', 'datatype' => 'int', 'sort' => 'y'),
    'patent_num_claims' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_claims', 'datatype' => 'int', 'sort' => 'y'),
    'patent_number' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'y'),
    'patent_title' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'title', 'datatype' => 'fulltext', 'sort' => 'y'),
    'patent_type' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'y'),
    'uspc_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_mainclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'mainclass_id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_mainclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'mainclass_title', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_num_assignees' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_assignees', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_num_inventors' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_inventors', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_num_patents' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_sequence' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_subclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'subclass_id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_subclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'subclass_title', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_years_active' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n')

);

$INVENTOR_ENTITY_SPECS = array(
    array('entity_name'=>'inventor', 'group_name'=>'inventors', 'keyId'=>'inventor_id', 'join'=>' inventor left outer JOIN inventor_location on inventor.inventor_id=inventor_location.inventor_id left outer join patent_inventor on inventor.inventor_id=patent_inventor.inventor_id'),
    array('entity_name'=>'patent', 'group_name'=>'patents', 'keyId'=>'patent_id', 'join'=>'left outer JOIN patent on patent_inventor.patent_id=patent.patent_id'),
    array('entity_name'=>'assignee', 'group_name'=>'assignees', 'keyId'=>'assignee_id', 'join'=>'left outer join patent_assignee on patent_inventor.patent_id=patent_assignee.patent_id left outer join assignee ON patent_assignee.assignee_id=assignee.assignee_id left outer JOIN assignee_location on assignee.assignee_id=assignee_location.assignee_id'),
    array('entity_name'=>'application', 'group_name'=>'applications', 'keyId'=>'app_id', 'join'=>'left outer join application on patent_inventor.patent_id=application.patent_id'),
    array('entity_name'=>'ipc', 'group_name'=>'IPCs', 'keyId'=>'', 'join'=>'left outer join ipcr on patent_inventor.patent_id=ipcr.patent_id'),
    array('entity_name'=>'uspc', 'group_name'=>'uspcs', 'keyId'=>'uspc_id', 'join'=>'left outer join uspc_current on patent_inventor.patent_id=uspc_current.patent_id')
);

$INVENTOR_FIELD_SPECS = array
(
    'app_country' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'app_date' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'app_id' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'application_id', 'datatype' => 'string', 'sort' => 'n'),
    'app_number' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'app_type' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_city' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'city', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_country' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_first_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'name_first', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_id' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'assignee_id', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_lastknown_city' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_city', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_lastknown_country' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_country', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_lastknown_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_latitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_lastknown_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_longitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_lastknown_state' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_state', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_last_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'name_last', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'latitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'longitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_num_patents' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'assignee_organization' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'organization', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_state' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'state', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_type' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_years_active' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'inventor_city' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'city', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_country' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_first_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'name_first', 'datatype' => 'string', 'sort' => 'y'),
    'inventor_id' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'inventor_id', 'datatype' => 'string', 'sort' => 'y'),
    'inventor_last_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'name_last', 'datatype' => 'string', 'sort' => 'y'),
    'inventor_lastknown_city' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_city', 'datatype' => 'string', 'sort' => 'y'),
    'inventor_lastknown_country' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_country', 'datatype' => 'string', 'sort' => 'y'),
    'inventor_lastknown_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_latitude', 'datatype' => 'float', 'sort' => 'y'),
    'inventor_lastknown_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_longitude', 'datatype' => 'float', 'sort' => 'y'),
    'inventor_lastknown_state' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_state', 'datatype' => 'string', 'sort' => 'y'),
    'inventor_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'latitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'longitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_num_patents' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'y'),
    'inventor_state' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'state', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_years_active' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'y'),
    'ipc_action_date' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'action_date', 'datatype' => 'date', 'sort' => 'n'),
    'ipc_class' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'ipc_class', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_classification_data_source' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'classification_data_source', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_classification_value' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'classification_value', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_main_group' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'main_group', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_num_assignees' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'num_assignees', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_num_inventors' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'num_inventors', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_section' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'section', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_sequence' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_subclass' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'subclass', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_subgroup' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'subgroup', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_symbol_position' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'symbol_position', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_version_indicator' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'ipc_version_indicator', 'datatype' => 'date', 'sort' => 'n'),
    'ipc_years_active' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'patent_abstract' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'abstract', 'datatype' => 'fulltext', 'sort' => 'n'),
    'patent_date' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'patent_firstnamed_city' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_city', 'datatype' => 'string', 'sort' => 'n'),
    'patent_firstnamed_country' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_country', 'datatype' => 'string', 'sort' => 'n'),
    'patent_firstnamed_latitude' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_latitude', 'datatype' => 'float', 'sort' => 'n'),
    'patent_firstnamed_longitude' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_longitude', 'datatype' => 'float', 'sort' => 'n'),
    'patent_firstnamed_state' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_state', 'datatype' => 'string', 'sort' => 'n'),
    'patent_id' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'patent_id', 'datatype' => 'string', 'sort' => 'n'),
    'patent_kind' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'kind', 'datatype' => 'string', 'sort' => 'n'),
    'patent_num_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_us_patents_cited', 'datatype' => 'int', 'sort' => 'n'),
    'patent_num_claims' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_claims', 'datatype' => 'int', 'sort' => 'n'),
    'patent_number' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'patent_title' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'title', 'datatype' => 'fulltext', 'sort' => 'n'),
    'patent_type' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_mainclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'mainclass_id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_mainclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'mainclass_title', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_num_assignees' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_assignees', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_num_inventors' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_inventors', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_num_patents' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_sequence' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_subclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'subclass_id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_subclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'subclass_title', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_years_active' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n')

);


$ASSIGNEE_ENTITY_SPECS = array(
    array('entity_name'=>'assignee', 'group_name'=>'assignees', 'keyId'=>'assignee_id', 'join'=>' assignee left outer JOIN assignee_location on assignee.assignee_id=assignee_location.assignee_id left outer join patent_assignee on assignee.assignee_id=patent_assignee.assignee_id'),
    array('entity_name'=>'patent', 'group_name'=>'patents', 'keyId'=>'patent_id', 'join'=>'left outer JOIN patent on patent_assignee.patent_id=patent.patent_id'),
    array('entity_name'=>'inventor', 'group_name'=>'inventors', 'keyId'=>'inventor_id', 'join'=>'left outer join patent_inventor on patent_assignee.patent_id=patent_inventor.patent_id left outer join inventor ON patent_inventor.inventor_id=inventor.inventor_id left outer JOIN inventor_location on inventor.inventor_id=inventor_location.inventor_id'),
    array('entity_name'=>'application', 'group_name'=>'applications', 'keyId'=>'app_id', 'join'=>'left outer join application on patent_assignee.patent_id=application.patent_id'),
    array('entity_name'=>'ipc', 'group_name'=>'IPCs', 'keyId'=>'', 'join'=>'left outer join ipcr on patent_assignee.patent_id=ipcr.patent_id'),
    array('entity_name'=>'uspc', 'group_name'=>'uspcs', 'keyId'=>'uspc_id', 'join'=>'left outer join uspc_current on patent_assignee.patent_id=uspc_current.patent_id')
);

$ASSIGNEE_FIELD_SPECS = array
(
    'app_country' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'app_date' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'app_id' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'application_id', 'datatype' => 'string', 'sort' => 'n'),
    'app_number' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'app_type' => array('entity_name'=>'application', 'table' => 'application', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_city' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'city', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_country' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_first_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'name_first', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_id' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'assignee_id', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_lastknown_city' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_city', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_lastknown_country' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_country', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_lastknown_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_latitude', 'datatype' => 'float', 'sort' => 'y'),
    'assignee_lastknown_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_longitude', 'datatype' => 'float', 'sort' => 'y'),
    'assignee_lastknown_state' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'lastknown_state', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_last_name' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'name_last', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_latitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'latitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_longitude' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'longitude', 'datatype' => 'float', 'sort' => 'n'),
    'assignee_num_patents' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'y'),
    'assignee_organization' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'organization', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_state' => array('entity_name'=>'assignee', 'table' => 'assignee_location', 'column_name' => 'state', 'datatype' => 'string', 'sort' => 'n'),
    'assignee_type' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'y'),
    'assignee_years_active' => array('entity_name'=>'assignee', 'table' => 'assignee', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'y'),
    'inventor_city' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'city', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_country' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'country', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_first_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'name_first', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_id' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'inventor_id', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_last_name' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'name_last', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_lastknown_city' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_city', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_lastknown_country' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_country', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_lastknown_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_latitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_lastknown_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_longitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_lastknown_state' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'lastknown_state', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_latitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'latitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_longitude' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'longitude', 'datatype' => 'float', 'sort' => 'n'),
    'inventor_num_patents' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'inventor_state' => array('entity_name'=>'inventor', 'table' => 'inventor_location', 'column_name' => 'state', 'datatype' => 'string', 'sort' => 'n'),
    'inventor_years_active' => array('entity_name'=>'inventor', 'table' => 'inventor', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_action_date' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'action_date', 'datatype' => 'date', 'sort' => 'n'),
    'ipc_class' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'ipc_class', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_classification_data_source' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'classification_data_source', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_classification_value' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'classification_value', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_main_group' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'main_group', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_num_assignees' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'num_assignees', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_num_inventors' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'num_inventors', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_section' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'section', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_sequence' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'ipc_subclass' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'subclass', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_subgroup' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'subgroup', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_symbol_position' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'symbol_position', 'datatype' => 'string', 'sort' => 'n'),
    'ipc_version_indicator' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'ipc_version_indicator', 'datatype' => 'date', 'sort' => 'n'),
    'ipc_years_active' => array('entity_name'=>'ipc', 'table' => 'ipcr', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n'),
    'patent_abstract' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'abstract', 'datatype' => 'fulltext', 'sort' => 'n'),
    'patent_date' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'date', 'datatype' => 'date', 'sort' => 'n'),
    'patent_firstnamed_city' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_city', 'datatype' => 'string', 'sort' => 'n'),
    'patent_firstnamed_country' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_country', 'datatype' => 'string', 'sort' => 'n'),
    'patent_firstnamed_latitude' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_latitude', 'datatype' => 'float', 'sort' => 'n'),
    'patent_firstnamed_longitude' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_longitude', 'datatype' => 'float', 'sort' => 'n'),
    'patent_firstnamed_state' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'firstnamed_state', 'datatype' => 'string', 'sort' => 'n'),
    'patent_id' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'patent_id', 'datatype' => 'string', 'sort' => 'n'),
    'patent_kind' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'kind', 'datatype' => 'string', 'sort' => 'n'),
    'patent_num_citations' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_us_patents_cited', 'datatype' => 'int', 'sort' => 'n'),
    'patent_num_claims' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'num_claims', 'datatype' => 'int', 'sort' => 'n'),
    'patent_number' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'number', 'datatype' => 'string', 'sort' => 'n'),
    'patent_title' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'title', 'datatype' => 'fulltext', 'sort' => 'n'),
    'patent_type' => array('entity_name'=>'patent', 'table' => 'patent', 'column_name' => 'type', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_mainclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'mainclass_id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_mainclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'mainclass_title', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_num_assignees' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_assignees', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_num_inventors' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_inventors', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_num_patents' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'num_patents', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_sequence' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'sequence', 'datatype' => 'int', 'sort' => 'n'),
    'uspc_subclass_id' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'subclass_id', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_subclass_title' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'subclass_title', 'datatype' => 'string', 'sort' => 'n'),
    'uspc_years_active' => array('entity_name'=>'uspc', 'table' => 'uspc_current', 'column_name' => 'years_active', 'datatype' => 'int', 'sort' => 'n')

);


