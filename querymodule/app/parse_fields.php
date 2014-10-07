<?php

require_once dirname(__FILE__) . '/entitySpecs.php';

function parseFieldList(array $fieldsParam=null)
{
    global $FIELD_SPECS;
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        $returnFieldSpecs[$fieldsParam[$i]] = $FIELD_SPECS[$fieldsParam[$i]];
    }

    return $returnFieldSpecs;
}