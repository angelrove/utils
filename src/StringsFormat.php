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
      $str = self::str_parseEsChars($str);
      $str = self::str_clean($str);

      return $str;
   }
   //------------------------------------------------------------------
   static function str_clean($string)
   {
     $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
     $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

     return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
   }
   //------------------------------------------------------------------
   static function removeSpecialChars($str)
   {
     $str = trim($str);
     if(!$str) return '';

     $charsES = array(' &','& ',',','"',"'",'´','/','%',"\\",'?','(',')','*','“','”',
                      );

     $chars   = array('&', '&', '', '', '', '', '', '', '',  '', '', '', '', '', '',
                      );

     // lower case
     $str = strtolower($str);

     // convertir caracteres
     $str = str_ireplace($charsES, $chars, $str);
     $str = str_replace('--', '', $str);

     return $str;
   }
   //------------------------------------------------------------------
   //------------------------------------------------------------------
   static function str_parseEsChars($str)
   {
     $charsES = array('á', 'é', 'í', 'ó', 'ú',
                      'Á', 'É', 'Í', 'Ó', 'Ú',
                      'à', 'è', 'ì', 'ò', 'ù',
                      'À', 'È', 'Ì', 'Ò', 'Ù',
                      'â', 'ê', 'î', 'ô', 'û',
                      'Â', 'Ê', 'Î', 'Ô', 'Û',
                      'ñ', 'Ñ',
                      );
     $chars   = array('a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'n', 'n',
                      );

     return str_ireplace($charsES, $chars, $str);
   }
   //------------------------------------------------------------------
   static function removeESchars($str, $limit=0)
   {
     $str = trim($str);
     if(!$str) return '';

     $charsES = array('á', 'é', 'í', 'ó', 'ú',
                      'Á', 'É', 'Í', 'Ó', 'Ú',
                      'à', 'è', 'ì', 'ò', 'ù',
                      'À', 'È', 'Ì', 'Ò', 'Ù',
                      'â', 'ê', 'î', 'ô', 'û',
                      'Â', 'Ê', 'Î', 'Ô', 'Û',
                      'ñ', 'Ñ', '&quot;',' - ',
                      '&', '#', ';', ':', ',',
                      '"', "'", '´', '/', '%',
                      "\\",'¿', '?', '!', '¡',
                      '.', '(', ')', '“', '‘','’',
                      '”', '+', '[', ']', 'º','ª'
                      );

     $chars   = array('a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'a', 'e', 'i', 'o', 'u',
                      'n', 'n', '',  '-',
                      '-', '',  '',  '-', '',
                      '',  '',  '',  '',  '-',
                      '',  '',  '',  '',  '',
                      '',  '',  '',  '',  '','',
                      '',  '',  '',  '',  '',''
                      );

     // lower case
     $str = strtolower($str);

     // convertir caracteres
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
   function urlEnc_utf8($url) {
     $url = urlencode(utf8_encode($url));
     return $url;
   }
   //-------------------------------------------
   function urlDec_utf8($url) {
     $url = urldecode(utf8_decode($url));
     return $url;
   }
   //-----------------------------------------------------------------
}