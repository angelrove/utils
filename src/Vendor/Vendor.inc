<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;

use angelrove\utils\CssJs_load;


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
         CssJs_load::set($file_path);
      }

   }
   //-------------------------------------------------
}