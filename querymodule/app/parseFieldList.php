<?php

require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';

function parseFieldList(array $fieldSpecs, array $fieldsParam=null)
{
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        try {
            $returnFieldSpecs[$fieldsParam[$i]] = $fieldSpecs[$fieldsParam[$i]];
        }
        catch (Exception $e) {
            ErrorHandler::getHandler()->sendError(400, 'Invalid field specified: ' . $fieldsParam[$i], $e);
        }
    }

    return $returnFieldSpecs;
}