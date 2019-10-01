<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/21/18
 * Time: 4:53 PM
 */

namespace Exceptions;

require_once(dirname(__FILE__) . "/APIException.php");

class QueryException extends APIException
{
    protected $message = array(
        "QR1" => "Per_page must be a positive number not to exceed: %d",
        "QR2" => "Sorting field %s is not in the output field list.",
        "QR3" => "Invalid field for sorting: %s",
        "QR4" => "Not a valid direction for sorting: %s",
        "QDI1" => "Query Internal Error",
        "QDI2" => "Query Internal Error",
        "QDS1" => "Query Internal Error",
        "QDIS1" => "Query Internal Error",
        "QDC1" => "Query Internal Error",
        "QDIS2" => "Query Internal Error",
        "QDIS3" => "Query Internal Error");


    public function __construct($code = "", array $custom_message = array(), Throwable $previous = null)
    {
        $this->code_mapping = array("QR1" => 400, "QR2" => 400, "QR3" => 400, "QR4" => 400, "QDI1" => 500,"QDI2" => 500, "QDS1" => 500, "QDIS1" => 500, "QDC1" => 500, "QDIS2" => 500, "QDIS3" => 500);
        $message = vsprintf($this->message[$code], $custom_message);
        \ErrorHandler::getHandler()->getLogger()->debug($message);
        parent::__construct($message, $code, $previous);
    }
}