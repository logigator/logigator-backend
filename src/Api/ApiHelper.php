<?php
/**
 * Created by PhpStorm.
 * User: LeoP
 * Date: 28.07.2019
 * Time: 19:26
 */

namespace Logigator\Api;


use Slim\Http\Response;

class ApiHelper
{
    public static function createJsonResponse(Response $response, $data, $status = 200, $error = null) {
        if($data === null) {
            $data = array();
        }
        if ($error !== null) {
            $data['error'] = $error;
        }
        if(is_array($data)) {
            $data['status'] = $status;
        } else {
            $data->status = $status;
        }
        return $response->withJson($data, $status);
    }

    public static function checkRequiredArgs($body, array $args): bool {
        foreach ($args as $arg) {
            if(!isset($body[$arg])) {
                return false;
            }
        }
        return true;
    }
}