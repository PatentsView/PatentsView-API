<?php

require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


/**
 * Class QueryParser
 *
 * Contains variables & methods used to convert query parameters into SOLR streaming expression
 */
class QueryParser
{
    /**
     * @var array API Query to SOLR query operator mapping for equality comparison
     */
    private $COMPARISON_OPERATORS = array('_eq' => ':');
    /**
     * @var array API Query to SOLR query operator mapping for string operations
     */
    private $STRING_OPERATORS = array('_begins' => '', '_contains' => '');
    /**
     * @var array API Query to SOLR query operator mapping for full text operations
     */
    private $FULLTEXT_OPERATORS = array('_text_all' => '', '_text_any' => '', '_text_phrase' => '');
    /**
     * @var array API Query to SOLR query operator mapping for conjugation operations
     */
    private $JOIN_OPERATORS = array('_and' => 'AND', '_or' => 'OR');
    /**
     * @var array API Query to SOLR query operator mapping for (non) equality comparison
     */
    private $NEGATION_OPERATORS = array('_not' => '-', '_neq' => '-');
    /**
     * @var array API Query to SOLR query operator mapping for range query
     */
    private $RANGE_OPERATORS = array('_lt' => '%s : { * TO %s }', '_lte' => '%s : [ * TO %s ]', '_gt' => '%s : { %s TO * }', '_gte' => '%s : [ %s TO * ]');

    /**
     * @var Holds the specs for primary entity
     */
    private $primaryEntity;
    /**
     * @var Holds all the fields that are used in current query (Not used currently)
     */
    private $fieldsUsed;
    /**
     * @var Holds streaming expression for current query
     */
    private $streamingXpression;
    /**
     * @var Primary entity specs for current query
     */
    private $entitySpecs;
    /**
     * @var Field specs for current primary entity
     */
    private $fieldSpecs;
    private $useSecondary;

    /**
     * @return mixed returns the fields that are used (Not used currently)
     */
    public function getFieldsUsed()
    {
        return $this->fieldsUsed;
    }

    /**
     * @return string Returns streaming expression for current query
     */
    public function getStreamingXpression()
    {
        return $this->streamingXpression;
    }


    /**
     * @param array $fieldSpecs field specs of current entity
     * @param array $query PHP Array containing query terms (possibly nested)
     * @param array $entitySpecs primary entity specifications
     * @return array Array containing streaming expression & collection indicating collection/join
     * Method which intitializes parse values & makes first invocation of recursive function
     */
    public function parse(array $fieldSpecs, array $query, array $entitySpecs)
    {
        // TO DO : Move initializatins to constructor
        $this->fieldSpecs = $fieldSpecs;
        $this->entitySpecs = $entitySpecs;

        $this->streamingXpression = '';
        $this->primaryEntity = $this->entitySpecs[0];

        // There should only be one pair in this array
        // TO DO: Verify if this check is already implemented in check*Parameters methods
        if (count($query) == 1) {
            $criteria = $query;
            $this->streamingXpression = $this->processQueryCriterion($criteria);
        }


        return $this->streamingXpression;
    }

