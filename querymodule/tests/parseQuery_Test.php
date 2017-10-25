<?php
require_once dirname(__FILE__) . '/../app/QueryParser.php';

class parseQuery_Test extends PHPUnit_Framework_TestCase
{

    public function testSimplePair()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"patent_type":"design"}', true), 'all');
        $this->assertEquals("(patent.type = 'design')", $returnString);
        $this->assertEquals(array('patent_type'), $pq->getFieldsUsed());
    }

    public function testJoin()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_and":[{"patent_type":"design"},{"inventor_last_name":"Hopper"}]}', true), 'all');
        $this->assertEquals("((patent.type = 'design') and (inventor.name_last = 'Hopper'))", $returnString);
        $expectedFields = array('patent_type','inventor_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
    }

    public function testComparison()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_lt":{"patent_type":"design"}}', true), 'all');
        $this->assertEquals("(patent.type < 'design')", $returnString);
        $this->assertEquals(array('patent_type'), $pq->getFieldsUsed());
    }

    public function testNegation()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_not":{"patent_type":"design"}}', true), 'all');
        $this->assertEquals("not (patent.type = 'design')", $returnString);
        $this->assertEquals(array('patent_type'), $pq->getFieldsUsed());
    }

    public function testValueList()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"inventor_last_name":["Hopper","Whitney"]}', true), 'all');
        $this->assertEquals("(inventor.name_last in ('Hopper', 'Whitney'))", $returnString);
        $this->assertEquals(array('inventor_last_name'), $pq->getFieldsUsed());
    }

    public function testComplex_1()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_and":[{"_gte":{"patent_date":"2007-01-04"}},{"_text_any":{"patent_title":"garment"}},{"inventor_last_name":"Hopper"},{"patent_title":["cotton gin","COBOL"]}]}', true), 'all');
        $this->assertEquals("((patent.date >= '2007-01-04') and match (patent.title) against ('garment' in boolean mode) and (inventor.name_last = 'Hopper') and (patent.title in ('cotton gin', 'COBOL')))", $returnString);
        $expectedFields = array('patent_title','inventor_last_name','patent_date');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertSame($expectedFields, $usedFields);
    }

    public function testComplex_2()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_or":[{"_and":[{"inventor_last_name":"Whitney"},{"_text_phrase":{"patent_title":"cotton gin"}}]},{"_and":[{"inventor_last_name":"Hopper"},{"_text_any":{"patent_title":"COBOL"}}]}]}', true), 'all');
        $this->assertEquals("(((inventor.name_last = 'Whitney') and match (patent.title) against ('\"cotton gin\"' in boolean mode)) or ((inventor.name_last = 'Hopper') and match (patent.title) against ('COBOL' in boolean mode)))", $returnString);
        $expectedFields = array('patent_title','inventor_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
    }

    public function testStringNotEqual()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_neq":{"inventor_last_name":"Whitney"}}', true), 'all');
        $this->assertEquals("(inventor.name_last <> 'Whitney')", $returnString);
        $expectedFields = array('inventor_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
    }

    public function testStringBegins()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $returnString = $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_begins":{"inventor_last_name":"Whit"}}', true), 'all');
        $this->assertEquals("(inventor.name_last like 'Whit%')", $returnString);
        $expectedFields = array('inventor_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidAPIField()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $pq->parse($PATENT_FIELD_SPECS, json_decode('{"XYZ":"value"}', true), 'all');
    }

    public function testComplex_1_ByEntity()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $queryString = json_decode('{"_and":[{"assignee_last_name":"Smith"},{"_text_any":{"patent_title":"garment"}},{"inventor_last_name":"Hopper"},{"patent_title":["cotton gin","COBOL"]}]}', true);

        $returnString = $pq->parse($PATENT_FIELD_SPECS, $queryString, 'inventor');
        $this->assertEquals("((inventor.name_last = 'Hopper'))", $returnString);
        $expectedFields = array('inventor_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
        $this->assertTrue($pq->getOnlyAndsWereUsed());

        $returnString = $pq->parse($PATENT_FIELD_SPECS, $queryString, 'assignee');
        $this->assertEquals("((assignee.name_last = 'Smith'))", $returnString);
        $expectedFields = array('assignee_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
        $this->assertTrue($pq->getOnlyAndsWereUsed());
    }

    public function testComplex_2_ByEntity()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $queryString = json_decode('{"_or":[{"_and":[{"inventor_last_name":"Whitney"},{"_text_phrase":{"patent_title":"cotton gin"}}]},{"_and":[{"inventor_last_name":"Hopper"},{"_text_any":{"patent_title":"COBOL"}}]}]}', true);

        $returnString = $pq->parse($PATENT_FIELD_SPECS, $queryString, 'inventor');
        $this->assertEquals("(((inventor.name_last = 'Whitney')) or ((inventor.name_last = 'Hopper')))", $returnString);
        $expectedFields = array('inventor_last_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
        $this->assertFalse($pq->getOnlyAndsWereUsed());
    }

    public function testComplex_3_ByEntity()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $queryString = json_decode('{"_or":[{"_and":[{"inventor_last_name":"Whitney"},{"inventor_first_name":"Eli"}]},{"_and":[{"inventor_last_name":"Hopper"},{"_text_any":{"patent_title":"COBOL"}}]}]}', true);

        $returnString = $pq->parse($PATENT_FIELD_SPECS, $queryString, 'inventor');
        $this->assertEquals("(((inventor.name_last = 'Whitney') and (inventor.name_first = 'Eli')) or ((inventor.name_last = 'Hopper')))", $returnString);
        $expectedFields = array('inventor_last_name','inventor_first_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
        $this->assertFalse($pq->getOnlyAndsWereUsed());
    }

    public function testComplex_4_ByEntity()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $queryString = json_decode('{"_or":[{"_and":[{"inventor_last_name":"Whitney"},{"inventor_first_name":"Eli"}]},{"_text_any":{"patent_title":"COBOL"}}]}', true);

        $returnString = $pq->parse($PATENT_FIELD_SPECS, $queryString, 'inventor');
        $this->assertEquals("(((inventor.name_last = 'Whitney') and (inventor.name_first = 'Eli')))", $returnString);
        $expectedFields = array('inventor_last_name','inventor_first_name');
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
        $this->assertFalse($pq->getOnlyAndsWereUsed());

        $returnString = $pq->parse($PATENT_FIELD_SPECS, $queryString, 'assignee');
        $this->assertEquals("", $returnString);
        $expectedFields = array();
        sort($expectedFields);
        $usedFields = $pq->getFieldsUsed();
        sort($usedFields);
        $this->assertEquals($expectedFields, $usedFields);
        $this->assertFalse($pq->getOnlyAndsWereUsed());
    }

}
 