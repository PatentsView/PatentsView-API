<?php

require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';

function parseFieldList(array $fieldsParam=null)
{
    global $FIELD_SPECS;
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        try {
            $returnFieldSpecs[$fieldsParam[$i]] = $FIELD_SPECS[$fieldsParam[$i]];
        }
        catch (Exception $e) {
            ErrorHandler::getHandler()->sendError(400, 'Invalid field specified: ' . $fieldsParam[$i], $e);
        }
    }

    return $returnFieldSpecs;
}