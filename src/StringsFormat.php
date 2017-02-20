<?
/**
 *
 */

namespace angelrove\utils;


class StringsFormat
{
   //------------------------------------------------------------------
   // Parse strings
   //------------------------------------------------------------------
   // Set first character of any word to uppercase
   static function str_ucfirst($str)
   {
     $str = trim(strtolower($str));
     $listPalabras = explode(' ', $str);

     $res = '';
     foreach($listPalabras as $palabra) {
        $palabra[0] = strtoupper($palabra[0]);
        $res .= $palabra.' ';
     }
     return $res;
   }
   //------------------------------------------------------------------
   static function str_getFriendly($str)
   {
      $str = trim(strtolower($str));
      $str = self::removeESchars($str); // Â¿?
      $str = self::str_clean($str);

      return $str;
   }
   //------------------------------------------------------------------
   static function str_clean($string)
   {
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
   static function removeESchars($str, $limit=0)
   {
     $str = trim($str);
     if(!$str) return '';

     $charsES = array('Ã¡', 'Ã©', 'Ã­', 'Ã³', 'Ãº',
                      'Ã', 'Ã‰', 'Ã', 'Ã“', 'Ãš',
                      'Ã ', 'Ã¨', 'Ã¬', 'Ã²', 'Ã¹',
                      'Ã€', 'Ãˆ', 'ÃŒ', 'Ã’', 'Ã™',
                      'Ã¢', 'Ãª', 'Ã®', 'Ã´', 'Ã»',
                      'Ã‚', 'ÃŠ', 'ÃŽ', 'Ã”', 'Ã›',
                      'Ã±', 'Ã‘',
                      );
     $chars   = array('a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'n', 'n',
                      );

     // lower case
     $str = strtolower($str);

     // reemplazar caracteres
     $str = str_ireplace($charsES, $chars, $str);
     $str = str_replace('--', '', $str);

     $str = trim($str);
     $str = str_replace(' ', '-', $str);

     // size
     if($limit > 0) {
        $str = substr($str, 0, $limit);
     }

     return $str;
   }
   //------------------------------------------------------------------
   /* UTF8 */
   //------------------------------------------------------------------
   function urlEnc_utf8($url)
   {
     $url = urlencode(utf8_encode($url));
     return $url;
   }
   //-------------------------------------------
   function urlDec_utf8($url)
   {
     $url = urldecode(utf8_decode($url));
     return $url;
   }
   //-----------------------------------------------------------------
}