    /**
     * @param array $criterion a query clause, made of operator & operands (which itself can be subclauses)
     * @param int $level an integer which tracks number of recursion
     * @return array Containing streaming expression for current clause & collection to operate on
     * @throws ErrorException
     */
    private function processQueryCriterion(array $criterion, $level = 0)
    {

        $queryArray = array();
        $streamString = "";

        // A criterion should always be a single name-value pair
        reset($criterion);


        // The key can either be an operator greater
        // less etc, or a field ( in such case equality operator is assumed)
        // TO DO : Link API documentation
        $operatorOrField = key($criterion); // Read the variable name as "operator OR Field",
        $rightHandValue = current($criterion);

        // If the operator is a comparison, then the right hand value will be a simple pair: { operator : { field : value } }
        if (isset($this->COMPARISON_OPERATORS[$operatorOrField])) {
            $clauseArray = $this->processSimplePair($operatorOrField, $rightHandValue);
            // Construction of array containing keys representing streaming expression components
            // TO DO : Link streaming expression page

            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_join_id"] . "," . $this->entitySpecs[0]["solr_key_id"], "sort" => $clauseArray["e"]["solr_join_id"] . ' asc', "qt" => "/export", "df" => $clauseArray["df"]);

            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];
        } elseif (isset($this->RANGE_OPERATORS[$operatorOrField])) {
            $clauseArray = $this->processRangePair($operatorOrField, $rightHandValue);
            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_join_id"] . "," . $this->entitySpecs[0]["solr_key_id"], "sort" => $clauseArray["e"]["solr_join_id"] . ' asc', "qt" => "/export", "df" => $clauseArray["df"]);
            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];
        }

        // If the operator is for strings, then the right hand value will be a simple pair: { operator : { field : value } }
        elseif (isset($this->STRING_OPERATORS[$operatorOrField])) {
            $clauseArray = $this->processStringPair($operatorOrField, $rightHandValue);

            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_join_id"] . "," . $this->entitySpecs[0]["solr_key_id"], "sort" => $clauseArray["e"]["solr_join_id"] . ' asc', "qt" => "/export", "df" => $clauseArray["df"]);


            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];
        }

        // If the operator is a join, then the right hand value will be a list of criteria: { operator : [ criterion, ... ] }
        elseif (isset($this->JOIN_OPERATORS[$operatorOrField])) {
            $joinString = $this->JOIN_OPERATORS[$operatorOrField];
            $streamArray = array();
            if (count($rightHandValue) < 2) {
                ErrorHandler::getHandler()->sendError(400, "Less than 2 operands provided for : $operatorOrField . ");
                throw new ErrorException("$operatorOrField has to have at least 2 operands");
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
            // Collect the clauses in an array
            $flatStreamArray = array();

            // Merge clauses if they share same collection
            foreach ($streamArray as $collection => $clauses) {
                // Expressions which are already conjugated can't be merged
                // since they will query multiple collections
                if ($collection == "join_stream") {
                    $flatStreamArray = array_merge($flatStreamArray, $clauses);
                    continue;
                }
                $queries = array();
                foreach ($clauses as $clause) {
                    $queries[] = $clause['q'];
                }
                // Generate expression after merging & add them to array
                $streamSourceString = 'search (' . $collection . ', ';
                foreach ($clauses[0] as $argument_name => $argument_value) {
                    if ($argument_name != "q")
                        $streamSourceString .= $argument_name . '="' . $argument_value . '",';
                }
                $flatStreamArray[] = $streamSourceString . 'q=' . implode(" " . $joinString . " ", $queries) . ")";
            }
            // Join the expressions using suitable join (AND / OR)
            $query = processConjugation(array_values($flatStreamArray), $joinString, $this->primaryEntity["solr_join_id"]);
            $queryArray = array("collection" => "join_stream", "query" => $query);


        }
        // If the operator is a negation, then the right hand value will be a criterion: { operator : { criterion } }

        elseif (isset($this->NEGATION_OPERATORS[$operatorOrField])) {
            // To Do : Find why SOLR negation operator does not work with stream expression
            $notString = $this->NEGATION_OPERATORS[$operatorOrField];
            $clauseArray = $this->processQueryCriterion($rightHandValue);
            // not can be applied on both direct fiels & conjugations
            // so we have to handle both cases
            $collection = $clauseArray["collection"];
            // Case 1: Field
            $streamSourceString = $clauseArray["query"];

            // Case 1: Field
            if (is_array($clauseArray["query"])) {
                $streamSourceString = 'search (' . $collection . ', ';
                foreach ($clauseArray["query"] as $argument_name => $argument_value) {
                    if ($argument_name != "q")
                        $streamSourceString .= $argument_name . '="' . $argument_value . '",';
                }

                $streamSourceString = 'complement(' . $streamSourceString . ',q=*:*),' . $streamSourceString . 'q=' . $clauseArray["query"]["q"] . ')' . ',on="' . $this->entitySpecs[0]["solr_join_id"] . '")';
            } else {
                $queryParts = explode(',', $clauseArray["query"]);

                $streamSourceString = 'complement(' . implode(",", array_slice($queryParts, 0, count($queryParts) - 1)) . ",q=*:*)," . $clauseArray["query"] . ',on="' . $this->entitySpecs[0]["solr_join_id"] . '")';

            }

            $queryArray["query"] = $streamSourceString;
            //$queryArray["collection"] = $clauseArray['collection'];
            $queryArray["collection"] = "join_stream";

        } // If the operator for for full text searching, then it will be: { operator : { field : value } }
        elseif (isset($this->FULLTEXT_OPERATORS[$operatorOrField])) {

            $clauseArray = $this->processTextSearch($operatorOrField, $rightHandValue);

            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_join_id"] . "," . $this->entitySpecs[0]["solr_key_id"], "sort" => $clauseArray["e"]["solr_join_id"] . ' asc', "qt" => "/export", "df" => $clauseArray["df"]);



            $queryArray["query"] = $streamArray;
            $queryArray["collection"] = $clauseArray['c'];

        } // Otherwise it is not an operator, but a regular equality pair: { field : value } or { field : [ values, ... ] }
        else {
            $clauseArray = $this->processPair('_eq', $criterion);

            $streamArray = array("q" => $clauseArray["q"], "fl" => $clauseArray["e"]["solr_join_id"] . "," . $this->entitySpecs[0]["solr_key_id"], "sort" => $clauseArray["e"]["solr_join_id"] . ' asc', "qt" => "/export", "df" => $clauseArray["df"]);

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
        if ($level == 0 && $this->useSecondary) {
            $queryArray["query"] = str_replace($this->entitySpecs[0]["solr_key_id"], $this->secondaryField, $queryArray["query"]);
        }
        return $queryArray;
    }

    /**
     * @param $operator
     * @param $criterion
     * @return array
     * @throws ErrorException
     */
    private function processSimplePair($operator, $criterion)
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
            //if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
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
                $val= replaceMinusSign($val);
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


        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"], "df" => $dbField);

    }

    /**
     * @param $apiField
     * @return array
     */
    private function getDBInfo($apiField)
    {
        $dbFieldInfo = getDBField($this->fieldSpecs, $apiField);
        $dbField = $dbFieldInfo["dbField"];
        //$solr_collection = $this->primatyEntity["solr_collection"];
        $entity = $dbFieldInfo["entity_name"];
        $entitySpec = getEntitySpecs($this->entitySpecs, $entity);
        $solr_collection = $entitySpec["solr_collection"];
        $secondaryUsage = false;
        if (array_key_exists("secondarySource", $entitySpec)) {
            $secondaryUsage = true;
            $this->useSecondary = true;
            $this->secondaryField = $entitySpec["secondary_key_id"];
        }


        return array("dbField" => $dbField, "solr_collection" => $solr_collection, "entity" => $entitySpec, "secondaryUsage" => $secondaryUsage, "df" => $dbField);
    }

    /**
     * @param $operator
     * @param $criterion
     * @return array
     * @throws ErrorException
     */
    private
    function processRangePair($operator, $criterion)
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
                $val = replaceMinusSign($val);
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

        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"], "df" => $dbField);

    }

    /**
     * @param $operator
     * @param $criterion
     * @return array
     * @throws ErrorException
     */
    private
    function processStringPair($operator, $criterion)
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
            if ($datatype == 'string') {
                $val = replaceMinusSign($val);
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

        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"], "df" => $dbField);
    }

    /**
     * @param $operator
     * @param $criterion
     * @return array
     * @throws ErrorException
     */
    private
    function processTextSearch($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        $dbFieldInfo = $this->getDBInfo($apiField);
        $dbField = $dbFieldInfo["dbField"];
        $solr_collection = $dbFieldInfo["solr_collection"];

        if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
            $val = current($criterion);


            if ($this->fieldSpecs[$apiField]['datatype'] != 'fulltext') {
                ErrorHandler::getHandler()->sendError(400, "The operation '$operator' is not valid on '$apiField''.");
                throw new ErrorException("The operation '$operator' is not valid on '$apiField'' . ");
            }

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
        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"], "df" => $dbField);


    }

    /**
     * @param $operator
     * @param $criterion
     * @return array
     * @throws ErrorException
     */
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
                if ($datatype == 'int') {
                    foreach ($val as $singleVal) {
                        if (!is_numeric($singleVal)) {
                            ErrorHandler::getHandler()->sendError(400, "Invalid integer value provided: $singleVal.");
                            throw new ErrorException("Invalid date provided: $singleVal.");
                        }
                    }
                    $returnString = "$dbField : (" . implode(" OR ", $val) . ")";
                } elseif ($datatype == 'float') {
                    foreach ($val as $singleVal) {
                        if (!is_float($singleVal)) {
                            ErrorHandler::getHandler()->sendError(400, "Invalid float value provided: $singleVal.");
                            throw new ErrorException("Invalid date provided: $singleVal.");
                        }
                    }
                    $returnString = "$dbField : (" . implode(" OR ", $val) . ")";
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
                    foreach ($val as &$singleVal){
                        $singleVal = replaceMinusSign($singleVal);
                    }

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


        return array("q" => $returnString, "c" => $solr_collection, "e" => $dbFieldInfo["entity"], "s" => $dbFieldInfo["secondaryUsage"], "df" => $dbField);
    }
}


