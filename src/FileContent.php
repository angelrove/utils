<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

namespace angelrove\utils;

class FileContent
{
    //------------------------------------------------------------------
    public static function include_return($file, $params = array())
    {
        try {
            ob_start();

            if ((include ($file)) === false) {
                throw new \Exception("File not found [$file]");
            }
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }

        return ob_get_clean();
    }
    //------------------------------------------------------------------
    public static function get_contents($file)
    {
        try {
            if (($ret = @file_get_contents($file)) === false) {
                throw new \Exception("Failed to open file [$file]");
            }

            return $ret;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    //------------------------------------------------------------------
    public static function put_contents($file, $data, $flags = 0)
    {
        try {
            if (($ret = @file_put_contents($file, $data, $flags)) === false) {
                throw new \Exception("Failed to open file [$file]");
            }

            return $ret;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    //------------------------------------------------------------------
}
