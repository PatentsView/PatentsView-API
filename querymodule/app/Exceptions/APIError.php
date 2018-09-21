<?php
/**
 * Created by PhpStorm.
 * User: smadhavan
 * Date: 9/20/18
 * Time: 3:44 PM
 */

namespace Slim\Handlers;
require_once dirname(__FILE__) . '/../ErrorHandler.php';

use Exceptions\APIException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class APIError extends Error
{
    public function __invoke(Request $request, Response $response, APIException $exception)
    {
        $status = $exception->getCode() ?: 500;
        $logger = \ErrorHandler::getHandler()->getLogger();
        $ip=$request->getServerParam('REMOTE_ADDR');
        $customCode=$exception->getCustomCode();
        $query=$request->getUri();
        $logger->error("$status\t$customCode\t$ip\t$query");
        return $response->withHeader("X-Status-Reason", $exception->getMessage())->withJson(array("error" => $exception->getMessage()), $status);
    }
}
