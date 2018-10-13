<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class UtilsBasic
{
    //------------------------------------------------------------------
    public static function fileExtFromUrl($url)
    {
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        list($ext) = explode('?', $ext);

        return $ext;
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
    public static function implode($sep, array $listStr)
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
    public static function get_imageFromUrl($url_image,
                                            $path_uploads,
                                            $dir_upload,
                                            $name_file,
                                            $max_with=false)
    {
        // Image name ---
        $ext = pathinfo($url_image, PATHINFO_EXTENSION);
        list($ext) = explode('?', $ext);
        $img_name = "$name_file.$ext";

        // Image path ---
        $path_upload = $path_uploads.'/'.$dir_upload.'/';
        $full_path   = $path_upload.$img_name;

        // Save image file ---
        if (($ret = @file_get_contents($url_image)) === false) {
            // throw new \Exception("Failed to open url image [$url_image]");
            return false;
        }
        file_put_contents($full_path, $ret);

        // Resize ------------
        if ($max_with) {
            list($width, $height, $tipo, $atributos) = getimagesize($full_path);
            if ($width > $max_with) {
                \angelrove\utils\Images\ImageTransform::resize($path_upload, $img_name, $max_with);
            }
        }

        // str membrillo -----
        // $image_mime = image_type_to_mime_type(exif_imagetype($full_path));
        $image_mime = '';
        $ret = "$img_name#$img_name###$image_mime#$dir_upload";

        return $ret;
    }
    //------------------------------------------------------------------
}
