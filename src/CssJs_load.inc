<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;


class CssJs_load
{
  static private $async  = array(
     'js'  => 'async',
     'css' => 'media="none" onload="if(media!=\'all\')media=\'all\'"'
  );

  static private $doc_root  = '.';
  static private $dir_cache = '_cache';

  static private $path_cache = '';
  static private $url_cache  = '';

  //---
  static private $default_combined = true;
  static private $set_minify = false;

  //---
  static private $async_files = array();

  static private $list_css      = array();
  static private $list_css_http = array();

  static private $list_less     = array();

  static private $list_js       = array();
  static private $list_scripts  = array();

  static private $list_css_combined = array();
  static private $list_js_combined  = array();

  //---------------------------------------------------------------------
  // CONF
  //---------------------------------------------------------------------
  public static function _init($doc_root)
  {
     self::$doc_root = $doc_root;

     // Cache dir ----
     self::$path_cache = self::$doc_root.'/'.self::$dir_cache.'/';
     self::$url_cache  = '/'.self::$dir_cache.'/';
  }
  //---------------------------------------------------------------------
  public static function isProd($isProd=false)
  {
     // self::$default_combined = $isProd;
     self::$set_minify = $isProd;
  }
  //---------------------------------------------------------------------
  // SETERS
  //---------------------------------------------------------------------
  public static function set($file, $async=false)
  {
     // Combined -------
     if(self::$default_combined && !self::is_http($file))
     {
        if(!file_exists($file)) {
           trigger_error("File not found: $file", E_USER_NOTICE);
           return false;
        }

        if(self::get_type($file) == 'js') {
           self::$list_js_combined[$file]  = $file;
        } else {
           self::$list_css_combined[$file] = $file;
        }

     }
     // Not combined ---
     else
     {
       if(self::get_type($file) == 'js') {
          self::set_js($file, $async);
       } else {
          self::set_css($file, $async);
       }
     }
  }
  //---------------------------------------------------------------------
  //---------------------------------------------------------------------
  public static function set_js($file, $async=false)
  {
     self::$async_files[$file] = $async;
     self::$list_js[$file] = $file;
  }
  //---------------------------------------------------------------------
  public static function set_css($file, $async=false, $combined=false)
  {
     self::$async_files[$file] = $async;

     if($combined) {
       self::$list_css_combined[$file] = $file;
     }
     else {
       if(self::is_http($file)) {
          self::$list_css_http[$file] = $file;
       } else {
          self::$list_css[$file] = $file;
       }
     }
  }
  //---------------------------------------------------------------------
  public static function set_less($file, $async=false)
  {
     self::$async_files[$file] = $async;
     self::$list_less[$file] = $file;
  }
  //---------------------------------------------------------------------
  // Dependiendo de si usa o no clave, sera combined o no
  public static function set_script($script, $key='')
  {
    if($key) {
      self::$list_scripts[$key] = $script;
    } else {
      self::$list_scripts[] = $script;
    }
  }
  //---------------------------------------------------------------------
  // GETERS
  //---------------------------------------------------------------------
  public static function get_css($version='')
  {
    self::get_css_js_files(self::$list_css_http, 'css');
    self::get_css_js_combined('css', $version);
    self::get_css_js_files(self::$list_css, 'css');
    self::get_css_js_files(self::$list_less, 'less');
  }
  //---------------------------------------------------------------------
  public static function get_js($version='')
  {
    self::get_css_js_files(self::$list_js, 'js');
    self::get_css_js_combined('js', $version);
    self::get_js_scripts();
  }
  //---------------------------------------------------------------------
  // PRIVATE
  //---------------------------------------------------------------------
  private static function get_js_scripts()
  {
     $strScripts = self::read_scripts(false);

     if($strScripts) {
        echo PHP_EOL .'<script type="text/javascript">'.PHP_EOL.$strScripts.'</script>'.PHP_EOL;
     }
  }
  //---------------------------------------------------------------------
  private static function get_css_js_files($listFiles, $ext)
  {
    $strCssJs = '';

    foreach($listFiles as $file)
    {
       $strAsync = (self::$async_files[$file])? self::$async[$ext] : '';
       // $strAsync = '';

       if($ext == 'js') {
          $strCssJs .= '<script '.$strAsync.' type="text/javascript" src="'.$file.'"></script>'.PHP_EOL ;
       }
       elseif($ext == 'css') {
          $strCssJs .= '<link type="text/css" '.$strAsync.' href="'.$file.'" rel="stylesheet">'.PHP_EOL ;
       }
       elseif($ext == 'less') {
          $strCssJs .= '<link type="text/css" '.$strAsync.' href="'.$file.'" rel="stylesheet/less">'.PHP_EOL ;
       }
    }

    echo $strCssJs;
  }
  //---------------------------------------------------------------------
  private static function get_css_js_combined($ext, $version='1', $obfuscate=false)
  {
    //------------------
    $listFiles    = array(); // Files
    $list_scripts = array(); // Scripts

    if($ext == 'js') {
       $listFiles    = self::$list_js_combined;
       $list_scripts = self::$list_scripts;
    }
    elseif($ext == 'css') {
       $listFiles = self::$list_css_combined;
    }

    $listKeys = implode(';', $listFiles) . implode(';', array_keys($list_scripts));
    if(!$listKeys) {
       return;
    }

    // File cache ---------
    // El nombre se genera con el md5 de la cadena resultante de concatenar todos los nombres de archivos y claves de scripts.
    // De esta forma se regenera automáticamente al añadir o eliminar un archivo o script.
    $seccion = self::get_seccion();
    $fileName = $seccion.'_'.md5($listKeys).'.'.$ext;
    /* debug */ // print_r2('$fileName: '.$fileName);

    //----
    $cache_fileName  = $version.$fileName;
    $cache_file_path = self::$path_cache.$cache_fileName;
    $cache_file_url  = self::$url_cache.$cache_fileName;

    /** Write cache **/
    if(!file_exists($cache_file_path))
    {
       // Read
       $strCombined = self::read_files_combined($listFiles, $ext);
       if($list_scripts) {
          $strCombined .= self::read_scripts(true);
       }

       // Write
       /* debug */ // print_r2('Write >> '.$cache_file_path);
       file_put_contents($cache_file_path, $strCombined, FILE_APPEND | LOCK_EX);

       /** Delete prev. version **/
        $cache_fileName_prev  = ($version-1).$fileName;
        $cache_file_path_prev = self::$path_cache.$cache_fileName_prev;
        if(file_exists($cache_file_path_prev)) {
           unlink($cache_file_path_prev); //echo "unlink: '$cache_file_path_prev'";
        }
    }

    // OUT ---------------
    if($ext == 'js') {
       echo '<script type="text/javascript" src="'.$cache_file_url.'"></script>'.PHP_EOL ;
    } elseif($ext == 'css') {
       echo '<link type="text/css" href="'.$cache_file_url.'" rel="stylesheet">'.PHP_EOL ;
    }
    elseif($ext == 'less') {
       echo '<link type="text/css" href="'.$cache_file_url.'" rel="stylesheet/less">'.PHP_EOL ;
    }
  }
  //---------------------------------------------------------------------
  // READ CONTENT
  //---------------------------------------------------------------------
  private static function read_files_combined($listFiles, $ext)
  {
    // Header ---------
    $head = '/**'.PHP_EOL.
            ' * lib: angelrove\utils\CssJs_load'.PHP_EOL.
            ' *'.PHP_EOL.
            ' * url: '.$_SERVER['REQUEST_URI'].PHP_EOL.
            ' * files:'.PHP_EOL.
            ' '.implode(PHP_EOL.' ', $listFiles).PHP_EOL.
            ' */'.PHP_EOL.PHP_EOL;

    if(self::$set_minify) {
       $head = '/* lib: angelrove\utils\CssJs_load - url: '.$_SERVER['REQUEST_URI'].' */'.PHP_EOL;
    }

    // List files -----
    $strCombined = '';
    foreach($listFiles as $file)
    {
       // $strFile = file_get_contents($file);
       ob_start();
       include($file);
       $strFile = ob_get_clean();

       // Minify
       if(self::$set_minify) {
          if(!strpos($file, '.min.') && !strpos($file, '.packed.')) {
             $strFile = self::{'minify_'.$ext}($strFile);
          }
       }

       //---
       $parts = explode('/', $file, 3);
       $strCombined .= PHP_EOL.'/**-- '.$parts[2].' --**/'.PHP_EOL;
       $strCombined .= $strFile.PHP_EOL;
    }
    //-----------------

    return $head.$strCombined;
  }
  //---------------------------------------------------------------------
  private static function read_scripts($combined=false)
  {
     $strScripts = '';

     foreach(self::$list_scripts as $key => $script)
     {
        if($combined == true) {
           if(is_string($key)) { // solo los que tienen clave
              if(self::$set_minify) {
                 $script = self::minify_js($script);
              }
              $strScripts .= '/* '.$key.' */ '.$script.PHP_EOL;
              // print_r2("Combined >> script key: ".$key);
           }
        }
        else {
           if(is_numeric($key)) {
              $strScripts .= '/* '.$key.' */ '.$script.PHP_EOL;
              // print_r2("script key: ".$key);
           }
        }
     }

     //-------
     if($strScripts) {
        return '/* -- scripts -- */'.PHP_EOL . $strScripts;
     }
     return '';
  }
  //---------------------------------------------------------------------
  // UTILS
  //---------------------------------------------------------------------
  private static function get_type($file)
  {
     $end_3 = substr($file, -3);

     if($end_3 == '.js' || strpos($file, 'js?')) {
        return 'js';
     }
     elseif($end_3 == 'ess') {
        return 'less';
     }
     else {
        return 'css';
     }
  }
  //---------------------------------------------------------------------
  private static function is_http($file)
  {
     if(strpos($file, '//') === false) {
        return  false;
     }
     else {
        return true;
     }
  }
  //---------------------------------------------------------------------
  private static function get_seccion()
  {
     // Seccion ------
     $seccion = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
     $seccion = strtok($seccion, '/');

     if(!$seccion || $seccion == 'index.php') {
        $seccion = '_home';
     }
     /* debug */ // print_r2('seccion = '.$seccion);

     return $seccion;
  }
  //---------------------------------------------------------------------
  private static function minify_css($buffer)
  {
     /* remove comments */
     $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

     /* remove tabs, spaces, newlines, etc. */
     $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

     return $buffer;
  }
  //---------------------------------------------------------------------
  private static function minify_js($buffer)
  {
     /* remove comments */
     $buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $buffer);

     /* remove tabs, spaces, newlines, etc. */
     $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);

     /* remove other spaces before/after ) */
     $buffer = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);

     return $buffer;
  }
  //---------------------------------------------------------------------
}
