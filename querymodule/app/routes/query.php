<?php
require_once dirname(__FILE__) . '/../execute_query.php';
require_once dirname(__FILE__) . '/../ErrorHandler.php';
require_once dirname(__FILE__) . '/../entitySpecs.php';

// query/q=<query in json format>[&f=<field in json format>][&o=<options in json format>]
$app->get(
    '/patents/query',
    function () use ($app) {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($app);

        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = FormatResults($formatParam, $results);
        $app->response->setBody($results);
    }
);


$app->post(
    '/patents/query',
    function () use ($app) {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($app);

        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = FormatResults($formatParam, $results);
        $app->response->setBody($results);
    }
);

$app->get(
    '/inventors/query',
    function () use ($app) {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($app);

        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = FormatResults($formatParam, $results);
        $app->response->setBody($results);
    }
);


$app->post(
    '/inventors/query',
    function () use ($app) {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($app);

        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = FormatResults($formatParam, $results);
        $app->response->setBody($results);
    }
);


$app->get(
    '/assignees/query',
    function () use ($app) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($app);

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = FormatResults($formatParam, $results);
        $app->response->setBody($results);
    }
);


$app->post(
    '/assignees/query',
    function () use ($app) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($app);

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = FormatResults($formatParam, $results);
        $app->response->setBody($results);
    }
);


function CheckGetParameters($app)
{
// Make sure the 'q' parameter exists.
    if ($app->request->get('q') == null) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: missing.", $app->request->get());
    }
    // Convert the query param to json, return error if empty or not valid
    $queryParam = json_decode($app->request->get('q'), true);
    if ($queryParam == null) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: not valid json.", $app->request->get());
    }
    // Ensure the query param only has one top-level object
    if (count($queryParam) != 1) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: should only have one json object in the top-level dictionary.", $app->request->get());
    }

    // Look for an "f" parameter; it may not exist.
    $fieldsParam = null;
    if ($app->request->get('f') != null) {
        $fieldsParam = json_decode($app->request->get('f'), true);
        if ($fieldsParam == null) {
            ErrorHandler::getHandler()->sendError(400, "'f' parameter: not valid json.", $app->request->get());
        }
    }

    // Look for an "s" parameter; it may not exist.
    $sortParam = null;
    if ($app->request->get('s') != null) {
        $sortParam = json_decode($app->request->get('s'), true);
        if ($sortParam == null) {
            ErrorHandler::getHandler()->sendError(400, "'s' parameter: not valid json.", $app->request->get());
        }
    }

    // Look for an "o" parameter; it may not exist.
    $optionsParam = null;
    if ($app->request->get('o') != null) {
        $optionsParam = json_decode($app->request->get('o'), true);
        if ($optionsParam == null) {
            ErrorHandler::getHandler()->sendError(400, "'o' parameter: not valid json.", $app->request->get());
        }
    }

    $formatParam = 'json';
    // Look for a "format" parameter; it may not exist.
    if ($app->request->get('format') != null) {
        if ($app->request->get('format') == 'json') {
            $formatParam = 'json';
            $app->contentType('application/json; charset=utf-8');
        }
        elseif ($app->request->get('format') == 'xml') {
            $formatParam = 'xml';
            $app->contentType('application/xml; charset=utf-8');
        }
        else
            ErrorHandler::getHandler()->sendError(400, "Invalid option for 'format' parameter: use either 'json' or 'xml'.", $app->request->get());
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam);
}

function CheckPostParameters($app)
{
    $body = $app->request->getBody();
    $bodyJSON = json_decode($body, true);
    if ($bodyJSON['q'] == null) {
        ErrorHandler::getHandler()->sendError(400, "Body does not contain valid JSON: $body", "Invalid JSON pass: $body");
    }
    //ErrorHandler::getHandler()->sendError(200, $bodyJSON);
    // Make sure the 'q' parameter exists.
    if ($bodyJSON['q'] == null) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: missing.", $app->request->get());
    }
    // Convert the query param to json, return error if empty or not valid
    $queryParam = $bodyJSON['q'];
    // Ensure the query param only has one top-level object
    if (count($queryParam) != 1) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: should only have one json object in the top-level dictionary.", $app->request->get());
    }

    // Look for an "f" parameter; it may not exist.
    $fieldsParam = null;
    if (array_key_exists('f', $bodyJSON))
        $fieldsParam = $bodyJSON['f'];

    $sortParam = null;
    // Look for an "s" parameter; it may not exist.
    if (array_key_exists('s', $bodyJSON))
        $sortParam = $bodyJSON['s'];

    $optionsParam = null;
    // Look for an "o" parameter; it may not exist.
    if (array_key_exists('o', $bodyJSON))
        $optionsParam = $bodyJSON['o'];

    $formatParam = 'json';
    // Look for a "format" parameter; it may not exist.
    if (array_key_exists('format', $bodyJSON)) {
        if ($bodyJSON['format'] == 'json') {
            $formatParam = 'json';
            $app->contentType('application/json; charset=utf-8');
        }
        elseif ($bodyJSON['format'] == 'xml') {
            $formatParam = 'xml';
            $app->contentType('application/xml; charset=utf-8');
        }
        else
            ErrorHandler::getHandler()->sendError(400, "Invalid option for 'format' parameter: use either 'json' or 'xml'.", $app->request->get());
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam);
}

function FormatResults($formatParam, $results)
{
    if ($formatParam == 'xml') {
        $xml = new SimpleXMLElement('<root/>');
        $results = array_to_xml($results, $xml)->asXML();
        return $results;
    } else
        $results = json_encode($results);return $results;
}

function array_to_xml(array $arr, SimpleXMLElement $xml) {
    foreach ($arr as $k => $v) {

        $attrArr = array();
        $kArray = explode(' ',$k);
        $tag = array_shift($kArray);

        if (count($kArray) > 0) {
            foreach($kArray as $attrValue) {
                $attrArr[] = explode('=',$attrValue);
            }
        }

        if (is_array($v)) {
            if (is_numeric($k)) {
                array_to_xml($v, $xml);
            } else {
                $child = $xml->addChild($tag);
                if (isset($attrArr)) {
                    foreach($attrArr as $attrArrV) {
                        $child->addAttribute($attrArrV[0],$attrArrV[1]);
                    }
                }
                array_to_xml($v, $child);
            }
        } else {
            $child = $xml->addChild($tag, $v);
            if (isset($attrArr)) {
                foreach($attrArr as $attrArrV) {
                    $child->addAttribute($attrArrV[0],$attrArrV[1]);
                }
            }
        }
    }

    return $xml;
}
