<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/entitySpecs.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


class DatabaseQuery
{
    private $entityTotalCounts = array();

    private $entitySpecs = array();
    private $entityGroupVars = array();
    private $fieldSpecs;
    private $entitySpecificWhereClauses = array();

    private $selectFieldSpecs;
    private $sortFieldsUsed;
	private $sortFieldsUsedSec;
	    
    private $db = null;
    private $errorHandler = null;

    private $matchedSubentitiesOnly = true;
    private $include_subentity_total_counts = false;

    private $supportDatabase = "";

    public function getTotalCounts()
    {
        return $this->entityTotalCounts;
    }

    public function queryDatabase(array $entitySpecs, array $fieldSpecs, $whereClause, array $whereFieldsUsed, array $entitySpecificWhereClauses, $onlyAndsWereUsed, array $selectFieldSpecs, array $sortParam=null, array $options=null)
    {
        global $config;
        $dbSettings = $config->getDbSettings();
        $this->supportDatabase = $dbSettings['supportDatabase'];

        $memUsed = memory_get_usage();
        $this->errorHandler = ErrorHandler::getHandler();
        $page = 1;
        $perPage = 25;
        $this->sortFieldsUsed = array();
        $this->sortFieldsUsedSec = array();
        $this->entitySpecificWhereClauses = $entitySpecificWhereClauses;

        $this->entitySpecs = $entitySpecs;
        $this->fieldSpecs = $fieldSpecs;
        $this->setupGroupVars();

        if ($options != null) {
            if (array_key_exists('page', $options)) {
                $page = $options['page'];
            }
            if (array_key_exists('per_page', $options))
                if (($options['per_page'] > $config->getMaxPageSize()) or ($options['per_page'] < 1))
                    $this->errorHandler->sendError(400, "Per_page must be a positive number not to exceed " . $config->getMaxPageSize() . ".", $options);
                else
                    $perPage = $options['per_page'];
            if (array_key_exists('matched_subentities_only', $options)) {
                $this->matchedSubentitiesOnly = strtolower($options['matched_subentities_only']) === 'true';
                # When the matched_subentities_only option is used, we need to check that all the criteria were 'and'ed together
//                if ($this->matchedSubentitiesOnly && !$onlyAndsWereUsed)
//                    $this->errorHandler->sendError(400, "When using the 'matched_subentities_only' option, the query criteria cannot contain any 'or's.", $options);
            }
            if (array_key_exists('include_subentity_total_counts', $options)) {
                $this->include_subentity_total_counts = strtolower($options['include_subentity_total_counts']) === 'true';
            }
        }

        $this->selectFieldSpecs = $selectFieldSpecs;
        $this->initializeGroupVars();
        $this->determineSelectFields();
        $from = $this->buildFrom($whereFieldsUsed, $this->selectFieldSpecs, $this->sortFieldsUsed);
        $sortString = $this->buildSortString($sortParam);
        
	// Get the QueryDefId for this where clause
        $stringToHash = "key->" . $this->entitySpecs[0]['keyId'] . "::query->$whereClause::sort->$sortString";
        $whereHash = crc32($stringToHash);   // Using crc32 rather than md5 since we only have 32-bits to work with.
        $queryDefId = sprintf('%u', $whereHash);

        $county = 0;
		$maxTries = 3;
		do {
			try {
			// If the query results for this where clause don't already exist, then we need to run the
        		// query and cache the primary entity IDs.
        		$results = $this->runQuery('QueryDefID, QueryString', $this->supportDatabase . '.QueryDef', "QueryDefID=$queryDefId", null);
        		//TODO Need to handle a hash collision
        		if (count($results) == 0) {
            		// Add an entry for the query
            		
                		$this->startTransaction();
                		$insertStatement = $this->supportDatabase . '.QueryDef (QueryDefId, QueryString) VALUES (:queryDefId, :whereClause)';
                		$this->runInsert($insertStatement, array(':queryDefId' => $queryDefId, ':whereClause' => $stringToHash));

                		// Get all the primary entity IDs and insert into the cached results table
                		#Todo: Optimization issue: when there is no where clause perhaps we should disallow it, otherwise it can be really slow depending on the primary entity. For patents on the full DB it takes over 7m - stopped waiting.
                		$insertStatement = $this->supportDatabase . '.QueryResults (QueryDefId, Sequence, EntityId)';
                		$selectPrimaryEntityIdsString =
                    		"$queryDefId, @row_number:=@row_number+1 as sequence, XX.XXid as " . $this->entityGroupVars[0]['keyId'];
                		if (strlen($whereClause) > 0) $whereInsert = "WHERE $whereClause "; else $whereInsert = "";
                		if (strlen($sortString) > 0) $sortInsert = "ORDER BY $sortString "; else $sortInsert = '';
                		$fromInsert = $this->buildFrom($whereFieldsUsed, array($entitySpecs[0]['keyId'] => $this->fieldSpecs[$entitySpecs[0]['keyId']]), $this->sortFieldsUsed);
                		$this->runInsertSelect($insertStatement,
                    		$selectPrimaryEntityIdsString,
                    		'(SELECT distinct ' . getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']) . ' as XXid FROM ' .
                    		$fromInsert . ' ' . $whereInsert . $sortInsert . ' limit ' . $config->getQueryResultLimit() . ') XX, (select @row_number:=0) temprownum',
                    		null,
                    		null);
                		$this->commitTransaction();
                		break;
            		}
			}
			catch (Exception $e) {
				$this->rollbackTransaction();
				$county++;
				if ($county==$maxTries) {
					throw new $e;
					break; }
				usleep(500000);
				continue;	
			}
		break;
		
	} while($county < $maxTries);

        // First find out how many there are in the complete set.
        $selectStringForEntity = 'count(QueryDefId) as total_found';
        $fromEntity = $this->supportDatabase . '.QueryResults qr';
        $whereEntity = "qr.QueryDefId=$queryDefId";
        $countResults = $this->runQuery($selectStringForEntity, $fromEntity, $whereEntity, null);
        $this->entityTotalCounts[$entitySpecs[0]['entity_name']] = intval($countResults[0]['total_found']);


        // Get the primary entities
        $results = array();
        $selectStringForEntity = $this->buildSelectStringForEntity($this->entitySpecs[0]);
        $fromEntity = $this->entitySpecs[0]['join'] .
            ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';
        $whereEntity = "qr.QueryDefId=$queryDefId";
        if ($perPage < $this->entityTotalCounts[$entitySpecs[0]['entity_name']])
            $whereEntity .= ' and ((qr.Sequence>=' . ((($page - 1)*$perPage)+1) . ') and (qr.Sequence<=' . $page*$perPage . '))';
        $sortEntity = 'qr.sequence';
        $entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, $sortEntity);
        $results[$this->entitySpecs[0]['group_name']] = $entityResults;
	unset($entityResults);
	
