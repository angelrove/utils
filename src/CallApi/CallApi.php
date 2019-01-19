<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2018
 *
 * With Guzzle: http://docs.guzzlephp.org/en/latest/overview.html
 */

namespace angelrove\utils\CallApi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class CallApi
{
    static private $lastUrl;

    //------------------------------------------------------------------
    public static function call($method, $url, array $headers = array(), array $data = array(),
                                $timeout=8)
    {
        // print_r2($url); print_r2($headers); exit();

        self::$lastUrl = $url;

        $body = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Request ----
        $client  = new Client();
        $request = new Request($method, $url, $headers, $body);

        // Response ---
        $response = $client->send($request, ['timeout' => $timeout]);
        $body = $response->getBody();

        //----
        $ret = new \stdClass;
        $ret->statusCode = $response->getStatusCode();
        $ret->body       = $body->getContents();
        //----

        // return $ret->body;
        return $ret;
    }
    //------------------------------------------------------------------
    public static function responseDecode($response)
    {
        if (!$response) {
            return '';
        }

        $result = json_decode($response);
        if ($result == NULL) {
            throw new \Exception(
                "CallAPI - decoding response: ".self::$lastUrl.
                '<div style="background:white">'.$response.'</div>'
            );
        }

        return $result;
    }
    //------------------------------------------------------------------
    public static function callAsObject($method, $url, array $headers = array(),
                                        array $data = array(),
                                        $timeout=8)
    {
        $response  = self::call($method, $url, $headers, $data, $timeout);

        // json decode ---
        $response->body = self::responseDecode($response->body);

        return $response;
    }
    //------------------------------------------------------------------
}
