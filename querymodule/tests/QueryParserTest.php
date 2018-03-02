<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 3/1/18
 * Time: 3:37 PM
 */


require_once dirname(__FILE__) . '/../app/entitySpecs.php';
require_once dirname(__FILE__) . '/../app/ErrorHandler.php';
require_once dirname(__FILE__) . '/../app/QueryParser.php';


class QueryParserTest extends PHPUnit_Framework_TestCase
{
    private function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function testQueryParser()
    {
        global $LOCATION_ENTITY_SPECS;
        global $LOCATION_FIELD_SPECS;
        $qp = new QueryParser();
        $queries = array();
        // Each part
        $queries[] = '{"_eq":{"patent_num_cited_by_us_patents":"3"}}';
        $queries[] = '{"_neq":{"wipo_field_id":"1"}}';
        $queries[] = '{"_gt":{"patent_date":"2015-01-05"}}';
        $queries[] = '{"_gte":{"patent_year":"2007"}}';
        $queries[] = '{"_lt":{"cpc_total_num_inventors":"1000"}}';
        $queries[] = '{"_lte":{"app_date":"2002-05-06"}}';
        $queries[] = '{"_begins":{"patent_id":"RE"}}';
        $queries[] = '{"_contains":{"patent_firstnamed_assignee_city":"Las"}}';
        $queries[] = '{"_text_all":{"patent_title":"Mobile floor sweeper"}}';
        $queries[] = '{"_text_any":{"patent_title":"Mobile floor sweeper"}}';
        $queries[] = '{"_text_phrase":{"patent_abstract":"human tumor necrosis"}}';

        $query = json_decode($queries[0], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);

        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("patents.patent_num_cited_by_us_patents : 3", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);


        $query = json_decode($queries[1], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("-wipos.wipo_field_id : 1", $whereClause['q']);
        $this->assertEquals("wipo", $whereClause['e']);
        $this->assertEquals("location_wipo_join", $whereClause['c']);

        $query = json_decode($queries[2], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("patents.patent_date : { 2015-01-05T00\:00\:00Z TO * }", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);

        $query = json_decode($queries[3], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals("patents.patent_year : [ 2007 TO * ]", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));

        $query = json_decode($queries[4], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals("cpcs.cpc_total_num_inventors : { * TO 1000 }", $whereClause['q']);
        $this->assertEquals("cpc", $whereClause['e']);
        $this->assertEquals("location_cpc_join", $whereClause['c']);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));

        $query = json_decode($queries[5], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("applications.app_date : [ * TO 2002-05-06T00\:00\:00Z ]", $whereClause['q']);
        $this->assertEquals("application", $whereClause['e']);
        $this->assertEquals("location_application_join", $whereClause['c']);

        $query = json_decode($queries[6], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("patents.patent_id : RE*", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);

        $query = json_decode($queries[7], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("patents.patent_firstnamed_assignee_city : *Las*", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);

        $query = json_decode($queries[8], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("patents.patent_title : Mobile AND floor AND sweeper", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);

        $query = json_decode($queries[9], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals("patents.patent_title : Mobile OR floor OR sweeper", $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);

        $query = json_decode($queries[10], true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("q", $whereClause) && array_key_exists("e", $whereClause) && array_key_exists("c", $whereClause));
        $this->assertEquals('patents.patent_abstract : "human tumor necrosis"', $whereClause['q']);
        $this->assertEquals("patent", $whereClause['e']);
        $this->assertEquals("location_patent_join", $whereClause['c']);

        // Combination Queries
        $query = json_decode('{
        "_and":[{
            "location_city":"prairie"},{
            "location_country":"us"}]}', true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "location", $LOCATION_ENTITY_SPECS);

        $this->assertEquals(1, count($whereClause));
        $this->assertEquals(true, array_key_exists("AND", $whereClause));
        $this->assertEquals(1, count($whereClause["AND"]));


        $query = json_decode('{
        "_and":[{
            "location_city":"prairie"},{
            "patent_type":"us"},{
            "inventor_first_name":"james"}]}', true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $query, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(1, count($whereClause));
        $this->assertEquals(true, array_key_exists("AND", $whereClause));
        $this->assertEquals(3, count($whereClause["AND"]));


        foreach ($whereClause["AND"] as $clause) {
            $this->assertEquals(true, (array_key_exists("q", $clause) && array_key_exists("e", $clause) && array_key_exists("c", $clause)) || array_key_exists("AND", $clause) || (array_key_exists("OR", $clause)));
            if (array_key_exists("q", $clause)) {
                $this->assertGreaterThan(0, strlen($clause["q"]));
                $this->assertGreaterThan(0, strlen($clause["c"]));
                $this->assertGreaterThan(0, strlen($clause["e"]));
            }
        }
        $complex_combinations = json_decode('{"_or":[{"_and":[{"inventor_last_name":"Whitener"},{"_text_phrase":{"patent_title":"cotton gin"}}]},{"_and":[{"inventor_last_name":"Heath"},{"_text_all":{"patent_title":"COBOL"}}]}]}', true);
        $whereClause = $qp->parse($LOCATION_FIELD_SPECS, $complex_combinations, "all", $LOCATION_ENTITY_SPECS);
        $this->assertEquals(true, array_key_exists("OR", $whereClause));
        $this->assertEquals(false, $this->isAssoc($whereClause["OR"]));
        $this->assertEquals(2, count($whereClause["OR"]));
        $this->assertEquals(true, array_key_exists("AND", $whereClause["OR"][0]));
        $this->assertEquals(2, count($whereClause["OR"][0]["AND"]));
        $this->assertEquals(true, array_key_exists("AND", $whereClause["OR"][1]));
        $this->assertEquals(2, count($whereClause["OR"][1]["AND"]));

        foreach ($whereClause["OR"] as $clause) {
            $this->assertEquals(true, (array_key_exists("q", $clause) && array_key_exists("e", $clause) && array_key_exists("c", $clause)) || array_key_exists("AND", $clause) || (array_key_exists("OR", $clause)));
            if (array_key_exists("q", $clause)) {
                $this->assertGreaterThan(0, strlen($clause["q"]));
                $this->assertGreaterThan(0, strlen($clause["c"]));
                $this->assertGreaterThan(0, strlen($clause["e"]));
            }
        }
        $this->assertEquals("inventors.inventor_last_name : Whitener", $whereClause["OR"][0]["AND"][0]["q"]);
        $this->assertEquals('patents.patent_title : "cotton gin"', $whereClause["OR"][0]["AND"][1]["q"]);
        $this->assertEquals("inventors.inventor_last_name : Heath", $whereClause["OR"][1]["AND"][0]["q"]);
        $this->assertEquals("patents.patent_title : COBOL", $whereClause["OR"][1]["AND"][1]["q"]);
    }


}
