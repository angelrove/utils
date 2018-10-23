<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2018
 *
 */

namespace angelrove\utils;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class CallApi
{
    static private $lastResponse;
    static private $lastUrl;

    //------------------------------------------------------------------
    public static function getLastJsonResponse()
    {
        return self::$lastResponse;
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
    // Call API
    //------------------------------------------------------------------
    public static function callAsObject($method, $url, array $headers = array(), array $data = array())
    {
        $response = self::call($method, $url, $headers, $data);

        // json decode ---
        return self::responseDecode($response);
    }
    //------------------------------------------------------------------
    public static function call2AsObject($method, $url, array $headers = array(), array $data = array())
    {
        $response = self::call2($method, $url, $headers, $data);

        // json decode ---
        return self::responseDecode($response);
    }
    //------------------------------------------------------------------
    /*
     * From https://stackoverflow.com/questions/9802788/call-a-rest-api-in-php
     */
    public static function call($method, $url, $headers = false, $data = false)
    {
        self::$lastUrl = $url;

        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    $data = json_encode( $data, JSON_UNESCAPED_UNICODE );
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
            break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);

                if ($data) {
                    $data = json_encode( $data, JSON_UNESCAPED_UNICODE );
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
            break;

            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        // Custom headers:
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        // Optional Authentication:
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 4);

        // Redirecciones
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 1);

        // Curl exec -------
        self::$lastResponse = curl_exec($curl);

        if (self::$lastResponse === FALSE) {
            $msgErr = curl_error($curl);
            curl_close($curl);

            throw new \Exception('CallAPI - cURL Error: '.$msgErr." in url: '$url'");
        }
        curl_close($curl);

        return self::$lastResponse;
    }
    //------------------------------------------------------------------
    /*
     * http://docs.guzzlephp.org/en/latest/overview.html
     */
    public static function call2($method, $url, array $headers = array(), array $data = array())
    {
        self::$lastUrl = $url;

        // print_r2($url); print_r2($headers); exit();

        $body = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Request ----
        $client  = new Client();
        $request = new Request($method, $url, $headers, $body);

        // Response ---
        $response = $client->send($request, ['timeout' => 9]);

        $body = $response->getBody();
        self::$lastResponse = $body->getContents();

        return self::$lastResponse;
    }
    //------------------------------------------------------------------
}