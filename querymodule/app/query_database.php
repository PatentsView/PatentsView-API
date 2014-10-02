<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/fieldSpecs.php';
require_once dirname(__FILE__) . '/../thirdparty/apache-log4php-2.3.0/logger.php';


class DatabaseQuery
{

    private $groupVars = array(
        array('single' => 'patent', 'hasId' => 'alreadyHasPatentId', 'hasFields' => 'hasPatentFields', 'keyId' => 'patent_id', 'table' => 'patent'),
        array('single' => 'inventor', 'hasId' => 'alreadyHasInventorId', 'hasFields' => 'hasInventorFields', 'keyId' => 'inventor_id', 'table' => 'inventor_flat'),
        array('single' => 'assignee', 'hasId' => 'alreadyHasAssigneeId', 'hasFields' => 'hasAssigneeFields', 'keyId' => 'assignee_id', 'table' => 'assignee_flat'),
        array('single' => 'application', 'hasId' => 'alreadyHasApplicationId', 'hasFields' => 'hasApplicationFields', 'keyId' => 'application_id', 'table' => 'application'),
        array('single' => 'ipc', 'hasId' => 'alreadyHasIPCId', 'hasFields' => 'hasIPCFields', 'keyId' => 'ipc_id', 'table' => 'ipcr'),
        array('single' => 'applicationcitation', 'hasId' => 'alreadyHasApplicationCitationId', 'hasFields' => 'hasApplicationCitationFields', 'keyId' => 'applicationcitation_id', 'table' => 'usapplicationcitation'),
        array('single' => 'patentcitation', 'hasId' => 'alreadyHasPatentCitationId', 'hasFields' => 'hasPatentCitationFields', 'keyId' => 'patentcitation_id', 'table' => 'uspatentcitation'),
        array('single' => 'uspc', 'hasId' => 'alreadyHasUSPCId', 'hasFields' => 'hasUSPCFields', 'keyId' => 'uspc_id', 'table' => 'uspc_flat')
    );

    private $selectFieldSpecs;

    public function queryDatabase($whereClause, array $selectFieldSpecs)
    {
        global $config;

        Logger::configure(dirname(__FILE__) . '/logger_config.xml');
        $log = Logger::getLogger('myLogger');

        $this->selectFieldSpecs = $selectFieldSpecs;
        $this->initializeGroupVars();
        $this->determineSelectFields();
        $selectString = $this->buildSelectString();

        $dbSettings = $config->getDbSettings();
        $db = new PDO("mysql:host=$dbSettings[host];dbname=$dbSettings[database]", $dbSettings['user'], $dbSettings['password']);
        // TODO Do something real when the connection to the DB fails.

        $sqlQuery = "SELECT distinct $selectString " .
            'FROM patent ' .
            'left outer JOIN patent_inventor ON patent.id=patent_inventor.patent_id ' .
            'left outer JOIN inventor_flat ON patent_inventor.inventor_id=inventor_flat.inventor_id ' .
            'left outer join patent_assignee on patent.id=patent_assignee.patent_id ' .
            'left outer join assignee_flat on patent_assignee.assignee_id=assignee_flat.assignee_id ' .
            'left outer join application on patent.id=application.patent_id ' .
            'left outer join ipcr on patent.id=ipcr.patent_id ' .
            'left outer join usapplicationcitation on patent.id=usapplicationcitation.patent_id ' .
            'left outer join uspatentcitation on patent.id=uspatentcitation.patent_id ' .
            'left outer join uspc_flat on patent.id=uspc_flat.uspc_patent_id ';
        if (strlen($whereClause) > 0) {
            $sqlQuery .= "WHERE $whereClause ";
        }
        $sqlQuery .= 'order BY patent.id, inventor_flat.inventor_id, assignee_flat.assignee_id, ipcr.uuid, usapplicationcitation.uuid, ' .
            'uspatentcitation.uuid, uspc_flat.uspc_id';

        $log->debug($sqlQuery);

        $st = $db->query($sqlQuery, PDO::FETCH_ASSOC);
        // TODO Handle error

        $results = $st->fetchAll();
        $log->debug($results);

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
}