<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

use angelrove\utils;


class CronUtils
{
    //---------------------------------------------------------
    // Delete files
    //---------------------------------------------------------
    static function log_rotate()
    {
     // get files -----
     $log_file = '-'.date('m-d').'.inc';

     $file_1 = LOG_DIR.'/error'.$log_file;
     $file_2 = LOG_DIR.'/myError-WARNING'.$log_file;

     // Delete files ---
     if(file_exists($file_1)) {
        unlink($file_1);
     }
     if(file_exists($file_2)) {
        unlink($file_2);
     }
    }
    //---------------------------------------------------------
    static public function delete_cache_files($prefijo, $horas) {
      $horaAct = date('H');

      if(in_array($horaAct, $horas))
      {
         printLog("del_cache: $prefijo");
         FileSystem::deleteFiles(BASE_DIR.'/_cache', false, $prefijo);
      }
    }
    //---------------------------------------------------------
}