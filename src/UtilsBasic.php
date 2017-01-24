<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;


Class UtilsBasic
{
  //------------------------------------------------------------------
  static function file_get_contents($file)
  {
     try {
        if(($ret = @file_get_contents($file)) === false)
        {
           throw new \Exception("Warning: UtilsBasic::file_get_contents($file): failed to open stream.");
        }

        return $ret;
     }
     catch (\Exception $e) {
        throw $e;
     }
  }
  //------------------------------------------------------------------
  static function file_put_contents($file, $data, $flags=0)
  {
     try {
        if(($ret = @file_put_contents($file, $data, $flags)) === false)
        {
           throw new \Exception("Warning: UtilsBasic::file_put_contents($file): failed to open stream");
        }

        return $ret;
     }
     catch (\Exception $e) {
        throw $e;
     }
  }
  //------------------------------------------------------------------
  //------------------------------------------------------------------
  static function include_return($file)
  {
     ob_start();
     include($file);
     return ob_get_clean();
  }
  //------------------------------------------------------------------
  static function parse_domain($host='')
  {
    if(!$host) {
       $host = $_SERVER['HTTP_HOST'];
    }

    $parts = explode('.', $host);
    $num_parts = count($parts);

    //----
    $domain = $parts[$num_parts-2].'.'.$parts[$num_parts-1];

    //----
    $subdomains = array_slice($parts, 0, $num_parts - 2 );
    $subdomain = implode('.', $subdomains);

    //----
    return array(
       'main'  => $domain,
       'other' => $subdomain,
       'subdomain' => $subdomains,
    );

  }
  //------------------------------------------------------------------
  // Arrays
  //------------------------------------------------------------------
  static function array_is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
  }
  //------------------------------------------------------------------
  // Introduce uno o más elementos al principio de la matriz asociativa
  static function array_unshift_assoc(&$arr, $key, $val) {
    $arr = array_reverse($arr, true);
    $arr[$key] = $val;
    $arr = array_reverse($arr, true);
    return count($arr);
  }
  //------------------------------------------------------------------
  // Concatena todas las cadenas de un array mediante un separador dado
  static function array_implode($sep, $listStr) {
    $strResult = '';

    $c=0;
    foreach($listStr AS $value) {
       if(!$value) {
          continue;
       }

       if($c > 0) {
          $strResult .= $sep;
       }
       $strResult .= $value;

       $c = 1;
    }

    return $strResult;
  }
  //------------------------------------------------------------------
  // Parse strings
  //------------------------------------------------------------------
  static function str_getFriendly($str) {
    $str = trim(strtolower($str));
    $str = self::str_parseEsChars($str);
    $str = self::str_clean($str);
    return $str;
  }
  //------------------------------------------------------------------
  static function str_clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
  }
  //------------------------------------------------------------------
  static function str_parseEsChars($str) {
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
  //------------------------------------------------------------------
  /*
   * Envía un email en formato HTML.
   *   - Elimina la codificación UTF8.
   *   - Ejemplo de $from = 'Galletas Fontaneda <gfontaneda@gmail.com>';
   */
  static function sendEMail($from, $mailto, $bcc, $asunto, $body, $ReplyTo='')
  {
    $from   = utf8_decode($from);
    $asunto = utf8_decode($asunto);
    $body   = utf8_decode($body);

    // Headers ----------
    $header  = "From: $from\n";
    $header .= "X-Mailer: PHP/". phpversion() ."\n";
    $header .= "Mime-Version: 1.0\n";
    $header .= "Content-Type: text/html; charset=iso-8859-1\n";

    if($bcc) {
       $header .= 'Bcc: '.$bcc ."\n";
    }
    if($ReplyTo) {
       $header .= 'Reply-To: '   .$ReplyTo."\n".
                  'Return-Path: '.$ReplyTo."\n";
    }

    // Body -------------
    $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
  <html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <style>
    body {
     background=#fff;
     font-family: Verdana, Arial;
     line-height: 21px;
    }
    </style>
  </head>
  <body>
    '.$body.'
  </body>
  </html>';

    // Mail -------------
    if(IS_LOCALHOST) {
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
  /**
   * $DEFAULT_DIR: no es la ruta completa, es el directorio de uploads.
   * Nota: quizás debería estar en WObjects
   */
  static function InputFile_getFile($bbdd_file, $DEFAULT_DIR='')
  {
    if(!$bbdd_file) {
       return false;
    }

    global $CONFIG_APP;
    if(isset($CONFIG_APP)) {
       $URL_UPLOADS  = $CONFIG_APP['url_uploads'];
       $PATH_UPLOADS = $CONFIG_APP['path_uploads'];
    }
    else {
       $URL_UPLOADS  = '/_uploads';
       $PATH_UPLOADS = '_uploads';
    }

    //---------------
    $datos = array();

    $params_foto = explode('#', $bbdd_file);
    $datos['data'] = $params_foto;

    /** Params **/
     $datos['name']    = $params_foto[0];
     $datos['nameUser']= (isset($params_foto[1]))? $params_foto[1] : '';
     $datos['fecha']   = (isset($params_foto[2]))? $params_foto[2] : '';
     $datos['size']    = (isset($params_foto[3]))? $params_foto[3] : '';
     $datos['mime']    = (isset($params_foto[4]))? $params_foto[4] : '';
     $datos['dir']     = (isset($params_foto[5]))? $params_foto[5] : '';

     //----
     if($DEFAULT_DIR) {
        $datos['dir'] = $DEFAULT_DIR;
     }
     if($datos['dir']) {
        $datos['dir'] .= '/';
     }
     //----

    /** Ruta (URL) **/
     $datos['ruta']             = $URL_UPLOADS.'/'.$datos['dir'];
     $datos['ruta_completa']    = $datos['ruta'].$datos['name'];
     $datos['ruta_completa_th'] = $datos['ruta'].'th_'.$datos['name'];

    /** Ruta (path) **/
     $datos['path']             = $PATH_UPLOADS.'/'.$datos['dir'];
     $datos['path_completo']    = $datos['path'].$datos['name'];
     $datos['path_completo_th'] = $datos['path'].'th_'.$datos['name'];

    return $datos;
  }
  //----------------------------------------------------------
  // $type: '', 'lightbox'
  static function get_htm_img($datos, $type='', $alt='', $class='', $op_nofoto=false, $link='')
  {
     // echo "=> UtilsBasic::get_htm_img($type, $alt, $class, $op_nofoto)";
     // print_r2($datos);

     $img = '';

     //-------
     $alt = ($alt)? htmlentities($alt) : $datos['name'];

     //---------------
     // no foto
     if(!$datos) {
        if($op_nofoto) {
           $img =
             '<div class="htm_img img-thumbnail '.$class.'" style="text-align:center">'.
               '<i class="fa-nofoto fa fa-picture-o fa-5x" aria-hidden="true"></i>'.
             '</div>';
        }
        return $img;
     }
     //---------------
     // lightbox
     if($type == 'lightbox')
     {
        $img =
          '<a class="htm_img img-thumbnail '.$class.'" href="'.$datos['ruta_completa'].'" data-lightbox="file_img">'.
             '<img class="img-responsive"'.
                  'src="'.$datos['ruta_completa_th'].'"'.
                  'onerror="this.onerror=null;this.src=\''.$datos['ruta_completa'].'\'"'.
                  'alt="'.$alt.'">'.
          '</a>';
     }
     //---------------
     // basic image
     else {
        $img = '<img class="htm_img img-responsive '.$class.'" src="'.$datos['ruta_completa'].'" alt="'.$alt.'">';
     }

     // Link ----
     if($link) {
        $img = '<a href="'.$link.'">'.$img.'</a>';
     }

     return $img;
  }
  //--------------------------------------------------------------
  static function InputFile_getFile2($bbdd_file)
  {
    if(!$bbdd_file) {
      return false;
    }

    $file = array();
    $params_foto = explode('#', $bbdd_file);

    /** Params **/
     $file['name'] = $params_foto[0];
     $file['dir']  = (isset($params_foto[5]))? $params_foto[5] : '';

     $file['dir'] .= '/';

    /** Ruta (URL) **/
     $file['ruta']    = $file['dir'].$file['name'];
     $file['ruta_th'] = $file['dir'].'th_'.$file['name'];

    return $file;
  }
  //------------------------------------------------------------------
  // Obtener un CSV a partir de un array
  static function get_csv($listFields, $listRows, $SEP, $isDebug=false, $fileName='export.csv')
  {
    $LINE_RET = ($isDebug)? '<br>' : "\n";

    //--------------
    $header = '';
    foreach($listFields as $f_name) {
       $header .= $SEP.'"'.$f_name.'"';
    }
    $header = ltrim($header, $SEP);
    $header .= $LINE_RET;

    //--------------
    $str_csv = '';
    foreach($listRows as $row)
    {
      $line = '';
      foreach($listFields as $f_name)
      {
         $value = trim($row[$f_name]);

         if(!$isDebug) {
            $value = addslashes($value);
         }
         $value = str_replace(array("\n", "\r"), ' ', $value);

         $line .= $SEP.'"'.$value.'"';
      }
      $line = ltrim($line, $SEP);
      $str_csv .= $line.$LINE_RET;
    }

    // OUT ---------
    if($isDebug) {
       return $header.$str_csv;
    }
    else {
       // header('Content-type: application/zip');
       header('Content-Disposition: attachment; filename="'.$fileName.'"');
       // header("Pragma: no-cache");
       // header("Expires: 0");
       echo $header.$str_csv;
       exit();
    }
  }
  //------------------------------------------------------------------
}
