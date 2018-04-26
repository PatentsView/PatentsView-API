<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Overrides default limit 0f 256 MB since we process lots of data
// TO DO: Experiment & find optimal value
ini_set('memory_limit', '1G');
require_once dirname(__FILE__) . '/../executeQuery.php';
require_once dirname(__FILE__) . '/../ErrorHandler.php';

// API Specs in regular and associative array format.
require_once dirname(__FILE__) . '/../entitySpecs.php';
// TO Do : Find out need for this file & its function
require_once dirname(__FILE__) . '/../AddEmailDatabase.php';

/**
 * Next sections has "registration" of api urls. All api endpoints that serves
 * patents data performs the following broad operations
 *
 * 1. Validate and parse request parameters - CheckPostParameters/CheckGetParameters
 *      a. Returns 400 bad request when url is malformed or if business logic is violated
 * 2. Execute the database/solr query that corresponds to requested api query
 * 3. Format the results in primary entity - sub-entity format
 *      a. See the *-entity-specs for finding related entities for any given entity
 *      b. When querying for patents, fields that are requested from other entities
 *         (assignees, inventors) etc are nested under (as "assignees" etc) patent main entity
 * 4. Send reponse
 */

// query/q=<query in json format>[&f=<field in json format>][&o=<options in json format>]

//Add to capture the email info
//$app->post(
//    '/addemail',
//    function (Request $req, Response $res, $args=[]) {
//        $body = $app->request->getBody();
//	$bodyJSON = json_decode($body,true);
//	    if ($bodyJSON['email'] == null) {
//            $app->response->status(400);
//	        ErrorHandler::getHandler()->sendError(400, "No email provided");
//	    } else {
//		$email = $bodyJSON['email'];
//            if (strpos($email,"@") === false || strpos($email,".") === false) {
//                $app->response->status(400);
//                ErrorHandler::getHandler()->sendError(400, "Email is invalid: $email");
//            }
//        }
//
//	$addEmailDB = new AddEmailDatabase();
//	$addEmailDB->addEmail($email);
//    }
//);
//
//$app->get(
//    '/patents/query',
//    function (Request $req, Response $res, $args=[]) {
//
//        global $PATENT_ENTITY_SPECS;
//        global $PATENT_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);
//
//        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//
//            return $results = FormatResults($formatParam, $results, $PATENT_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//
//       
//    }
//);
//
//
//$app->post(
//    '/patents/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $PATENT_ENTITY_SPECS;
//        global $PATENT_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);
//
//        $results = executeQuery($PATENT_ENTITY_SPECS, $PATENT_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $PATENT_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//$app->get(
//    '/inventors/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $INVENTOR_ENTITY_SPECS;
//        global $INVENTOR_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);
//
//        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $INVENTOR_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//$app->post(
//    '/inventors/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $INVENTOR_ENTITY_SPECS;
//        global $INVENTOR_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);
//
//        $results = executeQuery($INVENTOR_ENTITY_SPECS, $INVENTOR_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $INVENTOR_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
$app->get(
    '/assignees/query',
    function (Request $req, Response $res, $args = []) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
                if($formatParam == "xml"){
            return $results = FormatResults($formatParam, $results, $ASSIGNEE_ENTITY_SPECS);
        }
        else{
            return $res->withJson($results, 200 );
        }
       
    }
);


