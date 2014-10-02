<?php

require_once dirname(__FILE__) . '/fieldSpecs.php';


class QueryParser
{
    private $COMPARISON_OPERATORS = array('_eq' => '=', '_neq' => '<>', '_gt' => '>', '_gte' => '>=', '_lt' => '<', '_lte' => '<=');
    private $STRING_OPERATORS = array('_text_all' => '', '_text_any' => '', '_text_phrase' => '');
    private $JOIN_OPERATORS = array('_and' => 'and', '_or' => 'or');
    private $NEGATION_OPERATORS = array('_not' => 'not');

    private $fieldsUsed;
    private $whereClause;

    public function getFieldsUsed()
    {
        return $this->fieldsUsed;
    }

    public function getWhereClause()
    {
        return $this->whereClause;
    }

    public function parse(array $query)
    {
        $this->whereClause = '';
        $this->fieldsUsed = array();
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
        } // If the operator is a join, then the right hand value will be a list of criteria: { operator : [ criterion, ... ] }
        elseif (isset($this->JOIN_OPERATORS[$operatorOrField])) {
            $returnString .= '(';
            for ($i = 0; $i < count($rightHandValue); $i++) {
                if ($i > 0) {
                    $joinString = $this->JOIN_OPERATORS[$operatorOrField];
                    $returnString .= " $joinString ";
                }
                $returnString .= $this->processQueryCriterion($rightHandValue[$i]);
            }
            $returnString .= ')';
        } // If the operator is a negation, then the right hand value will be a criterion: { operator : { criterion } }
        elseif (isset($this->NEGATION_OPERATORS[$operatorOrField])) {
            $notString = $this->NEGATION_OPERATORS[$operatorOrField];
            $rightHandString = $this->processQueryCriterion($rightHandValue);
            $returnString .= "$notString $rightHandString";
        } // If the operator for for full text searching, then it will be: { operator : { field : value } }
        elseif (isset($this->STRING_OPERATORS[$operatorOrField])) {
            $returnString .= $this->processTextSearch($operatorOrField, $rightHandValue);
        } // Otherwise it is not an operator, but a regular equality pair: { field : value } or { field : [ values, ... ] }
        else {
            $returnString .= $this->processPair('_eq', $criterion);
        }

        return $returnString;
    }


    private function processPair($operator, $criterion)
    {
        reset($criterion);
        $apiField = key($criterion);
        $val = current($criterion);
        $dbField = getDBField($apiField);

        // If of the type: { field : value }
        if (!is_array($val)) {
            $returnString = $this->processSimplePair($operator, $criterion);
        } // Else of the type { field : [value,...] }
        else {
            if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
            // TODO Need to check the field type and conditionally wrap the value in quotes. Or format as valid date.
            $returnString = "($dbField in ['" . implode("', '", $val) . "'])";
        }

        return $returnString;
    }


    private function processSimplePair($operator, $criterion)
    {
        reset($criterion);
        $apiField = key($criterion);
        $val = current($criterion);
        $dbField = getDBField($apiField);
        if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
        $operatorString = $this->COMPARISON_OPERATORS[$operator];
        // TODO Need to check the field type and conditionally wrap the value in quotes. Or format as valid date.
        return "($dbField $operatorString '$val')";
    }

    private function processTextSearch($operator, $criterion)
    {
        $returnString = '';
        reset($criterion);
        $apiField = key($criterion);
        $val = current($criterion);
        $dbField = getDBField($apiField);
        if (!in_array($apiField, $this->fieldsUsed)) $this->fieldsUsed[] = $apiField;
        if ($operator == '_text_phrase') {
            $returnString = "match ($dbField) against ('\"$val\"' in boolean mode)";
        } elseif ($operator == '_text_any') {
            $returnString = "match ($dbField) against ('$val' in boolean mode)";
        } elseif ($operator == '_text_all') {
            $val = '+' . $val;
            $val = str_replace(' ', ' +', $val);
            $returnString = "match ($dbField) against ('$val' in boolean mode)";
        }

        return $returnString;
    }

}