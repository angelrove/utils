<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;

use angelrove\utils\CssJsLoad;


class Vendor
{
   // static private $path_vendor = '';

   static private $paths     = array();
   static private $resources = array();

   //-------------------------------------------------
   // static function set_path_vendor($path)
   // {
   //    self::$path_vendor = $path;
   // }
   //-------------------------------------------------
   static function conf($key, $path, $files)
   {
      self::$paths[$key]     = $path;
      self::$resources[$key] = $files;
   }
   //-------------------------------------------------
   static function get_path($key)
   {
      return self::$paths[$key];
   }
   //-------------------------------------------------
   static function get_url($key)
   {
      $url = self::get_path($key);
      $url = str_replace(PATH_VENDOR, '', $url);
      $url = URL_VENDOR.$url;

      return $url;
   }
   //-------------------------------------------------
   // Composer
   //-------------------------------------------------
   static function get_path_vendor($namespace, $classname)
   {
      $map = require DOCUMENT_ROOT.'/../vendor/composer/autoload_psr4.php';
      // print_r2($map);
      $ret = $map[$namespace.'\\'][0];
      return $ret;
   }
   //-------------------------------------------------
   static function get_url_vendor($namespace)
   {
      // Get Composer autoloads ---
      $json = json_decode(file_get_contents(DOCUMENT_ROOT.'/../composer.json'), true);
      $composer_autoloads = $json['autoload']['psr-4'];
      // print_r($composer_autoloads);

      $url = '/'.$composer_autoloads[$namespace.'\\'];
      // print_r2($url);

      return $url;
   }
   //-------------------------------------------------
   //-------------------------------------------------
   static function show_paths()
   {
      print_r2(self::$paths);
   }
   //-------------------------------------------------
   static function usef($key, $op='')
   {
      foreach(self::$resources[$key] as $file)
      {
         self::inc_file($key, $file);
      }
   }
   //-------------------------------------------------
   static private function inc_file($key, $file)
   {
      $file_path = self::$paths[$key].$file;
      $ext = substr($file, -4);

      // PHP -------
      if($ext == '.php' || $ext == '.inc') {
         include_once($file_path);
      }
      // js / css ---
      else {
         CssJsLoad::set($file_path);
      }

   }
   //-------------------------------------------------
}