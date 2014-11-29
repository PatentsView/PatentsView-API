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

        list($queryParam, $fieldsParam, $sortParam, $optionsParam) = CheckGetParameters($app);

        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = json_encode($results);

        $app->response->setBody($results);
    }
);


$app->post(
    '/patents/query',
    function () use ($app) {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam) = CheckPostParameters($app);

        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = json_encode($results);

        $app->response->setBody($results);
    }
);

$app->get(
    '/inventors/query',
    function () use ($app) {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam) = CheckGetParameters($app);

        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = json_encode($results);

        $app->response->setBody($results);
    }
);


$app->post(
    '/inventors/query',
    function () use ($app) {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam) = CheckPostParameters($app);

        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = json_encode($results);

        $app->response->setBody($results);
    }
);


$app->get(
    '/assignees/query',
    function () use ($app) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam) = CheckGetParameters($app);

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = json_encode($results);

        $app->response->setBody($results);
    }
);


$app->post(
    '/assignees/query',
    function () use ($app) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam) = CheckPostParameters($app);

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        $results = json_encode($results);

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

    // Look for a "format" parameter; it may not exist.
    if ($app->request->get('format') != null) {
        if ($app->request->get('format') == 'json')
            $app->contentType('application/json; charset=utf-8');
        elseif ($app->request->get('format') == 'xml')
            $app->contentType('application/xml; charset=utf-8');
        else
            ErrorHandler::getHandler()->sendError(400, "Invalid option for 'format' parameter: use either 'json' or 'xml'.", $app->request->get());
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam);
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

    // Look for a "format" parameter; it may not exist.
    if (array_key_exists('format', $bodyJSON)) {
        if ($bodyJSON['format'] == 'json')
            $app->contentType('application/json; charset=utf-8');
        elseif ($bodyJSON['format'] == 'xml')
            $app->contentType('application/xml; charset=utf-8');
        else
            ErrorHandler::getHandler()->sendError(400, "Invalid option for 'format' parameter: use either 'json' or 'xml'.", $app->request->get());
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam);
}