        // Loop through the subentities and get them.
        foreach (array_slice($this->entitySpecs,1) as $entitySpec) {
            $tempSelect = $this->buildSelectStringForEntity($entitySpec);
	    if ($tempSelect != '') { // If there aren't any fields to get back, then skip the group.
                $selectStringForEntity = getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . ' as ' . $this->entitySpecs[0]['keyId'];
                $selectStringForEntity .= ", $tempSelect";
                $fromEntity = $this->entitySpecs[0]['join'] .
                    ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';
                $fromEntity .= ' ' . $entitySpec['join'];
                $whereEntity = "qr.QueryDefId=$queryDefId";
                if ($perPage < $this->entityTotalCounts[$entitySpecs[0]['entity_name']])
                    $whereEntity .= ' and ((qr.Sequence>=' . ((($page - 1)*$perPage)+1) . ') and (qr.Sequence<=' . $page*$perPage . '))';
                if ($this->matchedSubentitiesOnly && array_key_exists($entitySpec['entity_name'], $this->entitySpecificWhereClauses) && $this->entitySpecificWhereClauses[$entitySpec['entity_name']] != '')
                    $whereEntity .= ' and ' . $this->entitySpecificWhereClauses[$entitySpec['entity_name']];
		
		if (array_key_exists($entitySpec['group_name'],$this->sortFieldsUsedSec)) {
			$sortStringSec = implode(',',$this->sortFieldsUsedSec[$entitySpec['group_name']]);
			$entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, $sortStringSec);
		} else {
			$entityResults = $this->runQuery("distinct $selectStringForEntity", $fromEntity, $whereEntity, null);
		}
		$results[$entitySpec['group_name']] = $entityResults;
		unset($entityResults);

                if ($this->include_subentity_total_counts) {
                    // Count of all subentities for all primary entities.
                    $selectStringForEntity = 'count(distinct ' . getDBField($this->fieldSpecs, $entitySpec['distinctCountId']) . ') as subentity_count';
                    $fromEntity = $this->entitySpecs[0]['join'] .
                        ' inner join ' . $this->supportDatabase . '.QueryResults qr on ' . getDBField($this->fieldSpecs, $this->entitySpecs[0]['keyId']) . '= qr.EntityId';
                    $fromEntity .= ' ' . $entitySpec['join'];
                    $whereEntity = "qr.QueryDefId=$queryDefId";
                    $countResults = $this->runQuery($selectStringForEntity, $fromEntity, $whereEntity, null);
                    $this->entityTotalCounts[$entitySpec['entity_name']] = intval($countResults[0]['subentity_count']);
                }
            }
        }
	return $results;
    }

    private function runQuery($select, $from, $where, $order)
    {
        $this->connectToDB();

        if (strlen($where) > 0) $where = "WHERE $where ";
        if (strlen($order) > 0) $order = "ORDER BY $order";
        $sqlQuery = "SELECT $select FROM $from $where $order";
        $this->errorHandler->getLogger()->debug($sqlQuery);
	
	try {
            $st = $this->db->query("$sqlQuery", PDO::FETCH_ASSOC);
            $results = $st->fetchAll();
            $st->closeCursor();
        }
        catch (Exception $e) {
            $this->errorHandler->sendError(500, "Query execution failed.", $e);
            throw new $e;
        }

        return $results;
    }

    private function runInsertSelect($insert, $select, $from, $where, $order)
    {
        $this->connectToDB();

        if (strlen($where) > 0) $where = "WHERE $where ";
        if (strlen($order) > 0) $order = "ORDER BY $order";
        $sqlQuery = "INSERT INTO $insert SELECT $select FROM $from $where $order";
        $this->errorHandler->getLogger()->debug($sqlQuery);

        try {
            $st = $this->db->prepare($sqlQuery);
            $results = $st->execute();
            $st->closeCursor();
        }
        catch (Exception $e) {
            $this->errorHandler->sendError(500, "Insert select execution failed.", $e);
            throw new $e;
        }

        return $results;
    }

    private function runInsert($insert, $params)
    {
        $this->connectToDB();

        $sqlStatement = "INSERT INTO $insert";
        $this->errorHandler->getLogger()->debug($sqlStatement);
        $this->errorHandler->getLogger()->debug($params);
	
	$counto = 0;
	$maxTriesy = 3;
	do {
    	    try {        
    		$st = $this->db->prepare($sqlStatement);
            	$results = $st->execute($params);
            	$st->closeCursor();
		break;
        	}
            catch (Exception $e) {
            	if ($counto==$maxTriesy) {
			$this->errorHandler->sendError(500, "Insert execution failed.", $e);		
			throw new $e;
			break;}
	    	usleep(1000000);
	    	continue;
        	}
	break;
	     } while(counto<maxTriesy);

        return $results;
    }

    private function initializeGroupVars()
    {
        foreach ($this->entityGroupVars as $group) {
            $this->{$group['hasId']} = false;
            $this->{$group['hasFields']} = false;
        }

        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            foreach ($this->entityGroupVars as $group) {
                if ($apiField == $group['keyId'])
                    $this->{$group['hasId']} = true;
                if ($fieldInfo['entity_name'] == $group['entity_name'])
                    $this->{$group['hasFields']} = true;
            }
        }
    }


    private function determineSelectFields()
    {
        foreach ($this->entityGroupVars as $group) {
            if ($group['entity_name'] == $this->entityGroupVars[0]['entity_name']) {
                if (!$this->{$group['hasId']})
                    $this->selectFieldSpecs[$group['keyId']] = $this->fieldSpecs[$group['keyId']];
            } else {
                if ($this->{$group['hasFields']} and !$this->{$group['hasId']} and array_key_exists($group['keyId'],$this->fieldSpecs))
                    $this->selectFieldSpecs[$group['keyId']] = $this->fieldSpecs[$group['keyId']];
            }
        }
    }


    private function buildSelectString()
    {
        $selectString = '';

        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($selectString != '')
                $selectString .= ', ';
            $selectString .= getDBField($this->fieldSpecs, $apiField) . " as $apiField";
        }

        return $selectString;
    }

    private function buildSelectStringForEntity($entitySpec)
    {
        $selectString = '';

        foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
            if ($fieldInfo['entity_name'] == $entitySpec['entity_name']) {
                if ($selectString != '')
                    $selectString .= ', ';
                $selectString .= getDBField($this->fieldSpecs, $apiField) . " as $apiField";
            }
        }

        return $selectString;
    }


    private function buildSortString($sortParam)
    {
        $orderString = '';
	if ($sortParam != null) {
	    foreach ($sortParam as $sortField) {
		foreach($sortField as $apiField=>$direction) {
                try {
                    $fieldSpec = $this->fieldSpecs[$apiField];
                }
                catch (ErrorException $e) {
                    ErrorHandler::getHandler()->sendError(400, "Invalid field for sorting: $apiField");
                    throw $e;
                }
                if (strtolower($fieldSpec['sort']) == 'y') {
                    if (($direction != 'asc') and ($direction != 'desc')) {
                        ErrorHandler::getHandler()->sendError(400, "Not a valid direction for sorting: $direction");
                        throw new ErrorException("Not a valid direction for sorting: $direction");
                    }
                    else {
                        if ($orderString != '')
                            $orderString .= ', ';
                        $orderString .= getDBField($this->fieldSpecs, $apiField) . ' ' . $direction;
                        $this->sortFieldsUsed[] = $apiField;
                    }
                } elseif (strtolower($fieldSpec['sort']) == 'suppl') {
		    if (($direction != 'asc') and ($direction != 'desc')) {
                        ErrorHandler::getHandler()->sendError(400, "Not a valid direction for sorting: $direction");
                        throw new ErrorException("Not a valid direction for sorting: $direction");
                    }
                    else {
                        if ($orderString != '')
                            $orderString .= ', ';
                        $orderString .= getDBField($this->fieldSpecs, $apiField) . ' ' . $direction;
			$this->sortFieldsUsed[] = $apiField;
			$secEntityField = $fieldSpec['entity_name'];
			$secEntityField .= "s";
			if (array_key_exists($secEntityField,$this->sortFieldsUsedSec)) {
				array_push($this->sortFieldsUsedSec[$secEntityField],getDBField($this->fieldSpecs, $apiField) . ' ' . $direction);
			} else {
				$this->sortFieldsUsedSec[$secEntityField] = array(getDBField($this->fieldSpecs, $apiField) . ' ' . $direction);
			}
                    } 
                } else {
                    $msg = "Not a valid field for sorting: $apiField";
                    ErrorHandler::getHandler()->sendError(400, $msg);
                    throw new ErrorException($msg);
                }}
            }
        }

        if ($orderString != '')
            $orderString .= ', ';
        $orderString .= getDBField($this->fieldSpecs, $this->entityGroupVars[0]['keyId']);
	return $orderString;
    }

    private function buildFrom(array $whereFieldsUsed, array $selectFieldSpecs, array $sortFields)
    {
        // Smerge all the fields into one array
        $allFieldsUsed = array_merge($whereFieldsUsed, array_keys($selectFieldSpecs), $sortFields);
        $allFieldsUsed = array_unique($allFieldsUsed);
        $fromString = '';
        $joins = array();

        // We need to go through the entities in order so the joins are done in the same order as they appear
        // in the entity specs.
        foreach ($this->entityGroupVars as $group)
            foreach ($allFieldsUsed as $apiField)
                if ($group['entity_name'] == $this->fieldSpecs[$apiField]['entity_name'])
                    if (!in_array($group['join'], $joins))
                        $joins[] = $group['join'];

        foreach ($joins as $join) {
            $fromString .= ' ' . $join . ' ';
        }

        return $fromString;
    }

    private function setupGroupVars()
    {

        $this->entityGroupVars = $this->entitySpecs;
        foreach ($this->entityGroupVars as &$group) {
            $name = $group['entity_name'];
            $group['hasId'] = "alreadyHas{$name}Id";
            $group['hasFields'] = "has{$name}Fields";
        }
        unset($group);
   }

    private function connectToDB()
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
    }

    private function startTransaction()
    {
        $this->db->beginTransaction();
    }

    private function commitTransaction()
    {
        $this->db->commit();
    }

    private function rollbackTransaction() {
        $this->db->rollback();
    }

    
}