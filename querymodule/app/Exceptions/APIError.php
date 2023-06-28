<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 3:44 PM
 */

namespace Exceptions;
require_once dirname(__FILE__) . '/../ErrorHandler.php';

use Exceptions\APIException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UnexpectedValueException;

final class APIError extends \Exception
{
    public function __invoke($request, $response, $exception)
    {
        global $config;
        $status = $exception->getCode() ?: 500;
        $logger = \ErrorHandler::getHandler()->getLogger($config);
        $ip = $request->getServerParam('REMOTE_ADDR');
//        try {
//            $customCode = $exception->getCustomCode();
//        } catch (UnexpectedValueException $e) {
//            $customCode = 'UNK';
//        }

        $query = $request->getUri();
        $logger->error("$status\t$ip\t$query");
        return $response->withHeader("X-Status-Reason", $exception->getMessage())->withJson(array("status" => "error", "payload" => array("error" => $exception->getMessage(), "code" => "")), $status);
    }


}