$app->post(
    '/assignees/query',
    function (Request $req, Response $res, $args = []) {
        global $ASSIGNEE_ENTITY_SPECS;
        global $ASSIGNEE_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);

        $results = executeQuery($ASSIGNEE_ENTITY_SPECS, $ASSIGNEE_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
                if($formatParam == "xml"){
            return $results = FormatResults($formatParam, $results, $ASSIGNEE_ENTITY_SPECS);
        }
        else{
            return $res->withJson($results, 200 );
        }
       
    }
);
//
//
//$app->get(
//    '/cpc_subsections/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $CPC_ENTITY_SPECS;
//        global $CPC_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);
//
//        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $CPC_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//$app->post(
//    '/cpc_subsections/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $CPC_ENTITY_SPECS;
//        global $CPC_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);
//
//        $results = executeQuery($CPC_ENTITY_SPECS, $CPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $CPC_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//$app->get(
//    '/cpc_groups/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $CPC_GROUP_ENTITY_SPECS;
//        global $CPC_GROUP_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);
//
//        $results = executeQuery($CPC_GROUP_ENTITY_SPECS, $CPC_GROUP_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $CPC_GROUP_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//$app->post(
//    '/cpc_groups/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $CPC_GROUP_ENTITY_SPECS;
//        global $CPC_GROUP_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);
//
//        $results = executeQuery($CPC_GROUP_ENTITY_SPECS, $CPC_GROUP_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $CPC_GROUP_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//
//
//$app->get(
//    '/uspc_mainclasses/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $USPC_ENTITY_SPECS;
//        global $USPC_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);
//
//        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $USPC_ENTITY_SPECS);
//        }
//        else{
//    return $res->withJson($results, 200 );
//}
//       
//    }
//);
//
//
//$app->post(
//    '/uspc_mainclasses/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $USPC_ENTITY_SPECS;
//        global $USPC_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);
//
//        $results = executeQuery($USPC_ENTITY_SPECS, $USPC_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $USPC_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//$app->get(
//    '/nber_subcategories/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $NBER_ENTITY_SPECS;
//        global $NBER_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);
//
//        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $NBER_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);
//
//
//$app->post(
//    '/nber_subcategories/query',
//    function (Request $req, Response $res, $args=[]) {
//        global $NBER_ENTITY_SPECS;
//        global $NBER_FIELD_SPECS;
//
//        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);
//
//        $results = executeQuery($NBER_ENTITY_SPECS, $NBER_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
//                if($formatParam == "xml"){
//            return $results = FormatResults($formatParam, $results, $NBER_ENTITY_SPECS);
//        }
//        else{
//            return $res->withJson($results, 200 );
//        }
//       
//    }
//);


$app->get(
    '/locations/query',
    function (Request $req, Response $res, $args = []) {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckGetParameters($req);

        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);
        if($formatParam == "xml"){
            return $results = FormatResults($formatParam, $results, $LOCATION_ENTITY_SPECS);
        }
        else{
            return $res->withJson($results, 200 );
        }


    }
);


$app->post(
    '/locations/query',
    function (Request $req, Response $res, $args = []) {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;

        list($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam) = CheckPostParameters($req);

        $results = executeQuery($LOCATION_ENTITY_SPECS, $LOCATION_FIELD_SPECS, $queryParam, $fieldsParam, $sortParam, $optionsParam);

        if($formatParam == "xml"){
            return $results = FormatResults($formatParam, $results, $LOCATION_ENTITY_SPECS);
        }
        else{
            return $res->withJson($results, 200 );
        }
    }
);

$app->get(
    '/',
    function (ServerRequestInterface $req, ResponseInterface $res, $args = []) {

        return $res->withStatus(303)->withHeader('Location', '/doc.html');
    }
);


/**
 * @param $app  Slim App - contains means for accessing request parameters
 * @return array JSON decoded PHP Arrays containing, query, sort, options and field list parameters
 *
 * Processes GET parameters
 * 'q' parameter is mandatory. s, o, f and format parameters are  optional
 */
function CheckGetParameters(Request $req)
{
// Make sure the 'q' parameter exists.
    if ($req->getQueryParam('q') == null) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: missing.", $req->getQueryParams());
    }

    // Convert the query param to json, return error if empty or not valid
    $queryParam = json_decode($req->getQueryParam('q'), true);

    if ($queryParam == null) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: not valid json.", $req->getQueryParams());
    }
    // Ensure the query param only has one top-level object
    if (count($queryParam) != 1) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: should only have one json object in the top-level dictionary.", $req->getQueryParams());
    }

    // Look for an "f" parameter; it may not exist.
    $fieldsParam = null;
    if ($req->getQueryParam('f') != null) {
        $fieldsParam = json_decode($req->getQueryParam('f'), true);
        if ($fieldsParam == null) {
            ErrorHandler::getHandler()->sendError(400, "'f' parameter: not valid json.", $req->getQueryParams());
        }
    }

    // Look for an "s" parameter; it may not exist.
    $sortParam = null;
    if ($req->getQueryParam('s') != null) {
        $sortParam = json_decode($req->getQueryParam('s'), true);
        if ($sortParam == null) {
            ErrorHandler::getHandler()->sendError(400, "'s' parameter: not valid json.", $req->getQueryParams());
        }
    }

    // Look for an "o" parameter; it may not exist.
    $optionsParam = null;
    if ($req->getQueryParam('o') != null) {
        $optionsParam = json_decode($req->getQueryParam('o'), true);
        if ($optionsParam == null) {
            ErrorHandler::getHandler()->sendError(400, "'o' parameter: not valid json.", $req->getQueryParams());
        }
    }


    $formatParam = 'json';
    // Look for a "format" parameter; it may not exist.
    if ($req->getQueryParam('format') != null) {
        if (strtolower($req->getQueryParam('format')) == 'json') {
            $formatParam = 'json';
            $app->contentType('application/json; charset=utf-8');
        } elseif (strtolower($req->getQueryParam('format')) == 'xml') {
            $formatParam = 'xml';
            $app->contentType('application/xml; charset=utf-8');
        } else
            ErrorHandler::getHandler()->sendError(400, "Invalid option for 'format' parameter: use either 'json' or 'xml'.", $req->getQueryParams());
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam);
}

