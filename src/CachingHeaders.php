<?php
/**
 * CachingHeaders
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;

class CachingHeaders
{
    //------------------------------------------------------------------
    /**
     * Llamar antes de cargar cualquier vista para así minimizar el riesgo de
     * cachear errores(ya que al volcarse un warning no se enviarían los headers)
     */
    public static function set($min, $hours = 0, $days = 0)
    {
        if ($days) {
            $days = 60 * 60 * 24 * $days;
        }

        if ($hours) {
            $hours = 60 * 60 * $hours;
        }

        if ($min) {
            $min = 60 * $min;
        }

        // fecha
        $expires = $min + $hours + $days;

        // headers
        header("Cache-Control: public");
        header("Pragma: public");
        header("Cache-Control: maxage=" . $expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

        return '<pre style="font-size:20px">
   DEBUG - cache_headers_enable()<hr>
   fecha   = [' . gmdate('d/m/Y H:i:s', time()) . '] GMT
   expires = [' . gmdate('d/m/Y H:i:s', time() + $expires) . '] GMT >> ' . $expires . 'sec.
   ' . gmdate('D, d M Y H:i:s', time() + $expires) . '
   </pre>';
    }
    //------------------------------------------------------------------
    public static function disable()
    {

    }
    //------------------------------------------------------------------
}
