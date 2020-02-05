<?php

require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';
require_once(dirname(__FILE__) . "/PVExceptions/ParsingException.php");

class QueryParser
{
    private $COMPARISON_OPERATORS = array('_eq' => '=', '_neq' => '<>', '_gt' => '>', '_gte' => '>=', '_lt' => '<', '_lte' => '<=');
    private $STRING_OPERATORS = array('_begins' => '', '_contains' => '');
    private $FULLTEXT_OPERATORS = array('_text_all' => '', '_text_any' => '', '_text_phrase' => '');
    private $JOIN_OPERATORS = array('_and' => 'and', '_or' => 'or');
    private $NEGATION_OPERATORS = array('_not' => 'not');

    private $fieldsUsed;
    private $whereClause;
    private $entityName;
    private $onlyAndsWereUsed; #Used to keep track of whether the query criteria are only joined by ANDs.

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

    public function parse(array $fieldSpecs, array $query, $entityName)
    {
        $this->fieldSpecs = $fieldSpecs;
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

    private function processQueryCriterion(array $criterion)
    {
        $returnString = '';
        // A criterion should always be a single name-value pair
        reset($criterion);
        $operatorOrField = key($criterion);
        $rightHandValue = current($criterion);

        // If the operator is a comparison, then the right hand value will be a simple pair: { operator : { field : value } }
        if (isset($this->COMPARISON_OPERATORS[$operatorOrField])) {
            $returnString .= $this->processSimplePair($operatorOrField, $rightHandValue);
        } // If the operator is for strings, then the right hand value will be a simple pair: { operator : { field : value } }
        elseif (isset($this->STRING_OPERATORS[$operatorOrField])) {
            $returnString .= $this->processStringPair($operatorOrField, $rightHandValue);
        } // If the operator is a join, then the right hand value will be a list of criteria: { operator : [ criterion, ... ] }
        elseif (isset($this->JOIN_OPERATORS[$operatorOrField])) {
            $subReturnString = '';
            $addConjunctionNextTime = false;
            $addedSomething = false;
            for ($i = 0; $i < count($rightHandValue); $i++) {
                $nextPhrase = $this->processQueryCriterion($rightHandValue[$i]);
                if (($i > 0) && $addConjunctionNextTime && $nextPhrase) {
                    $joinString = $this->JOIN_OPERATORS[$operatorOrField];
                    $subReturnString .= " $joinString ";
                }
                $subReturnString .= $nextPhrase;
                $addConjunctionNextTime = $nextPhrase != null;
                if ($nextPhrase) $addedSomething = true;
                if ($operatorOrField == "_or")
                    $this->onlyAndsWereUsed = false;
            }
            if ($addedSomething)
                $returnString .= '(' . $subReturnString . ')';
        } // If the operator is a negation, then the right hand value will be a criterion: { operator : { criterion } }
        elseif (isset($this->NEGATION_OPERATORS[$operatorOrField])) {
            $notString = $this->NEGATION_OPERATORS[$operatorOrField];
            $rightHandString = $this->processQueryCriterion($rightHandValue);
            $returnString .= "$notString $rightHandString";
        } // If the operator for for full text searching, then it will be: { operator : { field : value } }
        elseif (isset($this->FULLTEXT_OPERATORS[$operatorOrField])) {
            $returnString .= $this->processTextSearch($operatorOrField, $rightHandValue);
        } // Otherwise it is not an operator, but a regular equality pair: { field : value } or { field : [ values, ... ] }
        else {
            $returnString .= $this->processPair('_eq', $criterion);
        }

        return $returnString;
    }

    private function processSimplePair($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        if (array_key_exists($apiField, $this->fieldSpecs)) {
            if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
                if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                    $val = current($criterion);
                    $dbField = getDBField($this->fieldSpecs, $apiField);
                    $datatype = $this->fieldSpecs[$apiField]['datatype'];
                    if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                    $operatorString = $this->COMPARISON_OPERATORS[$operator];
                    if ($datatype == 'float') {
                        if (!is_float($val)) {
                            throw new \Exceptions\ParsingException("PINV2", array($val));
                        }
                        $returnString = "($dbField $operatorString $val)";
                    } elseif ($datatype == 'int') {
                        if (!is_numeric($val)) {
                            throw new \Exceptions\ParsingException("PINV1", array($val));
                        }
                        $returnString = "($dbField $operatorString $val)";
                    } elseif ($datatype == 'date') {
                        if (!strtotime($val)) {
                            throw new \Exceptions\ParsingException("PINV3", array($val));
                        }
                        $returnString = "($dbField $operatorString '" . date('Y-m-d', strtotime($val)) . "')";

                    } elseif (($datatype == 'string') or ($datatype == 'fulltext')) {
                        $val = str_replace("'", "''", $val);
                        $returnString = "($dbField $operatorString '$val')";
                    }
                    else {

                        throw new \Exceptions\ParsingException("PINV6", array($datatype, $operator, $apiField));
                    }
                } else {
                    throw new \Exceptions\ParsingException("PINV5", array($apiField));
                }
            }
        } else {
            throw new \Exceptions\ParsingException("PINV8", array($apiField));
        }
        return $returnString;

    }

    private function processStringPair($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        if (array_key_exists($apiField, $this->fieldSpecs)) {
            if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
                if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                    $val = current($criterion);
                    $dbField = getDBField($this->fieldSpecs, $apiField);
                    $datatype = $this->fieldSpecs[$apiField]['datatype'];
                    if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                    if ($datatype == 'string') {
                        if ($operator == '_begins')
                            if (is_array($val)) {
                                $returnString = "(";
                                for ($i = 0; $i < count($val); $i++) {
                                    $val[$i] = str_replace("'", "''", $val[$i]);
                                    $returnString .= "$dbField like '$val[$i]%'";
                                    if ($i < count($val) - 1) {
                                        $returnString .= " OR ";
                                    }
                                }
                                $returnString .= ")";
                            } else {
                                $val = str_replace("'", "''", $val);
                                $returnString = "($dbField like '$val%')";
                            }
                        elseif ($operator == '_contains')
                            if (is_array($val)) {
                                $returnString = "(";
                                for ($i = 0; $i < count($val); $i++) {
                                    $val[$i] = str_replace("'", "''", $val[$i]);
                                    $returnString .= "$dbField like '%$val[$i]%'";
                                    if ($i < count($val) - 1) {
                                        $returnString .= " OR ";
                                    }
                                }
                                $returnString .= ")";
                            } else {
                                $val = str_replace("'", "''", $val);
                                $returnString = "($dbField like '%$val%')";
                            }
                    } else {
                        throw new \Exceptions\ParsingException("PINV6", array($datatype, $operator, $apiField));
                    }
                } else {
                    throw new \Exceptions\ParsingException("PINV5", array($apiField));
                }
            }
        } else {
            throw new \Exceptions\ParsingException("PINV8", array($apiField));
        }
        return $returnString;
    }

    private function processTextSearch($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        if (array_key_exists($apiField, $this->fieldSpecs)) {
            if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
                if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                    $val = current($criterion);
                    $dbField = getDBField($this->fieldSpecs, $apiField);

                    if ($this->fieldSpecs[$apiField]['datatype'] != 'fulltext') {
                        throw new \Exceptions\ParsingException("PINV7", array($operator, $apiField));
                    }

                    if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                    if ($operator == '_text_phrase') {
                        $returnString = "match ($dbField) against ('\"$val\"' in boolean mode)";
                    } elseif ($operator == '_text_any') {
                        $returnString = "match ($dbField) against ('$val' in boolean mode)";
                    } elseif ($operator == '_text_all') {
                        $val = '+' . $val;
                        $val = str_replace(' ', ' +', $val);
                        $returnString = "match ($dbField) against ('$val' in boolean mode)";

                    } else
                        throw new \Exceptions\ParsingException("PINV5", array($apiField));
                }
            }
        } else {
            throw new \Exceptions\ParsingException("PINV8", array($apiField));
        }
        return $returnString;
    }

    private function processPair($operator, $criterion)
    {
        reset($criterion);
        $returnString = null;
        $apiField = key($criterion);
        if (array_key_exists($apiField, $this->fieldSpecs)) {
            if (($this->entityName == 'all') || ($this->fieldSpecs[$apiField]['entity_name'] == $this->entityName)) {
                if (strtolower($this->fieldSpecs[$apiField]['query']) === 'y') {
                    $val = current($criterion);
                    $dbField = getDBField($this->fieldSpecs, $apiField);
                    $datatype = $this->fieldSpecs[$apiField]['datatype'];
                    // If of the type: { field : value }
                    if (!is_array($val)) {
                        $returnString = $this->processSimplePair($operator, $criterion);
                    } // Else of the type { field : [value,...] }
                    else {
                        if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
                        if ($datatype == 'int') {
                            foreach ($val as $singleVal) {
                                if (!is_numeric($singleVal)) {
                                    throw new \Exceptions\ParsingException("PINV1", array($singleVal));
                                }
                            }
                            $returnString = "($dbField in (" . implode(", ", $val) . "))";
                        } elseif ($datatype == 'float') {
                            foreach ($val as $singleVal) {
                                if (!is_float($singleVal)) {
                                    throw new \Exceptions\ParsingException("PINV2", array($singleVal));
                                }
                            }
                            $returnString = "($dbField in (" . implode(", ", $val) . "))";
                        } elseif ($datatype == 'date') {
                            $dateVals = array();
                            foreach ($val as $singleVal) {
                                if (strtotime($singleVal))
                                    $dateVals[] = date('Y-m-d', strtotime($singleVal));
                                else {
                                    throw new \Exceptions\ParsingException("PINV3", array($singleVal));
                                }
                            }
                            $returnString = "($dbField in ('" . implode("', '", $dateVals) . "'))";
                        } elseif (($datatype == 'string') or ($datatype == 'fulltext')){
                            $val = str_replace("'", "''", $val);
                            $returnString = "($dbField in ('" . implode("', '", $val) . "'))";
                        }
                        else {
                            throw new \Exceptions\ParsingException("PINV4", array($datatype, $apiField));
                        }
                    }
                } else {
                    throw new \Exceptions\ParsingException("PINV5", array($apiField));
                }
            }
        } else {
            throw new \Exceptions\ParsingException("PINV8", array($apiField));
        }
        return $returnString;
    }
}

function parseFieldList(array $fieldSpecs, array $fieldsParam = null)
{
    $returnFieldSpecs = array();

    for ($i = 0; $i < count($fieldsParam); $i++) {
        if (array_key_exists($fieldsParam[$i], $fieldSpecs)) {
            $returnFieldSpecs[$fieldsParam[$i]] = $fieldSpecs[$fieldsParam[$i]];
        } else {
            throw new \Exceptions\ParsingException("PINV8", array($fieldsParam[$i]));
        }
    }
    return $returnFieldSpecs;
}