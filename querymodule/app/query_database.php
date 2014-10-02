<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/fieldSpecs.php';
require_once dirname(__FILE__) . '/../thirdparty/apache-log4php-2.3.0/logger.php';

function queryDatabase($whereClause, array $selectFieldSpecs)
{
    global $config;

    Logger::configure(dirname(__FILE__) . '/logger_config.xml');
    $log = Logger::getLogger('myLogger');

    $dbSettings = $config->getDbSettings();
    $db = new PDO("mysql:host=$dbSettings[host];dbname=$dbSettings[database]", $dbSettings['user'], $dbSettings['password']);
    // TODO Do something real when the connection to the DB fails.

    $selectString = buildSelectString($selectFieldSpecs);

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

    /*    $sqlQuery = 'SELECT patent.id AS patent_id, patent.type AS patent_type, number AS patent_number, ' .
            'country AS patent_country, title AS patent_title, assignee.id as assignee_id, ' .
            'assignee.name_last as assignee_last_name ' .
            'FROM patent ' .
            'left outer join patent_assignee on patent.id=patent_assignee.patent_id ' .
            'left outer join assignee on patent_assignee.assignee_id=assignee.id ' .
            'WHERE ' . $whereClause . ' ' .
            'order BY patent.id, assignee.id';

        $sqlQuery = 'SELECT patent.id AS patent_id, patent.type AS patent_type, number AS patent_number, ' .
            'country AS patent_country, title AS patent_title, inventor.id AS inventor_id, ' .
            'inventor.name_last AS inventor_last_name ' .
            'FROM patent ' .
            'left outer JOIN patent_inventor ON patent.id=patent_inventor.patent_id ' .
            'left outer JOIN inventor ON patent_inventor.inventor_id=inventor.id ' .
            'WHERE ' . $whereClause . ' ' .
            'order BY patent.id, inventor.id';

        $sqlQuery = 'SELECT patent.id AS patent_id, patent.type AS patent_type, number AS patent_number, ' .
            'country AS patent_country, title AS patent_title ' .
            'FROM patent ' .
            'WHERE ' . $whereClause . ' ' .
            'order BY patent.id';*/

    $log->debug($sqlQuery);

    $st = $db->query($sqlQuery, PDO::FETCH_ASSOC);
    // TODO Handle error

    $results = $st->fetchAll();
    $log->debug($results);

    $st->closeCursor();
    return $results;
}

function buildSelectString(array $selectFieldSpecs)
{
    global $FIELD_SPECS;
    $selectString = '';
    $alreadyHasPatentId = false;
    $alreadyHasInventorId = false;
    $alreadyHasAssigneeId = false;
    $alreadyHasApplicationId = false;
    $alreadyHasIPCId = false;
    $alreadyHasApplicationCitationId = false;
    $alreadyHasPatentCitationId = false;
    $alreadyHasUSPCId = false;
    $hasInventorFields = false;
    $hasAssigneeFields = false;
    $hasApplicationFields = false;
    $hasIPCFields = false;
    $hasApplicationCitationFields = false;
    $hasPatentCitationFields = false;
    $hasUSPCFields = false;

    foreach ($selectFieldSpecs as $apiField => $dbInfo) {
        if ($dbInfo['field_name'] == 'patent_id')
            $alreadyHasPatentId = true;
        elseif ($dbInfo['field_name'] == 'inventor_id')
            $alreadyHasInventorId = true;
        elseif ($dbInfo['field_name'] == 'assignee_id')
            $alreadyHasAssigneeId = true;
        elseif ($dbInfo['field_name'] == 'application_id')
            $alreadyHasApplicationId = true;
        elseif ($dbInfo['field_name'] == 'ipc_id')
            $alreadyHasIPCId = true;
        elseif ($dbInfo['field_name'] == 'applicationcitation_id')
            $alreadyHasApplicationCitationId = true;
        elseif ($dbInfo['field_name'] == 'patentcitation_id')
            $alreadyHasPatentCitationId = true;
        elseif ($dbInfo['field_name'] == 'uspc_id')
            $alreadyHasUSPCId = true;
        if ($dbInfo['table_name'] == 'inventor_flat')
            $hasInventorFields = true;
        elseif ($dbInfo['table_name'] == 'assignee_flat')
            $hasAssigneeFields = true;
        elseif ($dbInfo['table_name'] == 'application')
            $hasApplicationFields = true;
        elseif ($dbInfo['table_name'] == 'ipcr')
            $hasIPCFields = true;
        elseif ($dbInfo['table_name'] == 'usapplicationcitation')
            $hasApplicationCitationFields = true;
        elseif ($dbInfo['table_name'] == 'uspatentcitation')
            $hasPatentCitationFields = true;
        elseif ($dbInfo['table_name'] == 'uspc_flat')
            $hasUSPCFields = true;
    }

    if (!$alreadyHasPatentId)
        $selectFieldSpecs['patent_id'] = $FIELD_SPECS['patent_id'];
    if ($hasInventorFields and !$alreadyHasInventorId)
        $selectFieldSpecs['inventor_id'] = $FIELD_SPECS['inventor_id'];
    if ($hasAssigneeFields and !$alreadyHasAssigneeId)
        $selectFieldSpecs['assignee_id'] = $FIELD_SPECS['assignee_id'];
    if ($hasApplicationFields and !$alreadyHasApplicationId)
        $selectFieldSpecs['application_id'] = $FIELD_SPECS['application_id'];
    if ($hasIPCFields and !$alreadyHasIPCId)
        $selectFieldSpecs['ipc_id'] = $FIELD_SPECS['ipc_id'];
    if ($hasApplicationCitationFields and !$alreadyHasApplicationCitationId)
        $selectFieldSpecs['applicationcitation_id'] = $FIELD_SPECS['applicationcitation_id'];
    if ($hasPatentCitationFields and !$alreadyHasPatentCitationId)
        $selectFieldSpecs['patentcitation_id'] = $FIELD_SPECS['patentcitation_id'];
    if ($hasUSPCFields and !$alreadyHasUSPCId)
        $selectFieldSpecs['uspc_id'] = $FIELD_SPECS['uspc_id'];

    foreach ($selectFieldSpecs as $apiField => $dbInfo) {
        if ($selectString != '')
            $selectString .= ', ';
        $selectString .= "$dbInfo[table_name].$dbInfo[field_name] as $apiField";
    }

    return $selectString;
}