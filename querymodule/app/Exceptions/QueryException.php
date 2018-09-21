<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/21/18
 * Time: 4:53 PM
 */

namespace Exceptions;


class QueryException extends APIException
{
    protected $message = array(
        "QR1" => "Per_page must be a positive number not to exceed: %d",
        "QR2" => "Sorting field %s is not in the output field list.",
        "QDI1" => "Query Internal Error",
        "QDI2" => "Query Internal Error",
        "QDS1" => "Query Internal Error",
        "QDIS1" => "Query Internal Error",);


    public function __construct($code = "", array $custom_message = array(), Throwable $previous = null)
    {
        $this->code_mapping = array("QR1" => 400, "QR2" => 400, "QD1" => 500, "QDS1" => 500, "PINV5" => 400, "PINV6" => 400, "PINV7" => 400, "PINV8" => 400);

        parent::__construct(vsprintf($this->message[$code], $custom_message), $code, $previous);
    }
}