<?php
/**
 * StringsFormat
 */

namespace angelrove\utils;

class StringsFormat
{
    //------------------------------------------------------------------
    // Parse strings
    //------------------------------------------------------------------
    // Set first character of any word to uppercase
    public static function str_ucfirst($str)
    {
        $str          = trim(strtolower($str));
        $listPalabras = explode(' ', $str);

        $res = '';
        foreach ($listPalabras as $palabra) {
            $palabra[0] = strtoupper($palabra[0]);
            $res .= $palabra . ' ';
        }
        return $res;
    }
    //------------------------------------------------------------------
    public static function str_clean($string)
    {
        $string = trim($string);

        $string = self::parseESchars($string);

        // Replaces all spaces with hyphens
        $string = str_replace(' ', '-', $string);

        // Removes special chars
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);

        // To lower case
        $string = strtolower($string);

        // Replaces multiple hyphens with single one
        return preg_replace('/-+/', '-', $string);
    }
    //------------------------------------------------------------------
    public static function parseESchars($str)
    {
        $charsES = array(
            'á', 'é', 'í', 'ó', 'ú',
            'Á', 'É', 'Í', 'Ó', 'Ú',
            'ñ', 'Ñ',
        );
        $chars = array(
            'a', 'e', 'i', 'o', 'u',
            'a', 'e', 'i', 'o', 'u',
            'n', 'n',
        );

        $str = str_ireplace($charsES, $chars, $str);

        return $str;
    }
    //------------------------------------------------------------------
    /* UTF8 */
    //------------------------------------------------------------------
    public function urlEnc_utf8($url)
    {
        $url = urlencode(utf8_encode($url));
        return $url;
    }
    //-------------------------------------------
    public function urlDec_utf8($url)
    {
        $url = urldecode(utf8_decode($url));
        return $url;
    }
    //-----------------------------------------------------------------
}
