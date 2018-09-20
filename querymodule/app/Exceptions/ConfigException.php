<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 8/17/18
 * Time: 3:22 PM
 */

namespace Exceptions;
require_once(dirname(__FILE__) . "/APIException.php");

use Throwable;

class ConfigException extends APIException
{
    private $ini_path;
    private $config_object;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $ini_path = "", $config_object = array())
    {
        $this->ini_path = $ini_path;
        $this->config_object = $config_object;
        parent::__construct($message, $code, $previous);
    }

}