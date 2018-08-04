<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;

class UtilsBasic
{
    //------------------------------------------------------------------
    /*
     * From https://stackoverflow.com/questions/9802788/call-a-rest-api-in-php
     */
    public static function callAPI($method, $url, $data = false, $headers = false)
    {
        $curl = curl_init();

        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    $data = json_encode( $data, JSON_UNESCAPED_UNICODE ); // json encode
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
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

        // Redirecciones
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 1);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);

        //-----
        $result = curl_exec($curl);
        if ($result === FALSE) {
            echo "cURL Error: " . curl_error($curl);
        }
        curl_close($curl);

        return $result;
    }
    //------------------------------------------------------------------
    public static function parse_domain($host = '')
    {
        if (!$host) {
            $host = $_SERVER['HTTP_HOST'];
        }

        $parts     = explode('.', $host);
        $num_parts = count($parts);

        //----
        $domain = $parts[$num_parts - 2] . '.' . $parts[$num_parts - 1];

        //----
        $subdomains = array_slice($parts, 0, $num_parts - 2);
        $subdomain  = implode('.', $subdomains);

        //----
        return array(
            'main'      => $domain,
            'other'     => $subdomain,
            'subdomain' => $subdomains,
        );

    }
    //------------------------------------------------------------------
    // Arrays
    //------------------------------------------------------------------
    public static function array_is_assoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    //------------------------------------------------------------------
    // Introduce uno o más elementos al principio de la matriz asociativa
    public static function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr       = array_reverse($arr, true);
        $arr[$key] = $val;
        $arr       = array_reverse($arr, true);
        return count($arr);
    }
    //------------------------------------------------------------------
    // Concatena todas las cadenas de un array mediante un separador dado
    public static function array_implode($sep, array $listStr)
    {
        $strResult = '';

        $c = 0;
        foreach ($listStr as $value) {
            if (!$value) {
                continue;
            }

            if ($c > 0) {
                $strResult .= $sep;
            }
            $strResult .= $value;

            $c = 1;
        }

        return $strResult;
    }
    //------------------------------------------------------------------
    //------------------------------------------------------------------
    /*
     * Envía un email en formato HTML.
     *   - Elimina la codificación UTF8.
     *   - Ejemplo de $from = 'Galletas Fontaneda <gfontaneda@gmail.com>';
     */
    public static function sendEMail($from, $mailto, $bcc, $asunto, $body, $ReplyTo = '')
    {
        $from   = utf8_decode($from);
        $asunto = utf8_decode($asunto);
        $body   = utf8_decode($body);

        // Headers ----------
        $header = "From: $from\n";
        $header .= "X-Mailer: PHP/" . phpversion() . "\n";
        $header .= "Mime-Version: 1.0\n";
        $header .= "Content-Type: text/html; charset=iso-8859-1\n";

        if ($bcc) {
            $header .= 'Bcc: ' . $bcc . "\n";
        }
        if ($ReplyTo) {
            $header .= 'Reply-To: ' . $ReplyTo . "\n" .
                'Return-Path: ' . $ReplyTo . "\n";
        }

        // Body -------------
        $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
  <html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <style>
    body {
     background=#fff; font-family: Verdana, Arial; line-height: 21px;
    }
    </style>
  </head>
  <body>
    ' . $body . '
  </body>
  </html>';

        // Mail -------------
        if (IS_LOCALHOST) {
            echo "
         UtilsBasic::sendEMail() >> mail() >> IS_LOCALHOST<hr>
         Asunto: '$asunto'<br>
         Para: '$mailto'<br>
         <hr><br>
         $body
       ";
            exit;
        }

        mail($mailto, $asunto, $body, $header);
    }
    //------------------------------------------------------------------
    /*
     * Obtener un CSV a partir de un array
     */
    public static function get_csv(array $listFields,
                                   array $listRows,
                                   $SEP,
                                   $isDebug = false,
                                   $fileName = 'export.csv')
    {
        $LINE_RET = ($isDebug) ? '<br>' : "\n";

        //--------------
        $header = '';
        foreach ($listFields as $f_name) {
            $header .= $SEP . '"' . $f_name . '"';
        }
        $header = ltrim($header, $SEP);
        $header .= $LINE_RET;

        //--------------
        $str_csv = '';
        foreach ($listRows as $row) {
            $line = '';
            foreach ($listFields as $f_name) {
                $value = trim($row[$f_name]);

                if (!$isDebug) {
                    $value = addslashes($value);
                }
                $value = str_replace(array("\n", "\r"), ' ', $value);

                $line .= $SEP . '"' . $value . '"';
            }
            $line = ltrim($line, $SEP);
            $str_csv .= $line . $LINE_RET;
        }

        // OUT ---------
        if ($isDebug) {
            return $header . $str_csv;
        } else {
            // header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            // header("Pragma: no-cache");
            // header("Expires: 0");
            echo $header . $str_csv;
            exit();
        }
    }
    //------------------------------------------------------------------
}
