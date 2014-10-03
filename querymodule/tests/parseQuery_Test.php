<?php
/**
 * Created by PhpStorm.
 * User: gregy
 * Date: 9/25/2014
 * Time: 10:41 AM
 */
require_once dirname(__FILE__) . '/../app/QueryParser.php';

class parseQuery_Test extends PHPUnit_Framework_TestCase
{

    public function testSimplePair()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"patent_type":"design"}', true));
        $this->assertEquals("(patent.type = 'design')", $returnString);
        $this->assertEquals(array('patent_type'), $pq->getFieldsUsed());
    }

    public function testJoin()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"_and":[{"patent_type":"design"},{"inventor_last_name":"Hopper"}]}', true));
        $this->assertEquals("((patent.type = 'design') and (inventor_flat.name_last = 'Hopper'))", $returnString);
        $expectedFields = array('patent_title','inventor_last_name');
        $this->assertEquals(sort($expectedFields), sort($pq->getFieldsUsed()));
    }

    public function testComparison()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"_lt":{"patent_type":"design"}}', true));
        $this->assertEquals("(patent.type < 'design')", $returnString);
        $this->assertEquals(array('patent_type'), $pq->getFieldsUsed());
    }

    public function testNegation()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"_not":{"patent_type":"design"}}', true));
        $this->assertEquals("not (patent.type = 'design')", $returnString);
        $this->assertEquals(array('patent_type'), $pq->getFieldsUsed());
    }

    public function testValueList()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"inventor_last_name":["Hopper","Whitney"]}', true));
        $this->assertEquals("(inventor_flat.name_last in ('Hopper', 'Whitney'))", $returnString);
        $this->assertEquals(array('inventor_last_name'), $pq->getFieldsUsed());
    }

    public function testComplex_1()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"_and":[{"_gte":{"patent_date":"2007-01-04"}},{"_text_any":{"patent_title":"garment"}},{"inventor_last_name":"Hopper"},{"patent_title":["cotton gin","COBOL"]}]}', true));
        $this->assertEquals("((patent.date >= '2007-01-04') and match (patent.title) against ('garment' in boolean mode) and (inventor_flat.name_last = 'Hopper') and (patent.title in ('cotton gin', 'COBOL')))", $returnString);
        $expectedFields = array('patent_title','inventor_last_name');
        $this->assertEquals(sort($expectedFields), sort($pq->getFieldsUsed()));
    }

    public function testComplex_2()
    {
        $pq = new QueryParser();
        $returnString = $pq->parse(json_decode('{"_or":[{"_and":[{"inventor_last_name":"Whitney"},{"_text_phrase":{"patent_title":"cotton gin"}}]},{"_and":[{"inventor_last_name":"Hopper"},{"_text_any":{"patent_title":"COBOL"}}]}]}', true));
        $this->assertEquals("(((inventor_flat.name_last = 'Whitney') and match (patent.title) against ('\"cotton gin\"' in boolean mode)) or ((inventor_flat.name_last = 'Hopper') and match (patent.title) against ('COBOL' in boolean mode)))", $returnString);
        $expectedFields = array('patent_title','inventor_last_name');
        $this->assertEquals(sort($expectedFields), sort($pq->getFieldsUsed()));
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidAPIField()
    {
        $pq = new QueryParser();
        $pq->parse(json_decode('{"XYZ":"value"}', true));
    }

}
 