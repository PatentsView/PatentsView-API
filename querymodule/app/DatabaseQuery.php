<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


class DatabaseQuery
{

    private $entitySpecs = array();
    private $groupVars = array();

    private $selectFieldSpecs;
    private $sortFieldsUsed;

    private $db = null;
    private $errorHandler = null;

    public function queryDatabase(array $entitySpecs, $whereClause, array $whereFieldsUsed, array $selectFieldSpecs, array $sortParam=null, array $options=null)
    {
        $page = 1;
        $perPage = 25;
        $getAll = false;
        $this->sortFieldsUsed = array();
        $this->entitySpecs = $entitySpecs;

        $this->setupGroupVars();

        $this->errorHandler = ErrorHandler::getHandler();

        $this->selectFieldSpecs = $selectFieldSpecs;
        $this->initializeGroupVars();
        $this->determineSelectFields();
        $selectString = $this->buildSelectString();
        $sortString = $this->buildSortString($sortParam);
        $from = $this->buildFrom($whereFieldsUsed, $this->selectFieldSpecs, $this->sortFieldsUsed);

        if ($options != null) {
            if (array_key_exists('page', $options)) {
                if ($options['page'] == -1)
                    $getAll = true;
                else
                    $page = $options['page'];
            }
            if (array_key_exists('per_page', $options))
                $perPage = $options['per_page'];
        }

        // If getAll, then just get all the data.
        if ($getAll) {
            $results = $this->runQuery("distinct $selectString", $from, $whereClause, $sortString);
        }
        // If get a range, then first get all the IDs, and then get the IDs in that range and use
        // as the WHERE to get the data rows
        else {
            // Get the primary entity IDs
            $selectPrimaryEntityIdsString = "distinct " . getDBField($this->groupVars[0]['keyId']) . " as " .
                $this->groupVars[0]['keyId'];
            $results = $this->runQuery("distinct $selectPrimaryEntityIdsString", $from, $whereClause, $sortString);

            // Make sure they asked for a valid range.
            if (($page-1)*$perPage > count($results))
                $results = null;
            else {
                $primaryEntityIds = array();
                foreach (array_slice($results, ($page - 1) * $perPage, $perPage) as $row)
                    $primaryEntityIds[] = $row[$this->groupVars[0]['keyId']];

                $whereClause = getDBField($this->groupVars[0]['keyId']) . ' in ("' . join('","', $primaryEntityIds) . '") ';
                $selectString = "distinct $selectString";
                $results = $this->runQuery("distinct $selectString", $from, $whereClause, $sortString);
            }
        }

        return $results;
    }

    private function runQuery($select, $from, $where, $order)
    {
        global $config;

        if ($this->db === null) {
            $dbSettings = $config->getDbSettings();
            try {
                $this->db = new PDO("mysql:host=$dbSettings[host];dbname=$dbSettings[database];charset=utf8", $dbSettings['user'], $dbSettings['password']);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                $this->errorHandler->sendError(500, "Failed to connect to database: $dbSettings[database].", $e);
                throw new $e;
            }
        }

        if (strlen($where) > 0) $where = "WHERE $where ";
        $sqlQuery = "SELECT $select FROM $from $where ORDER BY $order";
        $this->errorHandler->getLogger()->debug($sqlQuery);

        try {
            $st = $this->db->query($sqlQuery, PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            $this->errorHandler->sendError(400, "Query execution failed.", $e);
            throw new $e;
        }

        $results = $st->fetchAll();
        $st->closeCursor();
        return $results;
    }

    private function initializeGroupVars()
    {
        foreach ($this->groupVars as $group) {
            $this->{$group['hasId']} = false;
            $this->{$group['hasFields']} = false;
        }

        foreach ($this->selectFieldSpecs as $apiField => $dbInfo) {
            foreach ($this->groupVars as $group) {
                if ($dbInfo['field_name'] == $group['keyId'])
                    $this->{$group['hasId']} = true;
                if ($dbInfo['table'] == $group['table'])
                    $this->{$group['hasFields']} = true;
            }
        }
    }


    private function determineSelectFields()
    {
        global $FIELD_SPECS;

        foreach ($this->groupVars as $group) {
            if ($group['name'] == $this->groupVars[0]['name']) {
                if (!$this->{$group['hasId']})
                    $this->selectFieldSpecs[$group['keyId']] = $FIELD_SPECS[$group['keyId']];
            } else {
                if ($this->{$group['hasFields']} and !$this->{$group['hasId']})
                    $this->selectFieldSpecs[$group['keyId']] = $FIELD_SPECS[$group['keyId']];
            }
        }
    }


    private function buildSelectString()
    {
        $selectString = '';

        foreach ($this->selectFieldSpecs as $apiField => $dbInfo) {
            if ($selectString != '')
                $selectString .= ', ';
            $selectString .= "$dbInfo[table].$dbInfo[field_name] as $apiField";
        }

        return $selectString;
    }


    private function buildSortString($sortParam)
    {
        global $FIELD_SPECS;
        $orderString = '';
        if ($sortParam != null) {
            foreach ($sortParam as $sortField) {
                $apiField = key($sortField);
                $direction = current($sortField);
                try {
                    $fieldSpec = $FIELD_SPECS[$apiField];
                }
                catch (ErrorException $e) {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field for sorting: $apiField");
                    throw $e;
                }
                if ($fieldSpec['table'] == $this->groupVars[0]['table']) {
                    if (($direction != 'asc') and ($direction != 'desc')) {
                        ErrorHandler::getHandler()->sendError(400, "Not a valid direction for sorting: $direction");
                        throw new ErrorException("Not a valid direction for sorting: $direction");
                    }
                    else {
                        if ($orderString != '')
                            $orderString .= ', ';
                        $orderString .= getDBField($apiField) . ' ' . $direction;
                        $this->sortFieldsUsed[] = $apiField;
                    }
                } else {
                    $msg = "Not a valid field for sorting, it must be a " . $this->groupVars[0]['name'] . " field: $apiField";
                    ErrorHandler::getHandler()->sendError(400, $msg);
                    throw new ErrorException($msg);
                }
            }
        }

        if ($orderString != '')
            $orderString .= ', ';
        $orderString .= getDBField($this->groupVars[0]['keyId']);
        return $orderString;
    }

    private function buildFrom(array $whereFieldsUsed, array $selectFieldSpecs, array $sortFields)
    {
        global $FIELD_SPECS;
        // Smerge all the fields into one array
        $allFieldsUsed = array_merge($whereFieldsUsed, array_keys($selectFieldSpecs), $sortFields);
        $allFieldsUsed = array_unique($allFieldsUsed);

        $fromString = '';
        $joins = array();

        // We need to go through the entities in order so the joins are done in the same order as they appear
        // in the entity specs.
        foreach ($this->groupVars as $group)
            foreach ($allFieldsUsed as $apiField)
                if ($group['table'] == $FIELD_SPECS[$apiField]['table'])
                    if (!in_array($group['join'], $joins))
                        $joins[] = $group['join'];

        foreach ($joins as $join) {
            $fromString .= ' ' . $join . ' ';
        }

        return $fromString;
    }

    private function setupGroupVars()
    {

        $this->groupVars = $this->entitySpecs;
        foreach ($this->groupVars as &$group) {
            $name = $group['name'];
            $group['hasId'] = "alreadyHas{$name}Id";
            $group['hasFields'] = "has{$name}Fields";
        }
   }

}