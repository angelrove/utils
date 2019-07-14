<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

class CronUtils
{
    //---------------------------------------------------------
    // Delete files
    //---------------------------------------------------------
    public static function log_rotate()
    {
        // get files -----
        $log_file = '-' . date('m-d') . '.inc';

        $file_1 = LOG_DIR . '/error' . $log_file;
        $file_2 = LOG_DIR . '/myError-WARNING' . $log_file;

        // Delete files ---
        if (file_exists($file_1)) {
            unlink($file_1);
        }
        if (file_exists($file_2)) {
            unlink($file_2);
        }
    }
    //---------------------------------------------------------
    public static function delete_cache_files($prefijo, $horas)
    {
        $horaAct = date('H');

        if (in_array($horaAct, $horas)) {
            printLog("del_cache: $prefijo");
            FileSystem::deleteFiles(DOC_ROOT_MAIN . '/_cache', false, $prefijo);
        }
    }
    //---------------------------------------------------------
}
