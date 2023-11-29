<?php
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/ErrorHandler.php';


class AddEmailDatabase
{
    private $db = null;
    private $errorHandler = null;

    private $supportDatabase = "";

    public function addEmail($email)
    {
	global $config;
        $dbSettings = $config->getDbSettings();
        $this->supportDatabase = $dbSettings['supportDatabase'];	

        $memUsed = memory_get_usage();
        $this->errorHandler = ErrorHandler::getHandler();

	// Get the QueryDefId for this where clause
        $stringToHash = $email;
        $whereHash = crc32($stringToHash);   // Using crc32 rather than md5 since we only have 32-bits to work with.
        $queryDefId = sprintf('%u', $whereHash);
	
	
	
        $county = 0;
		$maxTries = 3;
		do {
			try {
			// If the query results for this where clause don't already exist, then we need to run the
        		// query and cache the primary entity IDs.
        		$results = $this->runQuery('EmailDefId,Email', $this->supportDatabase . '.EmailDef', "EmailDefID=$queryDefId", null);
        		//TODO Need to handle a hash collision
        		if (count($results) == 0) {
            		// Add an entry for the query
            		
                		$this->startTransaction();
                		$insertStatement = $this->supportDatabase . '.EmailDef (EmailDefId, Email) VALUES (:queryDefId, :whereClause)';
				$this->runInsert($insertStatement, array(':queryDefId' => $queryDefId, ':whereClause' => $stringToHash));
				
                		$this->commitTransaction();
                		break;
            		}
			}
			catch (Exception $e) {
				$this->rollbackTransaction();
				$county++;
				if ($county==$maxTries) {
					$this->errorHandler->sendError(500, "Insert select execution failed.", $e);
            				throw new $e;
					break; }
				usleep(500000);
				continue;	
			}
		break;
		
	   } while($county < $maxTries);
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
            $this->errorHandler->sendError(500, "Query execution failed.", $e . "\n" . $sqlQuery);
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
			$this->errorHandler->sendError(500, "Insert execution failed.", $e . "\n" . $sqlStatement);		
			throw new $e;
			break;}
	    	usleep(1000000);
	    	continue;
        	}
	break;
	     } while($counto<$maxTriesy);

        return $results;
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


    private function buildSelectStringForEntityReturnApiField($entitySpec)
    {
	$selectString = Array();
	foreach ($this->selectFieldSpecs as $apiField => $fieldInfo) {
	    if ($fieldInfo['entity_name'] == $entitySpec['entity_name']) {
		$selectString[] = $apiField;
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
			if (!$this->sort_by_subentity_counts || ($this->sort_by_subentity_counts && getDBField($this->fieldSpecs, $apiField) !== getDBField($this->fieldSpecs, $this->sort_by_subentity_counts))) {

				$secEntityField = $fieldSpec['entity_name'];
				$secEntityField .= "s";

				if (array_key_exists($secEntityField,$this->sortFieldsUsedSec)) {
					array_push($this->sortFieldsUsedSec[$secEntityField],getDBField($this->fieldSpecs, $apiField) . ' ' . $direction);
				} else {
					$this->sortFieldsUsedSec[$secEntityField] = array(getDBField($this->fieldSpecs, $apiField) . ' ' . $direction);
				}
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