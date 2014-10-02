<?php
require_once dirname(__FILE__) . '/../execute_query.php';

// query/q=<query in json format>[&f=<field in json format>][&o=<options in json format>]
$app->get(
    '/query',
    /**
     *
     */
    function () use ($app) {
        // Make sure the 'q' parameter exists.
        if ($app->request->get('q') == null) {
            $app->response->header('X-Status-Reason', "'q' parameter: Missing.");
            $app->halt(400);
        }
        // Convert the query param to json, return error if empty or not valid
        $queryParam = json_decode($app->request->get('q'), true);
        if ($queryParam == null) {
            $app->response->header('X-Status-Reason', "'q' parameter: not valid json.");
            $app->halt(400);
        }
        // Ensure the query param only has one top-level object
        if (count($queryParam) != 1) {
            $app->response->header('X-Status-Reason', "'q' parameter: should only have one json object in the top-level dictionary.");
            $app->halt(400);
        }

        // Look for an "f" parameter; it may not exist.
        $fieldsParam = null;
        if ($app->request->get('f') != null) {
            $fieldsParam = json_decode($app->request->get('f'), true);
            if ($fieldsParam == null) {
                $app->response->header('X-Status-Reason', "'f' parameter: not valid json.");
                $app->halt(400);
            }
        }

        // Look for an "o" parameter; it may not exist.
        $optionsParam = null;
        if ($app->request->get('o') != null) {
            $optionsParam = json_decode($app->request->get('o'), true);
            if ($optionsParam == null) {
                $app->response->header('X-Status-Reason', "'o' parameter: not valid json.");
                $app->halt(400);
            }
        }

        $results = executeQuery($queryParam, $fieldsParam);
        $results = json_encode($results);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->setBody($results);
    }
);