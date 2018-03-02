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


        // A criterion should always be a single name-value pair
        reset($criterion);
        $operatorOrField = key($criterion);
        $rightHandValue = current($criterion);

        // If the operator is a comparison, then the right hand value will be a simple pair: { operator : { field : value } }
        if (isset($this->COMPARISON_OPERATORS[$operatorOrField])) {
            $queryArray = $this->processSimplePair($operatorOrField, $rightHandValue);


        }
        elseif (isset($this->RANGE_OPERATORS[$operatorOrField])) {
            $queryArray = $this->processRangePair($operatorOrField, $rightHandValue);

//            $queryString = $simpleQuery;
//            array_push($filterQueryArray, $simpleQueryArray[1]);

        } // If the operator is for strings, then the right hand value will be a simple pair: { operator : { field : value } }
        elseif (isset($this->STRING_OPERATORS[$operatorOrField])) {
            $queryArray = $this->processStringPair($operatorOrField, $rightHandValue);


        } // If the operator is a join, then the right hand value will be a list of criteria: { operator : [ criterion, ... ] }
        elseif (isset($this->JOIN_OPERATORS[$operatorOrField])) {

            $joinString = $this->JOIN_OPERATORS[$operatorOrField];
            $queryArray[$joinString] = array();
            $collections = array();
            $entities = array();
            for ($i = 0; $i < count($rightHandValue); $i++) {
                $retv = $this->processQueryCriterion($rightHandValue[$i], $level + 1);
                if (array_key_exists("c", $retv)) {
                    $collections[] = $retv["c"];
                    $entities[] = $retv["e"];
                }

                $queryArray[$joinString][] = $retv;
            }
            $value_counts = array_count_values($collections);
            if ((count($value_counts) == 1) || (count($value_counts) == 2 && array_key_exists($this->entitySpecs[0]["solr_collection"], $value_counts) && $value_counts[$this->entitySpecs[0]["solr_collection"]] > 0)) {
                $collection_to_use = $this->entitySpecs[0]["solr_collection"];
                $entity_to_use = $this->entitySpecs[0]["entity_name"];
                foreach (array_keys($value_counts) as $collection) {
                    if ($collection != $this->entitySpecs[0]["solr_collection"]) {
                        $collection_to_use = $collection;
                        foreach ($this->entitySpecs as $entitySpec) {
                            if ($entitySpec["solr_collection"] == "$collection_to_use") {
                                $entity_to_use = $entitySpec["entity_name"];
                            }
                        }


                    }
                }

                $mergedQueryArray = array($joinString => array(array("c" => $collection_to_use, "q" => array(), "e" => $entity_to_use)));
                foreach ($queryArray[$joinString] as $query) {
                    $mergedQueryArray[$joinString][0]["q"][] = $query["q"];
                }
                $mergedQueryArray[$joinString][0]["q"] = implode(" $joinString ", $mergedQueryArray[$joinString][0]["q"]);
                $queryArray = $mergedQueryArray;
            }

        } // If the operator is a negation, then the right hand value will be a criterion: { operator : { criterion } }
        elseif (isset($this->NEGATION_OPERATORS[$operatorOrField])) {
            $notString = $this->NEGATION_OPERATORS[$operatorOrField];
            $simpleQueryArray = $this->processQueryCriterion($rightHandValue);

            if (array_key_exists("q", $simpleQueryArray)) {
                $rightHandString = $simpleQueryArray['q'];
                $queryString = "$notString$rightHandString";
                $simpleQueryArray["q"] = $queryString;

            }
            $queryArray = $simpleQueryArray;


        } // If the operator for for full text searching, then it will be: { operator : { field : value } }
        elseif (isset($this->FULLTEXT_OPERATORS[$operatorOrField])) {
            $queryArray = $this->processTextSearch($operatorOrField, $rightHandValue);


        } // Otherwise it is not an operator, but a regular equality pair: { field : value } or { field : [ values, ... ] }
        else {
            $queryArray = $this->processPair('_eq', $criterion);

        }


        return $queryArray;
    }

    private function getDBInfo($apiField)
    {
        $dbFieldInfo = getDBField($this->fieldSpecs, $apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $this->entitySpecs[0]["solr_collection"];
        $entity = $dbFieldInfo["entity_name"];
        foreach ($this->entitySpecs as $entitySpec) {
            if ($entitySpec["entity_name"] == $dbFieldInfo["entity_name"]) {
                $solr_collection = $entitySpec["solr_collection"];

            }
        }
        return array("dbField" => $dbField, "solr_collection" => $solr_collection, "entity_name" => $entity);
    }

    private function processPair($operator, $criterion)
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


        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity_name"]);
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
                        ErrorHandler::getHandler()->sendError(400, "Invalid float value provided: $val.");
                        throw new ErrorException("Invalid integer value provided: $val.");
                    }
                    $returnString = "$dbField $operatorString $val";

                } elseif ($datatype == 'int') {
                    if (!is_numeric($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid integer value provided: $val.");
                        throw new ErrorException("Invalid integer value provided: $val.");
                    }
                    $returnString = "$dbField $operatorString $val";

                } elseif ($datatype == 'date') {
                    if (!strtotime($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid date provided: $val.");
                        throw new ErrorException("Invalid date provided: $val.");
                    }
                    $returnString = "$dbField $operatorString " . date('Y-m-d\TH\\\:i\\\:s\Z', strtotime($val)) . "";

                    file_put_contents('php://stderr', print_r($returnString, TRUE));
                } elseif (($datatype == 'string') or ($datatype == 'fulltext')) {
                    $returnString = "$dbField $operatorString $val";

                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' or operator '$operator' found for '$apiField'.");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField'.");
                }
            } else {
                $msg = "Not a valid field for querying: $apiField";
                ErrorHandler::getHandler()->sendError(400, $msg);
                throw new ErrorException($msg);
            }
        }

        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity_name"]);

    }

    private function processRangePair($operator, $criterion)
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
                        ErrorHandler::getHandler()->sendError(400, "Invalid float value provided: $val.");
                        throw new ErrorException("Invalid integer value provided: $val.");
                    }
                    $returnString = sprintf("$operatorString", $dbField, $val);

                } elseif ($datatype == 'int') {
                    if (!is_numeric($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid integer value provided: $val.");
                        throw new ErrorException("Invalid integer value provided: $val.");
                    }
                    $returnString = sprintf("$operatorString", $dbField, $val);

                } elseif ($datatype == 'date') {
                    if (!strtotime($val)) {
                        ErrorHandler::getHandler()->sendError(400, "Invalid date provided: $val.");
                        throw new ErrorException("Invalid date provided: $val.");
                    }
                    $returnString = sprintf("$operatorString", $dbField, date('Y-m-d\TH\\\\:i\\\\:s\Z', strtotime($val)));

                } elseif (($datatype == 'string') or ($datatype == 'fulltext')) {
                    $returnString = sprintf("$operatorString", $dbField, $val);

                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' or operator '$operator' found for '$apiField'.");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField'.");
                }
            } else {
                $msg = "Not a valid field for querying: $apiField";
                ErrorHandler::getHandler()->sendError(400, $msg);
                throw new ErrorException($msg);
            }
        }
        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity_name"]);

    }

    private function processStringPair($operator, $criterion)
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
                    if ($operator == '_begins')
                        $returnString = "$dbField : $val*";
                    elseif ($operator == '_contains')
                        $returnString = "$dbField : *$val*";
                } else {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field type '$datatype' or operator '$operator' found for '$apiField'.");
                    throw new ErrorException("Invalid field type '$datatype' found for '$apiField'.");
                }
            } else {
                $msg = "Not a valid field for querying: $apiField";
                ErrorHandler::getHandler()->sendError(400, $msg);
                throw new ErrorException($msg);
            }
        }
        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity_name"]);
    }

    private function processTextSearch($operator, $criterion)
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
                    throw new ErrorException("The operation '$operator' is not valid on '$apiField''.");
                }

                if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                if ($operator == '_text_phrase') {
                    $returnString = "$dbField : \"$val\"";
                } elseif ($operator == '_text_any') {
                    $pieces = explode(" ", $val);
                    $returnString = "$dbField : ".implode(" OR ", $pieces);

                } elseif ($operator == '_text_all') {
                    $pieces = explode(" ", $val);
                    $returnString = "$dbField : ".implode(" AND ", $pieces);


                } else {
                    $msg = "Not a valid field for querying: $apiField";
                    ErrorHandler::getHandler()->sendError(400, $msg);
                    throw new ErrorException($msg);
                }
            }
            return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity_name"]);
        }

    }
}

function parseFieldList(array $entitySpecs, array $fieldSpecs, array $fieldsParam = null)
{
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        $current_entity = $entitySpecs[0]["entity_name"];
        try {
            foreach ($entitySpecs as $entitySpec) {
                if ($entitySpec["entity_name"] == $fieldSpecs[$fieldsParam[$i]]["entity_name"]) {
                    $current_entity = $entitySpec["entity_name"];
                }
            }
            if (!array_key_exists($current_entity, $returnFieldSpecs)) {
                $returnFieldSpecs[$current_entity] = array();
                $returnFieldSpecs[$current_entity][$entitySpecs[0]["solr_key_id"]] = $fieldSpecs[$entitySpecs[0]["solr_key_id"]];
            }

            $returnFieldSpecs[$current_entity][$fieldsParam[$i]] = $fieldSpecs[$fieldsParam[$i]];
        } catch (Exception $e) {
            ErrorHandler::getHandler()->sendError(400, 'Invalid field specified: ' . $fieldsParam[$i], $e);
        }

    }


    return $returnFieldSpecs;
}