/**
 * @param $app  Slim App - contains means for accessing request parameters
 * @return array JSON decoded PHP Arrays containing, query, sort, options and field list parameters
 *
 * Processes GET parameters
 * 'q' parameter is mandatory. s, o, f and format parameters are  optional
 */
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
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: missing.", $req->getQueryParams());
    }
    // Convert the query param to json, return error if empty or not valid
    $queryParam = $bodyJSON['q'];
    // Ensure the query param only has one top-level object
    if (count($queryParam) != 1) {
        ErrorHandler::getHandler()->sendError(400, "'q' parameter: should only have one json object in the top-level dictionary.", $req->getQueryParams());
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
        } elseif ($bodyJSON['format'] == 'xml') {
            $formatParam = 'xml';
            $app->contentType('application/xml; charset=utf-8');
        } else
            ErrorHandler::getHandler()->sendError(400, "Invalid option for 'format' parameter: use either 'json' or 'xml'.", $req->getQueryParams());
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam);
}

/**
 * @param $formatParam Specifies response format
 * @param $results query results in php array format
 * @param $entitySpecs current entity's api specification
 * @return string|mixed Returns query results in json/xml format
 */
function FormatResults($formatParam, $results, $entitySpecs)
{
    if (strtolower($formatParam) == 'xml') {
        $xml = new SimpleXMLElement('<root/>');
        $results = array_to_xml($results, $xml, 'XXX', $entitySpecs)->asXML();
        return $results;
    } else {
        return new \Violet\StreamingJsonEncoder\JsonStream($results);
    }

}

/**
 * @param array $arr Data array
 * @param SimpleXMLElement $xml Base XML element (with <root>
 * @param $parentTag
 * @param $entitySpecs current entity's api specification
 * @return SimpleXMLElement
 */
# TO DO : Explore more 'out of the box' XML options
function array_to_xml(array $arr, SimpleXMLElement $xml, $parentTag, $entitySpecs)
{
    foreach ($arr as $k => $v) {

        $attrArr = array();
        $kArray = explode(' ', $k);
        $tag = array_shift($kArray);

        if (count($kArray) > 0) {
            foreach ($kArray as $attrValue) {
                $attrArr[] = explode('=', $attrValue);
            }
        }

        if (is_array($v)) {
            if (is_numeric($k)) {
                $childTag = substr($parentTag, 0, -1); #Stripping last character which is expected to be an 's'. Doing this case we don't find the tag in the entity specs.
                foreach ($entitySpecs as $entity)
                    if ($entity['group_name'] == $parentTag)
                        $childTag = $entity['entity_name'];
                $child = $xml->addChild($childTag);
                array_to_xml($v, $child, $tag, $entitySpecs);
            } else {
                $child = $xml->addChild($tag);
                if (isset($attrArr)) {
                    foreach ($attrArr as $attrArrV) {
                        $child->addAttribute($attrArrV[0], $attrArrV[1]);
                    }
                }
                array_to_xml($v, $child, $tag, $entitySpecs);
            }
        } else {
            $v = str_replace('&', '&amp;', $v);
            $child = $xml->addChild($tag, $v);
            if (isset($attrArr)) {
                foreach ($attrArr as $attrArrV) {
                    $child->addAttribute($attrArrV[0], $attrArrV[1]);
                }
            }
        }
    }
//TO DO : Try $xml->asXML() to check if string with proper format is returned
    return $xml;
}
