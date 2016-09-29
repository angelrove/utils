<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;


class FileSystem {
  //$ruta, $recursive, $testing

  //--------------------------------------------------------------------
  public static function deleteFiles($ruta, $testing, $cadena='')
  {
    $listFiles = self::getFiles($ruta, false);

    foreach($listFiles as $file) {
       $delFile = $file['ruta'].$file['name'];

       // Find
       if($cadena) {
          if(substr($file['name'], 0, strlen($cadena)) != $cadena) {
             continue;
          }
       }

       // Delete
       if($testing == true) {
          echo "=> $delFile <br>";
       }
       else {
          if(!@unlink($delFile)) {
             return "no se pudo eliminar el archivo: [$delFile]";
          }
       }
    }

  }
  //--------------------------------------------------------------------
  /* Renombrar archivos */
  //--------------------------------------------------------------------
  public static function substrReplace($ruta, $recursive, $testing, $cadena_sustituta, $comienzo, $longitud)
  {
    $listFiles = self::getFiles($ruta, $recursive);
    foreach($listFiles as $file) {
      $fileNameNew = substr_replace($file['name'], $cadena_sustituta, $comienzo, $longitud);
      self::renameFile($file['ruta'].$file['name'], $file['ruta'].$fileNameNew, $testing);
    }
  }
  //--------------------------------------------------------------------
  public static function strReplace($ruta, $recursive, $testing, $cadena_buscada, $cadena_sustituta)
  {
    $strResult = '';

    $listFiles = self::getFiles($ruta, $recursive);
    foreach($listFiles as $file) {
       $fileNameNew = str_replace($cadena_buscada, $cadena_sustituta, $file['name']);
       $strResult .= self::renameFile($file['ruta'].$file['name'], $file['ruta'].$fileNameNew, $testing);
    }

    return $strResult;
  }
  //--------------------------------------------------------------------
  private static function renameFile($fileName, $fileNameNew, $testing)
  {
    $strResult = '';

    if($fileName != $fileNameNew) {
       $strResult .= "\n -> \"$fileNameNew\"";
       if($testing == false) {
          rename($fileName, $fileNameNew);
       }
    }

    return $strResult;
  }
  //--------------------------------------------------------------------
  /* Trabajo con directorios */
  //--------------------------------------------------------------------
  public static function copyDir($origen, $destino)
  {
  }
  //--------------------------------------------------------------------
  public static function moveDir($origen, $destino)
  {
  }
  //--------------------------------------------------------------------
  public static function deleteDir($dir)
  {
     if(!is_dir($dir)) return 'el directorio no existe';

    // Files
     $listFiles = self::getFiles($dir, true);

     foreach($listFiles as $file) {
        $delFile = $dir.'/'.$file['ruta'].$file['name'];
        //echo "=> $delFile <br>"; continue;

        if(!@unlink($delFile)) {
           return "no se pudo eliminar el archivo: [$delFile]";
        }
     }

    // Carpetas
     $listDirs = self::getDirs($dir, true);
     rsort($listDirs);

     foreach($listDirs as $delDir) {
        $delDir = $dir.'/'.$delDir;
        //echo "=> $delDir <br>"; continue;

        if(!@rmdir($delDir)) {
           echo "no se pudo eliminar el directorio: [$delDir] <br>";
        }
     }

     //echo "=> $dir <br>"; return '';
     if(!@rmdir($dir)) {
        return "no se pudo eliminar el directorio: [$dir]";
     }

     return '';
  }
  //--------------------------------------------------------------------
  /* Obtener el listados de archivos y directorios */
  //--------------------------------------------------------------------
  public static function getFiles($dir, $recursive=false)
  {
     $ret = self::getFiles2(strlen($dir), $dir, $recursive);
     return $ret;
  }
  //--------------------------------------------------------------------
  private static function getFiles2($lenInicio, $dir, $recursive) {
    ini_set("max_execution_time", 10);

    if(!is_dir($dir)) {
       $strErr = "\n".'FileSystem::getFiles2(): Ruta incorrecta: "'.$dir.'"';
       trigger_error($strErr, E_USER_NOTICE);
       return array();
    }

    $files = array();

    $root = opendir($dir);
    while($file = readdir($root)){
       if($file == "." || $file == "..") {
          continue;
       }

       $rutaFile = $dir.'/'.$file;

       if(is_dir($rutaFile) && $recursive) {
         // Subdirectorio
         $files = array_merge($files, self::getFiles2($lenInicio, $rutaFile, $recursive));
       }
       else {
         //$dirX = substr($dir."/", $lenInicio+1);
         $dirX = $dir. "/" ;

         $files[]['name'] = $file;
         $files[count($files) - 1]['ruta'] = $dirX;
       }

    }

    return $files;
  }
  //--------------------------------------------------------------------
  public static function getDirs($dir, $recursive=false)
  {
     $ret = self::getDirs2(strlen($dir), $dir, $recursive);
     return $ret;
  }

  private static function getDirs2($lenInicio, $dir, $recursive=false)
  {
    if(!is_dir($dir)) die ("\n".'Ruta incorrecta: "'.$dir.'"');

    $res = array();

    $root  = opendir($dir);
    while($file = readdir($root)){
       if($file == "." || $file == "..") {
         continue;
       }

       $rutaFile = $dir.'/'.$file;

       if(is_dir($rutaFile)) {
         $dirX = substr($rutaFile, $lenInicio+1);
         $res[] = $dirX.'/';

         // Subdirectorio
         if($recursive) {
            $res = array_merge($res, self::getDirs2($lenInicio, $rutaFile, $recursive));
         }
       }

    }

    return $res;
  }
  //--------------------------------------------------------------------
}
?>
