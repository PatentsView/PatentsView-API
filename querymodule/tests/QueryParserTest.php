<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/21/18
 * Time: 12:16 PM
 */
require_once dirname(__FILE__) . '/../app/QueryParser.php';
require_once(dirname(__FILE__) . "/../app/Exceptions/ParsingException.php");
require_once dirname(__FILE__) . '/../app/entitySpecs.php';
class QueryParserTest extends PHPUnit_Framework_TestCase
{
    public function test_invalidField()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $exception = null;
        try {
            $pq->parse($PATENT_FIELD_SPECS, json_decode('{"XYZ":"value"}', true), 'all');
        } catch (\Exceptions\ParsingException $e) {
            $exception = $e->getCustomCode();
        }
        $this->assertEquals("PINV8", $exception);
    }
    public function test_nonQueryField()
    {
        global $PATENT_FIELD_SPECS;
        $pq = new QueryParser();
        $exception = null;
        try {
            $pq->parse($PATENT_FIELD_SPECS, json_decode('{"forprior_sequence":"value"}', true), 'all');
        } catch (\Exceptions\ParsingException $e) {
            $exception = $e->getCustomCode();
        }
        $this->assertEquals("PINV5", $exception);
    }
    public function test_invalidOperation()
    {
        global $PATENT_FIELD_SPECS;

        $pq = new QueryParser();
        $exception = null;
        try {
            $pq->parse($PATENT_FIELD_SPECS, json_decode('{"_text_all":{"detail_desc_length":"value"}}', true), 'all');
        } catch (\Exceptions\ParsingException $e) {
            $exception = $e->getCustomCode();
        }
        $this->assertEquals("PINV7", $exception);
    }
    public function test_invalidDataType()
    {
        global $PATENT_FIELD_SPECS;
        global $USPC_FIELD_SPECS;
        $pq = new QueryParser();
        $exception = null;
        try {
            $pq->parse($PATENT_FIELD_SPECS, json_decode('{"detail_desc_length":"value"}', true), 'all');
        } catch (\Exceptions\ParsingException $e) {
            $exception = $e->getCustomCode();
        }
        $this->assertEquals("PINV1", $exception);

        $exception = null;
        try {
            $pq->parse($USPC_FIELD_SPECS, json_decode('{"assignee_lastknown_latitude":"String"}', true), 'all');
        } catch (\Exceptions\ParsingException $e) {
            $exception = $e->getCustomCode();
        }
        $this->assertEquals("PINV2", $exception);

        $exception = null;
        try {
            $pq->parse($PATENT_FIELD_SPECS, json_decode('{"patent_date":"value"}', true), 'all');
        } catch (\Exceptions\ParsingException $e) {
            $exception = $e->getCustomCode();
        }
        $this->assertEquals("PINV3", $exception);
    }
}
