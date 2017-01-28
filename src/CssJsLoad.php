<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;

use angelrove\utils\FileContent;


class CssJsLoad
{
  static private $async  = array(
     'js'  => 'async',
     'css' => 'media="none" onload="if(media!=\'all\')media=\'all\'"'
  );

  static private $cache_enabled = true;

  static private $path_cache = '';
  static private $url_cache  = '';

  //---
  static private $default_combined = true;
  static private $set_minify = false;

  //---
  static private $async_files = array();

  static private $list_css      = array();
  static private $list_css_http = array();
  static private $list_css_blocks = array();

  static private $list_less     = array();

  static private $list_js       = array();
  static private $list_scripts  = array();

  static private $list_css_combined = array();
  static private $list_js_combined  = array();

  static private $called_get_cssjs = false;


  //---------------------------------------------------------------------
  // CONF
  //---------------------------------------------------------------------
  public static function __init($path_cache, $url_cache)
  {
     self::$path_cache = $path_cache;
     self::$url_cache  = $url_cache;
  }
  //---------------------------------------------------------------------
  public static function isProd($isProd=false)
  {
     // self::$default_combined = $isProd;
     self::$set_minify = $isProd;
  }
  //---------------------------------------------------------------------
  // SETTERS
  //---------------------------------------------------------------------
  public static function check_get_calls()
  {
     if(self::$called_get_cssjs == true) {
        // trigger_error("Called 'set()' after 'get()'", E_USER_NOTICE);
        throw new \Exception("Called 'set()' after 'get()'", E_USER_WARNING);
     }
  }
  //---------------------------------------------------------------------
  public static function set($file, $async=false)
  {
     // Combined -------
     if(self::$default_combined && !self::is_http($file))
     {
        if(!file_exists($file)) {
           // trigger_error("File not found: $file", E_USER_NOTICE);
           throw new \Exception("File not found: $file", E_USER_WARNING);
        }

        if(self::get_type($file) == 'js') {
           self::set_js_combined($file);
        } else {
           self::set_css_combined($file);
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
  public static function set_js($file, $async=false)
  {
     self::check_get_calls();

     self::$async_files[$file] = $async;
     self::$list_js[$file] = $file;
  }
  //---------------------------------------------------------------------
  public static function set_css($file, $async=false, $combined=false)
  {
     self::check_get_calls();

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
     self::check_get_calls();

     self::$async_files[$file] = $async;
     self::$list_less[$file] = $file;
  }
  //---------------------------------------------------------------------
  // Dependiendo de si usa o no clave, sera combined o no
  public static function set_script($script, $key='')
  {
     self::check_get_calls();

     if($key) {
        self::$list_scripts[$key] = $script;
     } else {
        self::$list_scripts[] = $script;
     }
  }
  //---------------------------------------------------------------------
  public static function set_css_block($css, $key='')
  {
     self::check_get_calls();

     if($key) {
        self::$list_css_blocks[$key] = $css;
     } else {
        self::$list_css_blocks[] = $css;
     }
  }
  //---------------------------------------------------------------------
  // PRIVATE
  //---------------------------------------------------------------------
  private static function set_js_combined($file)
  {
     self::check_get_calls();

     self::$list_js_combined[$file] = $file;
  }
  //---------------------------------------------------------------------
  private static function set_css_combined($file)
  {
     self::check_get_calls();

     self::$list_css_combined[$file] = $file;
  }
  //---------------------------------------------------------------------
  // GETTERS
  //---------------------------------------------------------------------
  public static function get_css($version='1', $cache_enabled=true)
  {
    // called: get() ---
    self::$called_get_cssjs = true;
    //------------------

    self::$cache_enabled = $cache_enabled;

    self::get_css_js_files(self::$list_css_http, 'css');
    self::get_css_js_combined('css', $version);
    self::get_css_js_files(self::$list_css, 'css');
    self::get_css_js_files(self::$list_less, 'less');
  }
  //---------------------------------------------------------------------
  public static function get_js($version='1')
  {
    // called: get() ---
    self::$called_get_cssjs = true;
    //------------------

    self::get_css_js_files(self::$list_js, 'js');
    self::get_css_js_combined('js', $version);
    self::get_js_scripts();
  }
  //---------------------------------------------------------------------
  // PRIVATE
  //---------------------------------------------------------------------
  private static function get_js_scripts()
  {
     $strScripts = self::read_scripts(self::$list_scripts, false);
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
  // El nombre se genera con el md5 de la cadena resultante de concatenar todos los nombres de archivos y claves de scripts.
  // De esta forma se regenera automáticamente al añadir o eliminar un archivo o script.
  private static function get_css_js_combined($ext, $version='1', $obfuscate=false)
  {
    /** List files **/
    $listFiles    = array(); // Files
    $list_scripts = array(); // Scripts

    if($ext == 'js') {
       $listFiles    = self::$list_js_combined;
       $list_scripts = self::$list_scripts;
    }
    elseif($ext == 'css') {
       $listFiles    = self::$list_css_combined;
       $list_scripts = self::$list_css_blocks;
    }
    else {
       return false;
    }


    /** File cache **/
    // listKeys
    $listKeys = implode(';', $listFiles) . implode(';', array_keys($list_scripts));
    if(!$listKeys) {
       return;
    }

    // File name
    $cache_fileName  = $version.'_'.md5($listKeys).'.'.$ext;
    $cache_file_path = self::$path_cache.'/'.$cache_fileName;
    $cache_file_url  = self::$url_cache.'/'.$cache_fileName;
    // echo "DEBUG - CssJsLoad: cache file: $cache_file_path";

    /** Cache disabled **/
    if(self::$cache_enabled === false)
    {
       @unlink($cache_file_path);
    }

    /** Write cache file **/
    if(!file_exists($cache_file_path))
    {
       // Read ---
       $strCombined = self::read_files_combined($listFiles, $ext);

       if($list_scripts) {
          if($ext == 'js') {
             $strCombined .= self::read_scripts($list_scripts, true);
          } else {
             $strCombined .= self::read_css_blocks($list_scripts);
          }
       }

       // Write ---
       file_put_contents($cache_file_path, $strCombined, FILE_APPEND | LOCK_EX);
    }

    /** OUT **/
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
            ' * lib: angelrove\utils\CssJsLoad'.PHP_EOL.
            ' *'.PHP_EOL.
            ' * url: '.$_SERVER['REQUEST_URI'].PHP_EOL.
            ' * files:'.PHP_EOL.
            ' '.implode(PHP_EOL.' ', $listFiles).PHP_EOL.
            ' */'.PHP_EOL.PHP_EOL;

    if(self::$set_minify) {
       $head = '/* lib: angelrove\utils\CssJsLoad - url: '.$_SERVER['REQUEST_URI'].' */'.PHP_EOL;
    }

    // List files -----
    $strCombined = '';
    $c=0;
    foreach ($listFiles as $file)
    {
       // Read file ---
       // print_r2($file);
       try {
          $strFile = FileContent::include_return($file);
       }
       catch (\Exception $e) {
          throw new \Exception($e->getMessage());
       }

       // Minify ---
       if(self::$set_minify) {
          if(!strpos($file, '.min.') && !strpos($file, '.packed.')) {
             $strFile = self::{'minify_'.$ext}($strFile);
          }
       }

       //---
       $parts = pathinfo($file);
       // print_r2($parts);
       $strCombined .= PHP_EOL.'/**-- '.$parts['basename'].' --**/'.PHP_EOL;
       $strCombined .= $strFile.PHP_EOL;
    }
    //-----------------

    return $head.$strCombined;
  }
  //---------------------------------------------------------------------
  private static function read_scripts($list_scripts, $combined=false)
  {
     $strScripts = '';

     foreach($list_scripts as $key => $script)
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
        return '/* -- read_scripts() -- */'.PHP_EOL . $strScripts;
     }
     return '';
  }
  //---------------------------------------------------------------------
  private static function read_css_blocks($list_css_blocks)
  {
     $strBlocks = '';
     foreach($list_css_blocks as $key => $block)
     {
        if(self::$set_minify) {
           $block = self::minify_css($block);
        }
        $strBlocks .= '/* '.$key.' */ '.$block.PHP_EOL;
     }

     //-----
     if($strBlocks) {
        return '/* -- read_css_blocks() -- */'.PHP_EOL . $strBlocks;
     } else {
        return '';
     }
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
