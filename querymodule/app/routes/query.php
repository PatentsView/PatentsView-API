<?php
require_once dirname(__FILE__) . '/../execute_query.php';
require_once dirname(__FILE__) . '/../ErrorHandler.php';

// query/q=<query in json format>[&f=<field in json format>][&o=<options in json format>]
$app->get(
    '/query',
    function () use ($app) {
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

        $results = executeQuery($queryParam, $fieldsParam, $sortParam);
        $results = json_encode($results);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody($results);
    }
);


$app->post(
    '/query',
    function () use ($app) {
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

        // Look for an "s" parameter; it may not exist.
        if (array_key_exists('s', $bodyJSON))
            $sortParam = $bodyJSON['s'];

        // Look for an "o" parameter; it may not exist.
        if (array_key_exists('o', $bodyJSON))
            $optionsParam = $bodyJSON['o'];

        $results = executeQuery($queryParam, $fieldsParam, $sortParam);
        $results = json_encode($results);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody($results);
    }
);