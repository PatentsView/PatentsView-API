<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 1:55 PM
 */

namespace PVExceptions;



require_once(dirname(__FILE__) . "/APIException.php");


class RequestException extends APIException
{
    protected $message = array("RQ1" => "'q' param is missing", "RQ2" => "'q' param is not valid json", "RQ3" => "'q' parameter: should only have one json object in the top-level dictionary.", "RF2" => "'f' param is not valid json", "RS2" => "'s' parameter: is not valid json", "RO2" => "'o' param is not valid json", "RFO4" => "Invalid option for 'format' parameter: use either 'json' or 'xml'.", "POST1" => "Body does not contain valid JSON: ");


    public function __construct($code = "", array $custom_message = array(), Throwable $previous = null)
    {
        $this->code_mapping = array("RQ1" => 400, "RQ2" => 400, "RQ3" => 400, "RF2" => 400, "RS2" => 400, "RO2" => 400, "RFO4" => 400);
        parent::__construct(vsprintf($this->message[$code], $custom_message), $code, $previous);
    }
}