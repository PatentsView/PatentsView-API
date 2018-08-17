<?php
require_once dirname(__FILE__) . '/ErrorHandler.php';

function getDBField(array $fieldSpecs, $apiFieldName, $columnFor = "column_name")
{
    if (!array_key_exists($apiFieldName, $fieldSpecs)) {
        ErrorHandler::getHandler()->sendError(400, "Field name is invalid: $apiFieldName.",
            "Field name not in FIELD_SPECS: $apiFieldName.");
    }
    return $fieldSpecs[$apiFieldName][$columnFor];
}

/*
 * ENTITY_SPECS: defines the entities we are dealing with. The first one is the primary entity; the others are
 *              subentities.
 * entity_name: name for this entity type. Used internal for an individual entity. Used externally as the name for
 *              individual units of this entity
 * group_name:  plural name for the group. Used internal for aggregating into groups and for external output.
 * keyId:       The field in the FIELD_SPECS that is a unique identifier. Could be blank for those entity types
 *              that are not really considered individual entities, but are more of a list of grouped attributes
 * join:        The SQL join statement that will bring in the data for this entity type when connected with the
 *              primary entity's JOIN statement. Note: these must be able to be used in aggregate with all the
 *              other joins in the ENTITY_SPECS, so any duplicate joins to the same table must use a unique
 *              alias for that table.
 * default_fields:  Only needed for the primary entity. Lists the fields that will be included in the results
 *              if API call does not include an explicit list of fields to include.
*/
$patent_entity_json = file_get_contents(dirname(__FILE__) . '/specs/patent-entity-specs.json');
$PATENT_ENTITY_SPECS = json_decode($patent_entity_json, true);
$patent_field_json = file_get_contents(dirname(__FILE__) . '/specs/patent-field-specs.json');
$PATENT_FIELD_SPECS = json_decode($patent_field_json, true);

$inventor_entity_json = file_get_contents(dirname(__FILE__) . '/specs/inventor-entity-specs.json');
$INVENTOR_ENTITY_SPECS = json_decode($inventor_entity_json, true);
$inventor_field_json = file_get_contents(dirname(__FILE__) . '/specs/inventor-field-specs.json');
$INVENTOR_FIELD_SPECS = json_decode($inventor_field_json, true);


$assignee_entity_json = file_get_contents(dirname(__FILE__) . '/specs/assignee-entity-specs.json');
$ASSIGNEE_ENTITY_SPECS = json_decode($assignee_entity_json, true);
$assignee_field_json = file_get_contents(dirname(__FILE__) . '/specs/assignee-field-specs.json');
$ASSIGNEE_FIELD_SPECS = json_decode($assignee_field_json, true);


$cpcgroup_entity_json = file_get_contents(dirname(__FILE__) . '/specs/cpc-group-entity-specs.json');
$CPC_GROUP_ENTITY_SPECS = json_decode($cpcgroup_entity_json, true);
$cpcgroup_field_json = file_get_contents(dirname(__FILE__) . '/specs/cpc-group-field-specs.json');
$CPC_GROUP_FIELD_SPECS = json_decode($cpcgroup_field_json, true);


$cpc_entity_json = file_get_contents(dirname(__FILE__) . '/specs/cpc-entity-specs.json');
$CPC_ENTITY_SPECS = json_decode($cpc_entity_json, true);
$cpc_field_json = file_get_contents(dirname(__FILE__) . '/specs/cpc-field-specs.json');
$CPC_FIELD_SPECS = json_decode($cpc_field_json, true);


$uspc_entity_json = file_get_contents(dirname(__FILE__) . '/specs/uspc-entity-specs.json');
$USPC_ENTITY_SPECS = json_decode($uspc_entity_json, true);
$uspc_field_json = file_get_contents(dirname(__FILE__) . '/specs/uspc-field-specs.json');
$USPC_FIELD_SPECS = json_decode($uspc_field_json, true);


$nber_entity_json = file_get_contents(dirname(__FILE__) . '/specs/nber-entity-specs.json');
$NBER_ENTITY_SPECS = json_decode($nber_entity_json, true);
$nber_field_json = file_get_contents(dirname(__FILE__) . '/specs/nber-field-specs.json');
$NBER_FIELD_SPECS = json_decode($nber_field_json, true);


$location_entity_json = file_get_contents(dirname(__FILE__) . '/specs/location-entity-specs.json');
$LOCATION_ENTITY_SPECS = json_decode($location_entity_json, true);
$location_field_json = file_get_contents(dirname(__FILE__) . '/specs/location-field-specs.json');
$LOCATION_FIELD_SPECS = json_decode($location_field_json, true);
