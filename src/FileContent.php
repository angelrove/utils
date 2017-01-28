<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

namespace angelrove\utils;


class FileContent
{
  //------------------------------------------------------------------
  public static function get_contents($file)
  {
     try {
        if(($ret = @file_get_contents($file)) === false) {
           throw new \Exception("Failed to open file [$file]");
        }

        return $ret;
     }
     catch (\Exception $e) {
        throw $e;
     }
  }
  //------------------------------------------------------------------
  public static function put_contents($file, $data, $flags=0)
  {
     try {
        if(($ret = @file_put_contents($file, $data, $flags)) === false) {
           throw new \Exception("Failed to open file [$file]");
        }

        return $ret;
     }
     catch (\Exception $e) {
        throw $e;
     }
  }
  //------------------------------------------------------------------
  /*
   * Usage:
   *  try {
   *     $strFile = UtilsBasic::include_return($file);
   *  }
   *  catch (\Exception $e) {
   *     throw new \Exception($e->getMessage());
   *  }
   *
   */
  public static function include_return($file)
  {
     try {
        ob_start();

        if((@include($file)) === false) {
           throw new \Exception("File not found [$file]");
        }
     }
     catch (\Exception $e) {
        ob_get_clean();
        throw $e;
     }

     return ob_get_clean();
  }
  //------------------------------------------------------------------
}
