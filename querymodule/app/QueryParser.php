<?php

require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


class QueryParser
{
    private $COMPARISON_OPERATORS = array('_eq' => ':');
    private $STRING_OPERATORS = array('_begins' => '', '_contains' => '');
    private $FULLTEXT_OPERATORS = array('_text_all' => '', '_text_any' => '', '_text_phrase' => '');
    private $JOIN_OPERATORS = array('_and' => 'AND', '_or' => 'OR');
    private $NEGATION_OPERATORS = array('_not' => '-', '_neq' => '-');
    private $RANGE_OPERATORS = array('_lt' => '%s : { * TO %s }', '_lte' => '%s : [ * TO %s ]', '_gt' => '%s : { %s TO * }', '_gte' => '%s : [ %s TO * ]');
    private $fieldsUsed;
    private $whereClause;
    private $entityName;
    private $onlyAndsWereUsed; #Used to keep track of whether the query criteria are only joined by ANDs.
    private $entitySpecs;
    private $fieldSpecs;

    public function getFieldsUsed()
    {
        return $this->fieldsUsed;
    }

    public function getWhereClause()
    {
        return $this->whereClause;
    }

    public function getOnlyAndsWereUsed()
    {
        return $this->onlyAndsWereUsed;
    }

    public function parseSortQuery(array $fieldSpecs, array $orderQuery)
    {

        $this->fieldSpecs = $fieldSpecs;
        $sortSpecs = array();

        foreach ($orderQuery as $order) {
            $currKey = "";
            $currValue = "";
            foreach ($order as $key => $value) {
                $currKey = getDBField($this->fieldSpecs, $key)["dbField"];
                $currValue = $value;
            }
            $sortSpecs[$currKey] = $currValue;
        }

        return $sortSpecs;
    }

    public function parse(array $fieldSpecs, array $query, $entityName, array $entitySpecs)
    {
        $this->fieldSpecs = $fieldSpecs;
        $this->entitySpecs = $entitySpecs;
        $this->whereClause = '';
        $this->fieldsUsed = array();
        $this->entityName = $entityName;
        $this->onlyAndsWereUsed = true;
        // There should only be one pair in this array
        if (count($query) == 1) {
            $criteria = $query;
            $this->whereClause = $this->processQueryCriterion($criteria);
        }

        return $this->whereClause;
    }