/**
 * @param $clauses
 * @param $join
 * @param $field
 * @return string
 */
function processConjugation($clauses, $join, $field)
{
    if (count($clauses) == 1) {
        return $clauses[0];
    }
    $streamDecorator = "intersect";
    if ($join == "OR") {
        $field = $field . " asc";
        $streamDecorator = "merge";

    }
    $baseStreamDecorator = $streamDecorator . '(' . implode(",", array_slice($clauses, 0, 2)) . ',on="' . $field . '")';
    //$baseStreamDecorator=$clauses[0];
    for ($i = 2; $i < count($clauses); $i++) {
        $baseStreamDecorator = $streamDecorator . '(' . $baseStreamDecorator . ',' . $clauses[$i] . ',on="' . $field . '")';

    }
    return $baseStreamDecorator;
}

/**
 * @param array $entitySpecs
 * @param array $fieldSpecs
 * @param array|null $fieldsParam
 * @return array
 */
function parseFieldList(array $entitySpecs, array $fieldSpecs, array $fieldsParam = null)
{
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        try {
            $current_entity = getEntitySpecs($entitySpecs, $fieldSpecs[$fieldsParam[$i]]["entity_name"]);
            if (!array_key_exists($current_entity["entity_name"], $returnFieldSpecs)) {
                $returnFieldSpecs[$current_entity["entity_name"]] = array();
                $returnFieldSpecs[$current_entity["entity_name"]][$entitySpecs[0]["solr_key_id"]] = $fieldSpecs[$entitySpecs[0]["solr_key_id"]];
            }

            $returnFieldSpecs[$current_entity["entity_name"]][$fieldsParam[$i]] = $fieldSpecs[$fieldsParam[$i]];
        } catch (Exception $e) {
            ErrorHandler::getHandler()->sendError(400, 'Invalid field specified: ' . $fieldsParam[$i], $e);
        }

    }


    return $returnFieldSpecs;
}

function replaceMinusSign($queryValue)
{
    if (substr($queryValue, 0, 1) === "-"){
        $queryValue = "\\".$queryValue;
    }
    return $queryValue;
}