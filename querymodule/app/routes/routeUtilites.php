<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 2:07 PM
 */
require_once(dirname(__FILE__) . "/../Exceptions/RequestException.php");

/**
 * @param $app  Slim App - contains means for accessing request parameters
 * @return array JSON decoded PHP Arrays containing, query, sort, options and field list parameters
 *
 * Processes GET parameters
 * 'q' parameter is mandatory. s, o, f and format parameters are  optional
 */
function CheckGetParameters(array $req)
{
    // Make sure the 'q' parameter exists.
    if (!array_key_exists("q", $req) || $req['q'] == null) {
        throw new \PVExceptions\RequestException("RQ1");
    }
    // Convert the query param to json, return error if empty or not valid
    $queryParam = json_decode($req['q'], true);

    if ($queryParam == null) {
        throw new \PVExceptions\RequestException("RQ2");
    }
    // Ensure the query param only has one top-level object
    if (count($queryParam) != 1) {
        throw new \PVExceptions\RequestException("RQ3");
    }

    // Look for an "f" parameter; it may not exist.
    $fieldsParam = null;
    if (array_key_exists("f", $req) && $req['f'] != null) {
        $fieldsParam = json_decode($req['f'], true);
        if ($fieldsParam == null) {
            throw new \PVExceptions\RequestException("RF2");
        }
    }

    // Look for an "s" parameter; it may not exist.
    $sortParam = null;
    if (array_key_exists("s", $req) && $req['s'] != null) {
        $sortParam = json_decode($req['s'], true);
        if ($sortParam == null) {
            throw new \PVExceptions\RequestException("RS2");
        }
    }

    // Look for an "o" parameter; it may not exist.
    $optionsParam = null;
    if (array_key_exists("o", $req) && $req['o'] != null) {
        $optionsParam = json_decode($req['o'], true);
        if ($optionsParam == null) {
            throw new \PVExceptions\RequestException("RO2");
        }
    }

    $formatParam = 'json';
    // Look for a "format" parameter; it may not exist.
    if (array_key_exists("format", $req) && $req['format'] != null) {
        if (strtolower($req['format']) == 'json') {
            $formatParam = 'json';

        } elseif (strtolower($req['format']) == 'xml') {
            $formatParam = 'xml';

        } else
            throw new \PVExceptions\RequestException("RFO4");
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam);
}

//
function CheckPostParameters($requestBody)
{
    $body = $requestBody;
    $bodyJSON = json_decode($body, true);
    if ($bodyJSON['q'] == null) {
        throw new \PVExceptions\RequestException("POST1", $body);

    }
    //ErrorHandler::getHandler()->sendError(200, $bodyJSON);
    // Make sure the 'q' parameter exists.
    if ($bodyJSON['q'] == null) {
        throw new \PVExceptions\RequestException("RQ1");
    }
    // Convert the query param to json, return error if empty or not valid
    $queryParam = $bodyJSON['q'];
    // Ensure the query param only has one top-level object
    if (count($queryParam) != 1) {
        throw new \PVExceptions\RequestException("RQ3");
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

        } elseif ($bodyJSON['format'] == 'xml') {
            $formatParam = 'xml';

        } else
            throw new \PVExceptions\RequestException("RFO4");
    }

    return array($queryParam, $fieldsParam, $sortParam, $optionsParam, $formatParam);

}

function FormatResults($formatParam, $results, $entitySpecs)
{
    if (strtolower($formatParam) == 'xml') {
        $xml = new SimpleXMLElement('<root/>');
        $results = array_to_xml($results, $xml, 'XXX', $entitySpecs)->asXML();
        return $results;
    } else
        $results = json_encode($results);
    return $results;
}

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

    return $xml;
}