    private function processQueryCriterion(array $criterion, $level = 0)
    {

        $queryArray = array();
        $streamString = "";

        // A criterion should always be a single name-value pair
        reset($criterion);
        $operatorOrField = key($criterion);
        $rightHandValue = current($criterion);

        // If the operator is a comparison, then the right hand value will be a simple pair: { operator : { field : value } }
        if (isset($this->COMPARISON_OPERATORS[$operatorOrField])) {
            $clauseArray = $this->processSimplePair($operatorOrField, $rightHandValue);
            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_key_id"] . "," . $this->entitySpecs[0]["solr_fetch_id"], "sort" => $clauseArray["e"]["solr_key_id"] . ' asc', "qt" => "/export","df"=>$clauseArray["df"]);

            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];
        } elseif (isset($this->RANGE_OPERATORS[$operatorOrField])) {
            $clauseArray = $this->processRangePair($operatorOrField, $rightHandValue);
            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_key_id"] . "," . $this->entitySpecs[0]["solr_fetch_id"], "sort" => $clauseArray["e"]["solr_key_id"] . ' asc', "qt" => "/export","df"=>$clauseArray["df"]);

            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];
//            $queryString = $simpleQuery;
//            array_push($filterQueryArray, $simpleQueryArray[1]);

        } // If the operator is for strings, then the right hand value will be a simple pair: { operator : { field : value } }
        elseif (isset($this->STRING_OPERATORS[$operatorOrField])) {
            $clauseArray = $this->processStringPair($operatorOrField, $rightHandValue);
            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_key_id"] . "," . $this->entitySpecs[0]["solr_fetch_id"], "sort" => $clauseArray["e"]["solr_key_id"] . ' asc', "qt" => "/export","df"=>$clauseArray["df"]);


            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];

        } // If the operator is a join, then the right hand value will be a list of criteria: { operator : [ criterion, ... ] }
        elseif (isset($this->JOIN_OPERATORS[$operatorOrField])) {

            $joinString = $this->JOIN_OPERATORS[$operatorOrField];
            //$queryArray[$joinString] = array();
            $streamArray = array();
            if (count($rightHandValue) < 2) {
                ErrorHandler::getHandler()->sendError(400, "Less than 2 operands provided for : $operatorOrField . ");
                throw new ErrorException("$operatorOrField has to have atleast 2 operands");
            }
            for ($i = 0; $i < count($rightHandValue); $i++) {
                $retv = $this->processQueryCriterion($rightHandValue[$i], $level + 1);

                if (array_key_exists("collection", $retv)) {
                    $collection = $retv["collection"];
                    if (!array_key_exists($collection, $streamArray)) {
                        $streamArray[$collection] = array();
                    }
                    array_push($streamArray[$collection], $retv["query"]);
                }
            }
            $flatStreamArray = array();
            foreach ($streamArray as $collection => $clauses) {
                if ($collection == "join_stream") {
                    $flatStreamArray = array_merge($flatStreamArray, $clauses);
                    continue;

                }
                $queries = array();
                foreach ($clauses as $clause) {
                    $queries[] = $clause['q'];
                }
                $streamSourceString = 'search (' . $collection . ', ';
                foreach ($clauses[0] as $argument_name => $argument_value) {
                    if ($argument_name != "q")
                        $streamSourceString .= $argument_name . '="' . $argument_value . '",';
                }
                $flatStreamArray[] = $streamSourceString . 'q=' . implode($joinString, $queries) . ")";
            }


            $query = processConjugation(array_values($flatStreamArray), $joinString, $this->entitySpecs[0]["solr_key_id"]);
            $queryArray = array("collection" => "join_stream", "query" => $query);

//            $value_counts = array_count_values($collections);
//            if ((count($value_counts) == 1) || (count($value_counts) == 2 && array_key_exists($this->entitySpecs[0]["solr_collection"], $value_counts) && $value_counts[$this->entitySpecs[0]["solr_collection"]] > 0)) {
//                $collection_to_use = $this->entitySpecs[0]["solr_collection"];
//                $entity_to_use = $this->entitySpecs[0]["entity_name"];
//                foreach (array_keys($value_counts) as $collection) {
//                    if ($collection != $this->entitySpecs[0]["solr_collection"]) {
//                        $collection_to_use = $collection;
//                        foreach ($this->entitySpecs as $entitySpec) {
//                            if ($entitySpec["solr_collection"] == "$collection_to_use") {
//                                $entity_to_use = $entitySpec["entity_name"];
//                            }
//                        }
//
//
//                    }
//                }
//
//                $mergedQueryArray = array($joinString => array(array("c" => $collection_to_use, "q" => array(), "e" => $entity_to_use)));
//                foreach ($queryArray[$joinString] as $query) {
//                    $mergedQueryArray[$joinString][0]["q"][] = $query["q"];
//                }
//                $mergedQueryArray[$joinString][0]["q"] = implode(" $joinString ", $mergedQueryArray[$joinString][0]["q"]);
//                $queryArray = $mergedQueryArray;
            //}

        } // If the operator is a negation, then the right hand value will be a criterion: { operator : { criterion } }
        elseif (isset($this->NEGATION_OPERATORS[$operatorOrField])) {
            $notString = $this->NEGATION_OPERATORS[$operatorOrField];
            $clauseArray = $this->processQueryCriterion($rightHandValue);
            $collection = $clauseArray["collection"];
            //$streamArray = array("q" => $clauseArray["query"]["q"], "fl" => $clauseArray["query"]["e"]["solr_key_id"] . "," . $this->entitySpecs[0]["solr_key_id"], "sort" => $clauseArray["query"]["e"]["solr_key_id"] . ' asc', "qt" => "/export");
            $streamSourceString = $clauseArray["query"];
            if (is_array($clauseArray["query"])) {
                $streamSourceString = 'search (' . $collection . ', ';
                foreach ($clauseArray["query"] as $argument_name => $argument_value) {
                    if ($argument_name != "q")
                        $streamSourceString .= $argument_name . '="' . $argument_value . '",';
                }
                $streamSourceString='complement(' . $streamSourceString . ',q=*:*),' . $streamSourceString . 'q=' . $clauseArray["query"]["q"] . ')' . ',on="' . $this->entitySpecs[0]["solr_key_id"] . '")';
            }
            $queryArray["query"] = $streamSourceString;
            //$queryArray["collection"] = $clauseArray['collection'];
            $queryArray["collection"] = "join_stream";

        } // If the operator for for full text searching, then it will be: { operator : { field : value } }
        elseif (isset($this->FULLTEXT_OPERATORS[$operatorOrField])) {

            $clauseArray = $this->processTextSearch($operatorOrField, $rightHandValue);
            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_key_id"] . "," . $this->entitySpecs[0]["solr_fetch_id"], "sort" => $clauseArray["e"]["solr_key_id"] . ' asc', "qt" => "/export","df"=>$clauseArray["df"]);


            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];

        } // Otherwise it is not an operator, but a regular equality pair: { field : value } or { field : [ values, ... ] }
        else {
            $clauseArray = $this->processPair('_eq', $criterion);
            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_key_id"] . "," . $this->entitySpecs[0]["solr_fetch_id"], "sort" => $clauseArray["e"]["solr_key_id"] . ' asc', "qt" => "/export","df"=>$clauseArray["df"]);
            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];
        }

        if ($level == 0 & is_array($queryArray["query"])) {
            $streamSourceString = 'search (' . $queryArray["collection"] . ', ';
            foreach ($queryArray["query"] as $argument_name => $argument_value) {
                if ($argument_name != "q")
                    $streamSourceString .= $argument_name . '="' . $argument_value . '",';
            }
            $streamSourceString = $streamSourceString . 'q=' . $queryArray["query"]["q"] . ")";
            $queryArray["query"] = $streamSourceString;
        }
        return $queryArray;
    }

    private function processSimplePair($operator, $criterion)
    {

        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        $dbFieldInfo = $this->getDBInfo($apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $dbFieldInfo["solr_collection"];
        if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
            if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                $val = current($criterion);

                $datatype = $this->fieldSpecs[$apiField]['datatype'];
                if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                $operatorString = $this->COMPARISON_OPERATORS[$operator];
                if ($datatype == 'float') {
                    if (!is_float($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid float value provided: $val . ");
                        throw new ErrorException("Invalid integer value provided: $val . ");
                    }
                    $returnString = "$dbField $operatorString $val";

                } elseif ($datatype == 'int') {
                    if (!is_numeric($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid integer value provided: $val . ");
                        throw new ErrorException("Invalid integer value provided: $val . ");
                    }
                    $returnString = "$dbField $operatorString $val";

                } elseif ($datatype == 'date') {
                    if (!strtotime($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid date provided: $val . ");
                        throw new ErrorException("Invalid date provided: $val . ");
                    }
                    $returnString = "$dbField $operatorString " . date('Y-m-d\TH\\\:i\\\:s\Z', strtotime($val)) . "";

                    file_put_contents('php://stderr', print_r($returnString, TRUE));
                } elseif (($datatype == 'string') or ($datatype == 'fulltext')) {
                    if ($val == trim($val) && strpos($val, ' ') !== false) {
                        $val = '"' . $val . '"';
                    }
                    $returnString = "$dbField $operatorString $val";

                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' or operator '$operator' found for '$apiField' . ");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField' . ");
                }
            } else {
                $msg = "Not a valid field for querying: $apiField";
                ErrorHandler::getHandler()->sendError(400, $msg);
                throw new ErrorException($msg);
            }
        }

        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"],"df"=>$dbField);

    }

    private function getDBInfo($apiField)
    {
        $dbFieldInfo = getDBField($this->fieldSpecs, $apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $this->entitySpecs[0]["solr_collection"];
        $entity = $dbFieldInfo["entity_name"];
        $entitySpec = getEntitySpecs($this->entitySpecs, $entity);
        $solr_collection = $entitySpec["solr_collection"];
        $secondaryUsage = false;
        if (array_key_exists("secondarySource", $entitySpec))
            $secondaryUsage = true;


        return array("dbField" => $dbField, "solr_collection" => $solr_collection, "entity" => $entitySpec, "secondaryUsage" => $secondaryUsage,"df"=>$dbField);
    }

    private
    function processRangePair($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;

        $apiField = key($criterion);
        $dbFieldInfo = $this->getDBInfo($apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $dbFieldInfo["solr_collection"];

        if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
            if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                $val = current($criterion);

                $datatype = $this->fieldSpecs[$apiField]['datatype'];
                if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                $operatorString = $this->RANGE_OPERATORS[$operator];

                if ($datatype == 'float') {
                    if (!is_float($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid float value provided: $val . ");
                        throw new ErrorException("Invalid integer value provided: $val . ");
                    }
                    $returnString = sprintf("$operatorString", $dbField, $val);

                } elseif ($datatype == 'int') {
                    if (!is_numeric($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid integer value provided: $val . ");
                        throw new ErrorException("Invalid integer value provided: $val . ");
                    }
                    $returnString = sprintf("$operatorString", $dbField, $val);

                } elseif ($datatype == 'date') {
                    if (!strtotime($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid date provided: $val . ");
                        throw new ErrorException("Invalid date provided: $val . ");
                    }
                    $returnString = sprintf("$operatorString", $dbField, date('Y-m-d\TH\\\\:i\\\\:s\Z', strtotime($val)));

                } elseif (($datatype == 'string') or ($datatype == 'fulltext')) {
                    $returnString = sprintf("$operatorString", $dbField, $val);

                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' or operator '$operator' found for '$apiField' . ");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField' . ");
                }
            } else {
                $msg = "Not a valid field for querying: $apiField";
                ErrorHandler::getHandler()->sendError(400, $msg);
                throw new ErrorException($msg);
            }
        }
        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"],"df"=>$dbField);

    }

    private
    function processStringPair($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        $dbFieldInfo = $this->getDBInfo($apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $dbFieldInfo["solr_collection"];
        if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
            if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                $val = current($criterion);
                $datatype = $this->fieldSpecs[$apiField]['datatype'];
                if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                if ($datatype == 'string') {

                    if ($operator == '_begins') {
                        $returnString = "$dbField : $val*";
                    } elseif ($operator == '_contains') {
                        $returnString = "$dbField : *$val* ";
                    }


                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' or operator '$operator' found for '$apiField' . ");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField' . ");
                }
            } else {
                $msg = "Not a valid field for querying: $apiField";
                ErrorHandler::getHandler()->sendError(400, $msg);
                throw new ErrorException($msg);
            }
        }
        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"],"df"=>$dbField);
    }

    private
    function processTextSearch($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        $dbFieldInfo = $this->getDBInfo($apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $dbFieldInfo["solr_collection"];
        if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
            if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                $val = current($criterion);


                if ($this->fieldSpecs[$apiField]['datatype'] != 'fulltext') {
                    ErrorHandler::getHandler()->sendError(400, "The operation '$operator' is not valid on '$apiField''.");
                    throw new ErrorException("The operation '$operator' is not valid on '$apiField'' . ");
                }

                if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                if ($operator == '_text_phrase') {
                    $returnString = "$dbField : \"$val\"";
                } elseif ($operator == '_text_any') {
                    $pieces = explode(" ", $val);
                    $returnString = "$dbField : " . implode(" OR ", $pieces);

                } elseif ($operator == '_text_all') {
                    $pieces = explode(" ", $val);
                    $returnString = "$dbField : " . implode(" AND ", $pieces);


                } else {
                    $msg = "Not a valid field for querying: $apiField";
                    ErrorHandler::getHandler()->sendError(400, $msg);
                    throw new ErrorException($msg);
                }
            }
            return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"],"df"=>$dbField);
        }

    }

    private
    function processPair($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);

        $dbFieldInfo = $this->getDBInfo($apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $dbFieldInfo["solr_collection"];

        if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
            $val = current($criterion);

            $datatype = $this->fieldSpecs[$apiField]['datatype'];
            // If of the type: { field : value }
            if (!is_array($val)) {
                $simpleQueryArray = $this->processSimplePair($operator, $criterion);
                $returnString = $simpleQueryArray["q"];
            } // Else of the type { field : [value,...] }
            else {
                if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                if ($datatype == 'int') {
                    foreach ($val as $singleVal) {
                        if (!is_numeric($singleVal)) {
                            ErrorHandler::getHandler()->sendError(400, "Invalid integer value provided: $singleVal.");
                            throw new ErrorException("Invalid date provided: $singleVal.");
                        }
                    }
                    $returnString = "($dbField : (" . implode(" OR ", $val) . "))";
                } elseif ($datatype == 'float') {
                    foreach ($val as $singleVal) {
                        if (!is_float($singleVal)) {
                            ErrorHandler::getHandler()->sendError(400, "Invalid float value provided: $singleVal.");
                            throw new ErrorException("Invalid date provided: $singleVal.");
                        }
                    }
                    $returnString = "($dbField : (" . implode(" OR ", $val) . "))";
                    $nullString = "-$dbField:\\-1";
                } elseif ($datatype == 'date') {
                    $dateVals = array();
                    foreach ($val as $singleVal) {
                        if (strtotime($singleVal)) {
                            $dateVals[] = date('Y-m-d\THH\\:mi\\:ss', strtotime($singleVal));
                            $nullString = "-$dbField:9999-12-31T00\\:00\\:00Z";
                        } else {
                            ErrorHandler::getHandler()->sendError(400, "Invalid date provided: $singleVal.");
                            throw new ErrorException("Invalid date provided: $singleVal.");
                        }
                    }
                    $returnString = "$dbField : (" . implode(" OR ", $dateVals) . ")";
                } elseif (($datatype == 'string') or ($datatype == 'fulltext')) {
                    $returnString = "$dbField : (" . implode(" OR ", $val) . ")";

                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' found for '$apiField'.");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField'.");
                }
            }
        } else {
            $msg = "Not a valid field for querying: $apiField";
            ErrorHandler::getHandler()->sendError(400, $msg);
            throw new ErrorException($msg);
        }


        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"],"df"=>$dbField);
    }
}


function processConjugation($clauses, $join, $field)
{
    $streamDecorator = "intersect";
    if ($join == "OR") {
        $field = $field . " asc";
        $streamDecorator = "merge";

    }
    return $streamDecorator . '(' . implode(",", $clauses) . ',on="' . $field . '")';
}

function parseFieldList(array $entitySpecs, array $fieldSpecs, array $fieldsParam = null)
{
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        try {
            $current_entity = getEntitySpecs($entitySpecs, $fieldSpecs[$fieldsParam[$i]]["entity_name"]);
            if (!array_key_exists($current_entity["entity_name"], $returnFieldSpecs)) {
                $returnFieldSpecs[$current_entity["entity_name"]] = array();
                $returnFieldSpecs[$current_entity["entity_name"]][$entitySpecs[0]["solr_fetch_id"]] = $fieldSpecs[$entitySpecs[0]["solr_fetch_id"]];
            }

            $returnFieldSpecs[$current_entity["entity_name"]][$fieldsParam[$i]] = $fieldSpecs[$fieldsParam[$i]];
        } catch (Exception $e) {
            ErrorHandler::getHandler()->sendError(400, 'Invalid field specified: ' . $fieldsParam[$i], $e);
        }

    }


    return $returnFieldSpecs;
}