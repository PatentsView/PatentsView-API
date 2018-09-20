<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 4:46 PM
 */

namespace Exceptions;

require_once(dirname(__FILE__) . "/APIException.php");

class ParsingException extends APIException
{
    protected $message = array("PINV1" => "Invalid integer value provided: %s",
        "PINV2" => "Invalid float value provided: %s",
        "PINV3" => "Invalid date provided: %s",
        "PINV4" => "Invalid field type %s found for  %s",
        "PINV5" => "Not a valid field for querying: %s",
        "PINV6" => "Invalid field type %s or operator %s found for %s.",
        "PINV7" => "The operation %s is not valid on %s.",
        "PINV8" => "Invalid field specified: %s ");
    private $code_mapping = array("PINV1" => 400, "PINV2" => 400, "PINV3" => 400, "PINV4" => 400, "PINV5" => 400, "PINV6" => 400, "PINV7" => 400, "PINV8" => 500);
    private $custom_code = "";

    public function __construct($code = "", array $custom_message = array(), Throwable $previous = null)
    {
        $this->custom_code = $code;
        parent::__construct(vsprintf($this->message[$code], $custom_message), $this->code_mapping[$code], $previous);
    }

    public function getCustomCode()
    {
        return $this->custom_code;
    }
}