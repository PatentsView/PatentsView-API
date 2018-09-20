<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 1:55 PM
 */

namespace Exceptions;


require_once (dirname(__FILE__)."/APIException.php");

class RequestException extends APIException
{
    protected $message = array("RQ1" => "'q' param is missing", "RQ2" => "'q' param is not valid json", "RQ3" => "'q' parameter: should only have one json object in the top-level dictionary.", "RF2" => "'f' param is not valid json", "RS2" => "'s' parameter: is not valid json", "RO2" => "'o' param is not valid json", "RFO4" => "Invalid option for 'format' parameter: use either 'json' or 'xml'.", "POST1" => "Body does not contain valid JSON: ");

    private $code_mapping = array("RQ1" => 400, "RQ2" => 400, "RQ3" => 400, "RF2" => 400, "RS2" => 400, "RO2" => 400, "RFO4" => 400);
    private $custom_code = "";

    public function __construct($code = "", $custom_message = "", Throwable $previous = null)
    {
        $this->custom_code = $code;
        parent::__construct($this->message[$code] . " " . $custom_message, $this->code_mapping[$code], $previous);
    }

    public function getCustomCode()
    {
        return $this->custom_code;
    }

}