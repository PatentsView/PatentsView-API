<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Overrides default limit 0f 256 MB since we process lots of data
// TO DO: Experiment & find optimal value
ini_set('memory_limit', '1G');
require_once dirname(__FILE__) . '/../executeQuery.php';
require_once dirname(__FILE__) . '/../ErrorHandler.php';
require_once("../app/routes/routeUtilites.php");
// API Specs in regular and associative array format.
require_once dirname(__FILE__) . '/../entitySpecs.php';
// TO Do : Find out need for this file & its function
require_once dirname(__FILE__) . '/../AddEmailDatabase.php';

//query/q=<query in json format>[&f=<field in json format>][&o=<options in json format>]
// $app->get(
//   '/',
//   function () use ($app) {
//       $app->contentType('application/html; charset=utf-8');
//       //$app->response->redirect('doc.html', 303);
//       $app->response->redirect('unavailable.html', 303);
//   }
// );
// $app->get('/:method', function($method) use ($app) {
//     $app->response->status(503);
// })->conditions(array('method' => '.+'));
//Add to capture the email info
$app->post(
    '/addemail',
    function (Request $req, Response $res, $args = []) {
        $body = $req->getBody();
        $bodyJSON = json_decode($body, true);
        if ($bodyJSON['email'] == null) {
            $res->withStatus()->response->status(400, "No email provided");
        } else {
            $email = $bodyJSON['email'];
            if (strpos($email, "@") === false || strpos($email, ".") === false) {
                $res->withStatus()->response->status(400, "Email is invalid: $email");
            }
        }
        $addEmailDB = new AddEmailDatabase();
        $addEmailDB->addEmail($email);
    }
);

$app->get(
    '/patents/query',
    function (Request $req, Response $res, $args = []) {

        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;
        $req->getQueryParams();
        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $PATENT_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/patents/query',
    function (Request $req, Response $res, $args = []) {
        global $PATENT_ENTITY_SPECS;
        global $PATENT_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $PATENT_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);

$app->get(
    '/inventors/query',
    function (Request $req, Response $res, $args = []) {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $INVENTOR_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/inventors/query',
    function (Request $req, Response $res, $args = []) {
        global $INVENTOR_ENTITY_SPECS;
        global $INVENTOR_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $INVENTOR_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->get(
    '/assignees/query',
    function (Request $req, Response $res, $args = []) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $ASSIGNEE_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/assignees/query',
    function (Request $req, Response $res, $args = []) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $ASSIGNEE_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->get(
    '/cpc_subsections/query',
    function (Request $req, Response $res, $args = []) {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $CPC_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/cpc_subsections/query',
    function (Request $req, Response $res, $args = []) {
        global $CPC_ENTITY_SPECS;
        global $CPC_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $CPC_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->get(
    '/cpc_groups/query',
    function (Request $req, Response $res, $args = []) {
        global $CPC_GROUP_ENTITY_SPECS;
        global $CPC_GROUP_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($CPC_GROUP_ENTITY_SPECS, $CPC_GROUP_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $CPC_GROUP_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/cpc_groups/query',
    function (Request $req, Response $res, $args = []) {
        global $CPC_GROUP_ENTITY_SPECS;
        global $CPC_GROUP_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($CPC_GROUP_ENTITY_SPECS, $CPC_GROUP_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $CPC_GROUP_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->get(
    '/uspc_mainclasses/query',
    function (Request $req, Response $res, $args = []) {
        global $USPC_ENTITY_SPECS;
        global $USPC_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $USPC_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/uspc_mainclasses/query',
    function (Request $req, Response $res, $args = []) {
        global $USPC_ENTITY_SPECS;
        global $USPC_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $USPC_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->get(
    '/nber_subcategories/query',
    function (Request $req, Response $res, $args = []) {
        global $NBER_ENTITY_SPECS;
        global $NBER_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $NBER_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/nber_subcategories/query',
    function (Request $req, Response $res, $args = []) {
        global $NBER_ENTITY_SPECS;
        global $NBER_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $NBER_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->get(
    '/locations/query',
    function (Request $req, Response $res, $args = []) {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = checkGetParameters($req->getQueryParams());

        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {
            return $results = FormatResults($formatParam, $results, $LOCATION_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);


$app->post(
    '/locations/query',
    function (Request $req, Response $res, $args = []) {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req->getBody());

        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if ($formatParam == "xml") {

            return $results = FormatResults($formatParam, $results, $LOCATION_ENTITY_SPECS);
        } else {
            return $res->withJson($results, 200);
        }
    }
);

$app->get(
    '/',
    function (Request $req, Response $res, $args = []) {

        return $res->withStatus(301)->withHeader('Location', 'https://patentsview.org/apis/purpose');
    }
);


