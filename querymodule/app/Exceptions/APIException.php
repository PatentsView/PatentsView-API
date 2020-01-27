<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 4:08 PM
 */

namespace PVExceptions;

use \Exception as e;
use Throwable;

abstract class APIException extends e
{
    protected $code_mapping;
    public function __construct($message = "", $code = "", Throwable $previous = null)
    {
        $this->custom_code = $code;
        parent::__construct($message, $this->code_mapping[$code], $previous);
    }
    public function getCustomCode()
    {
        return $this->custom_code;
    }
}