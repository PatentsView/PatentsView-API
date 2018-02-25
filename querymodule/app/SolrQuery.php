<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 2/20/18
 * Time: 5:37 PM
 */

class PVSolrQuery
{
    private $solr_connections = array();
    private $db = null;
    private $errorHandler = null;

    public function __construct(array $entitySpecs)
    {
        global $config;
        foreach ($entitySpecs as $entitySpec) {
            if (!array_key_exists($entitySpec["entity_name"], $this->solr_connections)){

            $currentDBSetting = $config->getSOLRSettings();
            $currentDBSetting["path"] = "solr/" . $entitySpec['solr_collection'];
            try {
//                file_put_contents('php://stderr', print_r($currentDBSetting, TRUE));
                $this->solr_connections[$entitySpec["entity_name"]]= new SolrClient($currentDBSetting);
            } catch (SolrIllegalArgumentException $e) {

                $this->errorHandler->sendError(500, "Failed to connect to database: $currentDBSetting[hostname] , $entitySpecs[solr_collection]", $e);
                throw new $e;
            }
            //$this->solr_connections[$entitySpec["entity_name"]] = $currentDBSetting;
        }
        }

    }

    public function getSolrConnection($entity_name)
    {
        return $this->solr_connections[$entity_name];
    }

    public function countRowsForQuery($entity, $where)
    {
        $connectionToUse = $this->solr_connections[$entity];
        $query = new SolrQuery();
        if (array_key_exists("fq", $where)) {
            foreach ($where['fq'] as $filterQuery) {
                $query->addFilterQuery($filterQuery);
                //$query->addF
            }
        }

        if (array_key_exists("q", $where)) {
            $query->setQuery($where["q"]);
        } else {
            $query->setQuery("*:*");
        }
        $q = $connectionToUse->query($query);
        $response = $q->getResponse();
        return $response["response"]["numFound"];
    }
    public function loadMainEntityQuery($entity_name, $query_string){
        $connectionToUse = $this->solr_connections[$entity_name];
        $query = new SolrQuery();
        $query->setQuery( $query_string);

        $rows_left=0;
        $rows_fetched=0;
        do{
            $query->setRows(10);
            $query->setStart($rows_fetched);
            $query->addField("location_id:id");
            $q = $connectionToUse->query($query);

            $response = $q->getResponse();
            $rows_fetched+=10000;
            $rows_left=$response["response"]["numFound"]-$rows_fetched;
            print_r($rows_left);
            break;
        }while($rows_left>0);

    }
    public function query($whereClause){
        foreach (array_keys($whereClause) as $whereJoin){

            foreach ($whereClause[$whereJoin] as $clause){
                $this->loadMainEntityQuery($clause["e"],$clause["q"]);
            }

        }
    }
}