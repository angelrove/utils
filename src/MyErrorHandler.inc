<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2016
 *
 * Nota: Cuando 'display_errors' es true, el archivo de
 *       error por defecto se escribirá siempre.
 *
 */

namespace angelrove\utils;


class MyErrorHandler
{
  private static $display_errors;
  private static $PATH_LOGS;
  private static $file_pref;

  //------------------------------------------------------------------
  static function init($display_errors, $PATH_LOGS, $file_pref='')
  {
      self::$display_errors = $display_errors;
      self::$PATH_LOGS = $PATH_LOGS;
      self::$file_pref = $file_pref;

      //-------------
      ini_set('error_log',      self::$PATH_LOGS.'/error-'.date('d').'.log');
      ini_set('display_errors', self::$display_errors);
      set_error_handler("angelrove\utils\MyErrorHandler::handler");
  }
  //------------------------------------------------------------------
  static function handler($errno, $errstr, $errfile, $errline)
  {
      // Para códigos de error que no están incluidos en 'error_reporting' (ejem.: se utiliza '@')
      if(!(error_reporting() & $errno)) {
         return;
      }

      //--------------
      switch($errno) {
        case E_USER_NOTICE:
        case E_USER_ERROR:
          self::write_log('user', $errstr, $errfile, $errline);
        break;

        case E_NOTICE:
          self::write_log('notice', $errstr, $errfile, $errline);
        break;

        case E_WARNING:
          self::write_log('warning', $errstr, $errfile, $errline);
        break;

        default:
          return false; // Ejecutar el gestor de errores interno de PHP
        break;
      }

      // Ejecuta siempre el gestor de errores interno de PHP (según 'display_errors') ------
      //  - necesario para que se muestren los errores en pantalla
      //  - escribirá los errores en la ruta por defecto definida en 'error_log'
      if(self::$display_errors) {
         return false;
      }
  }
  //------------------------------------------------------------------
  static private function write_log($id_file, $errstr, $errfile, $errline)
  {
     //-------
     $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
     $paramUrl    = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
     // $param_agent = (isset($_SERVER['HTTP_USER_AGENT']))? '['.$_SERVER['HTTP_USER_AGENT'].']' : '[no USER_AGENT]';

     $msg = "[".date('h:i:s')."] [$REMOTE_ADDR] [$paramUrl] $errstr in file $errfile line $errline".PHP_EOL;

     //-------
     $file_name = self::$PATH_LOGS.'/'.
                  'handler-'.self::$file_pref.$id_file.'-'.date('d').'.log';

     //-------
     file_put_contents($file_name, $msg, FILE_APPEND);
  }
  //------------------------------------------------------------------
}