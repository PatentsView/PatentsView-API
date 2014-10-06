<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/fieldSpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


class DatabaseQuery
{

    private $groupVars = array(
        array('single' => 'patent', 'hasId' => 'alreadyHasPatentId', 'hasFields' => 'hasPatentFields', 'keyId' => 'patent_id', 'table' => 'patent', 'join'=>''),
        array('single' => 'inventor', 'hasId' => 'alreadyHasInventorId', 'hasFields' => 'hasInventorFields', 'keyId' => 'inventor_id', 'table' => 'inventor_flat', 'join'=>'left outer JOIN patent_inventor ON patent.id=patent_inventor.patent_id left outer JOIN inventor_flat ON patent_inventor.inventor_id=inventor_flat.inventor_id'),
        array('single' => 'assignee', 'hasId' => 'alreadyHasAssigneeId', 'hasFields' => 'hasAssigneeFields', 'keyId' => 'assignee_id', 'table' => 'assignee_flat', 'join'=>'left outer join patent_assignee on patent.id=patent_assignee.patent_id left outer join assignee_flat on patent_assignee.assignee_id=assignee_flat.assignee_id'),
        array('single' => 'application', 'hasId' => 'alreadyHasApplicationId', 'hasFields' => 'hasApplicationFields', 'keyId' => 'application_id', 'table' => 'application', 'join'=>'left outer join application on patent.id=application.patent_id'),
        array('single' => 'ipc', 'hasId' => 'alreadyHasIPCId', 'hasFields' => 'hasIPCFields', 'keyId' => 'ipc_id', 'table' => 'ipcr', 'join'=>'left outer join ipcr on patent.id=ipcr.patent_id'),
        array('single' => 'applicationcitation', 'hasId' => 'alreadyHasApplicationCitationId', 'hasFields' => 'hasApplicationCitationFields', 'keyId' => 'applicationcitation_id', 'table' => 'usapplicationcitation', 'join'=>'left outer join usapplicationcitation on patent.id=usapplicationcitation.patent_id'),
        array('single' => 'patentcitation', 'hasId' => 'alreadyHasPatentCitationId', 'hasFields' => 'hasPatentCitationFields', 'keyId' => 'patentcitation_id', 'table' => 'uspatentcitation', 'join'=>'left outer join uspatentcitation on patent.id=uspatentcitation.patent_id'),
        array('single' => 'uspc', 'hasId' => 'alreadyHasUSPCId', 'hasFields' => 'hasUSPCFields', 'keyId' => 'uspc_id', 'table' => 'uspc_flat', 'join'=>'left outer join uspc_flat on patent.id=uspc_flat.uspc_patent_id')
    );

    private $selectFieldSpecs;
    private $sortFieldsUsed;

    private $db = null;
    private $errorHandler = null;

    public function queryDatabase($whereClause, array $whereFieldsUsed, array $selectFieldSpecs, array $sortParam=null, array $options=null)
    {
        global $FIELD_SPECS;
        $page = 1;
        $perPage = 25;
        $getAll = false;
        $this->sortFieldsUsed = array();

        $this->errorHandler = ErrorHandler::getHandler();

        $this->selectFieldSpecs = $selectFieldSpecs;
        $this->initializeGroupVars();
        $this->determineSelectFields();
        $selectString = $this->buildSelectString();
        $sortString = $this->buildSortString($sortParam);
        if ($sortString != '') $sortString .= ', ';
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
            // Get the patentIDs
            $selectPatentIdsString = "distinct " . getDBField($this->groupVars[0]['keyId']) . " as " .
                $this->groupVars[0]['keyId'];
            $results = $this->runQuery("distinct $selectPatentIdsString", $from, $whereClause, $sortString);

            // Make sure they asked for a valid range.
            if (($page-1)*$perPage > count($results))
                $results = null;
            else {
                $patentIDs = array();
                foreach (array_slice($results, ($page - 1) * $perPage, $perPage) as $row)
                    $patentIDs[] = $row['patent_id'];

                $whereClause = 'patent.id in ("' . join('","', $patentIDs) . '") ';
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
        $sqlQuery = "SELECT $select FROM $from $where ORDER BY $order patent.id";
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
                if ($dbInfo['table_name'] == $group['table'])
                    $this->{$group['hasFields']} = true;
            }
        }
    }


    private function determineSelectFields()
    {
        global $FIELD_SPECS;

        foreach ($this->groupVars as $group) {
            if ($group['single'] == 'patent') {
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
        global $FIELD_SPECS;
        $selectString = '';

        foreach ($this->selectFieldSpecs as $apiField => $dbInfo) {
            if ($selectString != '')
                $selectString .= ', ';
            $selectString .= "$dbInfo[table_name].$dbInfo[field_name] as $apiField";
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
                }
                if ($fieldSpec['table_name'] == 'patent') {
                    if (($direction != 'asc') and ($direction != 'desc'))
                        ErrorHandler::getHandler()->sendError(400, "Not a valid direction for sorting: $direction");
                    else {
                        if ($orderString != '')
                            $orderString .= ', ';
                        $orderString .= getDBField($apiField) . ' ' . $direction;
                        $this->sortFieldsUsed[] = $apiField;
                    }
                } else {
                    ErrorHandler::getHandler()->sendError(400, "Not a valid field for sorting, it must be a patent field: $apiField");
                }
            }
        }
        return $orderString;
    }

    private function buildFrom(array $whereFieldsUsed, array $selectFieldSpecs, array $sortFields)
    {
        global $FIELD_SPECS;
        // Smerge all the fields into one array
        $allFieldsUsed = array_merge($whereFieldsUsed, array_keys($selectFieldSpecs), $sortFields);
        $allFieldsUsed = array_unique($allFieldsUsed);

        $fromString = $this->groupVars[0]['table'];
        $tablesAdded = array();

        foreach ($allFieldsUsed as $apiField) {
            if (!in_array($FIELD_SPECS[$apiField]['table_name'], $tablesAdded)) {
                foreach ($this->groupVars as $group) {
                    if ($group['table'] == $FIELD_SPECS[$apiField]['table_name'])
                        $fromString .= ' ' . $group['join'] . ' ';
                }
                $tablesAdded[] = $FIELD_SPECS[$apiField]['table_name'];
            }
        }
        return $fromString;
    }

}