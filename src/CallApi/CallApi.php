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
    public static function call($method,
                                $url,
                                array $headers = array(),
                                array $data = array(),
                                $asJson=false,
                                $timeout=8)
    {
        self::$lastUrl = $url;

        // Headers ---
        $headers_def = array(
            'Content-Type' => 'application/json',
        );
        $headers = array_merge($headers_def, $headers);

        // Body ---
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Request ----
        // print_r2($method); print_r2($url); print_r2($headers); print_r2($body);

        $client  = new Client();
        $request = new Request($method, $url, $headers, $body);

        // Response ---
        try {
            $response = $client->send($request, ['timeout' => $timeout]);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'error 28') !== false) {
                $ret = new \StdClass();
                $ret->statuscode = 'timeout';
                $ret->body = $e->getMessage();
                return $ret;
            }
            else {
                throw new \Exception($e->getMessage().'body: '.print_r($body, true).'<br>');
            }
        }

        // Ret ----
        $body = $response->getBody();

        $ret = new \stdClass;
        $ret->statusCode = $response->getStatusCode();

        // As Json / As object
        if ($asJson) {
            $ret->body = $body->getContents();
        } else {
            $ret->body = self::responseDecode($body->getContents());
        }

        return $ret;
    }
    //------------------------------------------------------------------
    private static function responseDecode($response)
    {
        if (!$response) {
            return '';
        }

        $result = json_decode($response);
        if ($result == NULL) {
            throw new \Exception(
                "CallAPI - responseDecode: ".self::$lastUrl.
                '<div style="background:white">'.$response.'</div>'
            );
        }

        return $result;
    }
    //------------------------------------------------------------------
}
