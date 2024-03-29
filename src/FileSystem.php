<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;

class FileSystem
{
    //--------------------------------------------------------------------
    public static function deleteFiles($ruta, $testing, $cadena = '')
    {
        $listFiles = self::getFiles($ruta, false);
        $listDeletes = [];

        foreach ($listFiles as $file) {
            $delFile = $file['ruta'] . $file['name'];

            // Find
            if ($cadena) {
                if (substr($file['name'], 0, strlen($cadena)) != $cadena) {
                    //print_r2($file['name'] .'  >>  '. $cadena);
                    continue;
                }
            }

            // Delete
            if ($testing == true) {
                echo "=> $delFile <br>";
            } else {
                unlink($delFile);
                $listDeletes[] = $file['name'];

                // if(!@unlink($delFile)) {
                //    return "no se pudo eliminar el archivo: [$delFile]";
                // }
            }
        }

        return $listDeletes;
    }
    //--------------------------------------------------------------------
    /* Renombrar archivos */
    //--------------------------------------------------------------------
    public static function substrReplace($ruta,
                                         $recursive,
                                         $testing,
                                         $cadena_sustituta,
                                         $comienzo,
                                         $longitud)
    {
        $listFiles = self::getFiles($ruta, $recursive);
        foreach ($listFiles as $file) {
            $fileNameNew = substr_replace($file['name'], $cadena_sustituta, $comienzo, $longitud);
            self::renameFile($file['ruta'] . $file['name'], $file['ruta'] . $fileNameNew, $testing);
        }
    }
    //--------------------------------------------------------------------
    public static function strReplaceNames($ruta, $recursive, $testing, $cadena_buscada, $cadena_sustituta)
    {
        $strResult = '';

        $listFiles = self::getFiles($ruta, $recursive);
        foreach ($listFiles as $file) {
            $fileNameNew = str_replace($cadena_buscada, $cadena_sustituta, $file['name']);
            $strResult .= self::renameFile($file['ruta'] . $file['name'], $file['ruta'] . $fileNameNew, $testing);
        }

        return $strResult;
    }
    //--------------------------------------------------------------------
    public static function strReplace($ruta, $cadena_buscada, $cadena_sustituta)
    {
        $strResult = '';

        $listFiles = self::getFiles($ruta, true);
        foreach ($listFiles as $file) {
            $str = file_get_contents($file['ruta'] . $file['name']);
            $str = str_replace($cadena_buscada,  $cadena_sustituta, $str);
            file_put_contents($file['ruta'] . $file['name'], $str);
        }

        return $strResult;
    }
    //--------------------------------------------------------------------
    private static function renameFile($fileName, $fileNameNew, $testing)
    {
        $strResult = '';

        if ($fileName != $fileNameNew) {
            $strResult .= "\n -> \"$fileNameNew\"";
            if ($testing == false) {
                rename($fileName, $fileNameNew);
            }
        }

        return $strResult;
    }
    //--------------------------------------------------------------------
    /* Folders */
    //--------------------------------------------------------------------
    public static function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            trigger_error('FileSystem::deleteDir(): Ruta incorrecta: "' . $dir . '"');
            return false;
        }

        // Files
        $listFiles = self::getFiles($dir, true);

        foreach ($listFiles as $file) {
            $delFile = $dir . '/' . $file['ruta'] . $file['name'];
            //echo "=> $delFile <br>"; continue;

            if (!@unlink($delFile)) {
                return "no se pudo eliminar el archivo: [$delFile]";
            }
        }

        // Carpetas
        $listDirs = self::getDirs($dir, true);
        rsort($listDirs);

        foreach ($listDirs as $delDir) {
            $delDir = $dir . '/' . $delDir;
            //echo "=> $delDir <br>"; continue;

            if (!@rmdir($delDir)) {
                echo "no se pudo eliminar el directorio: [$delDir] <br>";
            }
        }

        //echo "=> $dir <br>"; return '';
        if (!@rmdir($dir)) {
            return "no se pudo eliminar el directorio: [$dir]";
        }

        return '';
    }
    //--------------------------------------------------------------------
    /* Obtener el listados de archivos y directorios */
    //--------------------------------------------------------------------
    public static function getFiles($dir, $recursive = false)
    {
        $ret = self::getFiles2(strlen($dir), $dir, $recursive);
        return $ret;
    }
    //--------------------------------------------------------------------
    private static function getFiles2($lenInicio, $dir, $isRecursive)
    {
        // print_r2($dir);

        ini_set("max_execution_time", 10);

        if (!is_dir($dir)) {
            $strErr = 'FileSystem::getFiles2(): Ruta incorrecta: [' . $dir . ']';
            trigger_error($strErr, E_USER_NOTICE);
            return array();
        }

        $files = array();

        $root = opendir($dir);
        while ($file = readdir($root)) {
            if ($file == "." || $file == "..") {
                continue;
            }

            $rutaFile = $dir . '/' . $file;

            if (is_dir($rutaFile) && $isRecursive) {
                // Subdirectorio
                $files = array_merge($files, self::getFiles2($lenInicio, $rutaFile, $isRecursive));
            } else {
                //$dirX = substr($dir."/", $lenInicio+1);
                $dirX = $dir . "/";

                $files[]['name']                  = $file;
                $files[count($files) - 1]['ruta'] = $dirX;
            }

        }

        return $files;
    }
    //--------------------------------------------------------------------
    public static function getDirs($dir, $isRecursive = false)
    {
        $ret = self::getDirs2(strlen($dir), $dir, $isRecursive);
        return $ret;
    }

    private static function getDirs2($lenInicio, $dir, $isRecursive = false)
    {
        if (!is_dir($dir)) {
            die("\n" . 'Ruta incorrecta: "' . $dir . '"');
        }

        $res = array();

        $root = opendir($dir);
        while ($file = readdir($root)) {
            if ($file == "." || $file == "..") {
                continue;
            }

            $rutaFile = $dir . '/' . $file;

            if (is_dir($rutaFile)) {
                $dirX  = substr($rutaFile, $lenInicio + 1);
                $res[] = $dirX . '/';

                // Subdirectorio
                if ($isRecursive) {
                    $res = array_merge($res, self::getDirs2($lenInicio, $rutaFile, $isRecursive));
                }
            }

        }

        return $res;
    }
    //--------------------------------------------------------------------
    public static function recurse_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);

        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
    //--------------------------------------------------------------------
}
