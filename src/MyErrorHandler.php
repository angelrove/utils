<?php
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
    public static function init($display_errors, $PATH_LOGS, $file_pref = '')
    {
        self::$display_errors = $display_errors;
        self::$PATH_LOGS      = $PATH_LOGS;
        self::$file_pref      = $file_pref;

        //-------------
        ini_set('error_log', self::$PATH_LOGS . '/' . self::$file_pref . 'php-error.log');
        ini_set('display_errors', self::$display_errors);

        //-------------
        set_error_handler("angelrove\utils\MyErrorHandler::handler");
        set_exception_handler("angelrove\utils\MyErrorHandler::handler_excentions");
    }
    //------------------------------------------------------------------
    public static function handler_excentions($e)
    {
        error_log($e);

        if (self::$display_errors) {
            $msg = $e->getMessage() . ' in ' . $e->getFile() . '(' . $e->getLine() . ')' . "\n" .
            $e->getTraceASString();
            self::print_err($msg, true);
        } else {
            throw $e;
        }
    }
    //------------------------------------------------------------------
    public static function handler($errno, $errstr, $errfile, $errline)
    {
        // Para códigos de error que no están incluidos en 'error_reporting' (ejem.: se utiliza '@')
        if (!(error_reporting() & $errno)) {
            return;
        }
        // self::print_err("$errno,\n $errstr,\n $errfile,\n $errline");

        //--------------
        switch ($errno) {
            case E_USER_NOTICE:
            case E_USER_ERROR:
                self::write_log('user', $errstr, $errfile, $errline);
                break;
            case E_NOTICE:
                self::write_log('notice', $errstr, $errfile, $errline);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                self::write_log('warning', $errstr, $errfile, $errline);
                break;

            default:
                return false; // Ejecutar el gestor de errores interno de PHP
                break;
        }

        // Ejecuta siempre el gestor de errores interno de PHP (según 'display_errors') ------
        //  - necesario para que se muestren los errores en pantalla
        //  - escribirá los errores en la ruta por defecto definida en 'error_log'
        if (self::$display_errors) {
            self::print_err(self::debug_string_backtrace());
            return false;
        } else {
        }
    }
    //------------------------------------------------------------------
    /*
     * @author php.net/manual/es/function.debug-print-backtrace.php#86932
     */
    private static function debug_string_backtrace()
    {
        ob_start();
        debug_print_backtrace(0, 8);
        $trace = ob_get_contents();
        ob_end_clean();

        // Remove first item from backtrace as it's this function which is redundant.
        $trace = preg_replace('/^.+\n/', '', $trace);
        $trace = preg_replace('/^.+\n/', '', $trace);
        // $trace = preg_replace('/^.+\n/', '', $trace);

        // Renumber backtrace items.
        // $trace = preg_replace('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);
        $trace = preg_replace('/^#(\d+)/m', '#', $trace);

        return $trace;
    }
    //------------------------------------------------------------------
    private static function write_log($id_file, $errstr, $errfile, $errline)
    {
        //-------
        $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        $paramUrl    = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        // $param_agent = (isset($_SERVER['HTTP_USER_AGENT']))? '['.$_SERVER['HTTP_USER_AGENT'].']' : '[no USER_AGENT]';

        $msg = "[" . date('d-M h:i:s') . "] [$REMOTE_ADDR] [$paramUrl] $errstr in file $errfile line $errline" . PHP_EOL;

        //-------
        $file_name = self::$PATH_LOGS . '/';
        // $file_name .= 'handler-'.self::$file_pref.$id_file.'-'.date('d').'.log';
        $file_name .= self::$file_pref . $id_file . '.log';

        //-------
        file_put_contents($file_name, $msg, FILE_APPEND);
    }
    //------------------------------------------------------------------
    private static function print_err($object, $isException = false)
    {
        $style = ($isException) ? 'background:wheat;' : 'background:#eee;';

        echo '</select><pre style="max-width:1100px;max-height:400px;' . $style . ' font-size:13px;padding:8px;border:1px solid #de9595;text-align:left">' .
        "<b>ErrorHandler - debug backtrace</b>\n---------------------------------\n" .
        print_r($object, true) .
            '</pre>';
    }
    //------------------------------------------------------------------
}
