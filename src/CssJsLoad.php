<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;

class CssJsLoad
{
    private static $async = array(
        'js'  => 'async',
        'css' => 'media="none" onload="if(media!=\'all\')media=\'all\'"',
    );

    private static $path_cache = '';
    private static $url_cache  = '';

    //---
    private static $set_minify     = false;
    private static $cache_disabled = false;
    private static $cache_version  = '1';

    //---
    private static $async_files = array();

    private static $list_css        = array();
    private static $list_css_http   = array();
    private static $list_css_blocks = array();

    private static $list_js      = array();
    private static $list_scripts = array();

    private static $list_css_combined = array();
    private static $list_js_combined  = array();

    private static $called_get_css = false;
    private static $called_get_js  = false;

    //---------------------------------------------------------------------
    // CONF
    //---------------------------------------------------------------------
    public static function __init($path_cache, $url_cache, $cache_version)
    {
        self::$path_cache = $path_cache;
        self::$url_cache  = $url_cache;
        self::$cache_version = $cache_version;
    }
    //---------------------------------------------------------------------
    public static function set_minify($minify)
    {
        self::$set_minify = $minify;
    }
    //---------------------------------------------------------------------
    public static function set_cache_disabled($flag)
    {
        self::$cache_disabled = $flag;
    }
    //---------------------------------------------------------------------
    // SETTERS
    //---------------------------------------------------------------------
    public static function check_getjs_calls()
    {
        if (self::$called_get_js == true) {
            // trigger_error("Called 'set js' after 'get()'", E_USER_NOTICE);
            throw new \Exception("Called 'set css' after 'get()'", 1);
        }
    }
    //---------------------------------------------------------------------
    public static function check_getcss_calls()
    {
        if (self::$called_get_css == true) {
            // trigger_error("Called 'set css' after 'get()'", E_USER_NOTICE);
            throw new \Exception("Called 'set css' after 'get()'", 1);
        }
    }
    //---------------------------------------------------------------------
    public static function set($file, $async = false)
    {
        // Not combined ---
        if (self::is_http($file)) {
            if (self::get_type($file) == 'js') {
                self::set_js($file, $async);
            } else {
                self::set_css($file, $async);
            }
        }
        // Combined -------
        else {
            if (!file_exists($file)) {
                trigger_error("File not found: $file", E_USER_NOTICE);
                return;
            }

            if (self::get_type($file) == 'js') {
                self::set_js_combined($file);
            } else {
                self::set_css_combined($file);
            }
        }
    }
    //---------------------------------------------------------------------
    public static function set_js($file, $async = false)
    {
        self::check_getjs_calls();

        self::$async_files[$file] = $async;
        self::$list_js[$file]     = $file;
    }
    //---------------------------------------------------------------------
    public static function set_css($file, $async = false, $combined = false)
    {
        self::check_getcss_calls();

        self::$async_files[$file] = $async;

        if ($combined) {
            self::$list_css_combined[$file] = $file;
        } else {
            if (self::is_http($file)) {
                self::$list_css_http[$file] = $file;
            } else {
                self::$list_css[$file] = $file;
            }
        }
    }
    //---------------------------------------------------------------------
    // Dependiendo de si usa o no clave, sera combined o no
    public static function set_script($script, $key = '')
    {
        self::check_getjs_calls();

        if ($key) {
            self::$list_scripts[$key] = $script;
        } else {
            self::$list_scripts[] = $script;
        }
    }
    //---------------------------------------------------------------------
    public static function set_css_block($css, $key = '')
    {
        self::check_getcss_calls();

        if ($key) {
            self::$list_css_blocks[$key] = $css;
        } else {
            self::$list_css_blocks[] = $css;
        }
    }
    //---------------------------------------------------------------------
    // PRIVATE
    //---------------------------------------------------------------------
    private static function set_js_combined($file)
    {
        self::check_getjs_calls();

        self::$list_js_combined[$file] = $file;
    }
    //---------------------------------------------------------------------
    private static function set_css_combined($file)
    {
        self::check_getcss_calls();

        self::$list_css_combined[$file] = $file;
    }
    //---------------------------------------------------------------------
    // GETTERS
    //---------------------------------------------------------------------
    public static function get_css()
    {
        // called: get() ---
        self::$called_get_css = true;
        //------------------

        self::get_css_js_files(self::$list_css_http, 'css');
        self::get_css_js_combined('css');
        self::get_css_js_files(self::$list_css, 'css');
    }
    //---------------------------------------------------------------------
    public static function get_js()
    {
        // called: get() ---
        self::$called_get_js = true;
        //------------------

        self::get_css_js_files(self::$list_js, 'js');
        self::get_css_js_combined('js');
        self::get_js_scripts();
    }
    //---------------------------------------------------------------------
    // PRIVATE
    //---------------------------------------------------------------------
    private static function get_js_scripts()
    {
        $strScripts = self::read_scripts(self::$list_scripts, false);
        if ($strScripts) {
            echo PHP_EOL .
                '<!-- js_scripts -->' . PHP_EOL .
                '<script>' . PHP_EOL . $strScripts . '</script>' .
                PHP_EOL;
        }
    }
    //---------------------------------------------------------------------
    private static function get_css_js_files(array $listFiles, $ext)
    {
        $strCssJs = '';

        foreach ($listFiles as $file) {
            $strAsync = (self::$async_files[$file]) ? self::$async[$ext] : '';
            // $strAsync = '';

            if ($ext == 'js') {
                $strCssJs .= '<script ' . $strAsync . ' type="text/javascript" src="' . $file . '"></script>' . PHP_EOL;
            } elseif ($ext == 'css') {
                $strCssJs .= '<link type="text/css" ' . $strAsync . ' href="' . $file . '" rel="stylesheet">' . PHP_EOL;
            }
        }

        echo $strCssJs;
    }
    //---------------------------------------------------------------------
    // El nombre se genera con el md5 de la cadena resultante de concatenar todos los nombres de archivos y claves de scripts.
    // De esta forma se regenera automáticamente al añadir o eliminar un archivo o script.
    private static function get_css_js_combined($ext, $obfuscate = false)
    {
        /** List files **/
        $listFiles    = array(); // Files
        $list_scripts = array(); // Scripts

        if ($ext == 'js') {
            $listFiles    = self::$list_js_combined;
            $list_scripts = self::$list_scripts;
        } elseif ($ext == 'css') {
            $listFiles    = self::$list_css_combined;
            $list_scripts = self::$list_css_blocks;
        } else {
            return false;
        }

        /** Cache file name **/
        // listKeys
        $listKeys = implode(';', $listFiles) . implode(';', array_keys($list_scripts));
        if (!$listKeys) {
            return;
        }

        // version + keys + ext
        $cache_fileName = self::$cache_version . '_' . md5($listKeys) . '.' . $ext;

        /** Write cache **/
        $cache_file_path = self::$path_cache . '/' . $cache_fileName;

        // Cache disabled
        if (self::$cache_disabled === true) {
            @unlink($cache_file_path);
        }

        if (!file_exists($cache_file_path)) {
            // Read ---
            $strCombined = self::read_files_combined($listFiles, $ext);

            if ($list_scripts) {
                if ($ext == 'js') {
                    $strCombined .= self::read_scripts($list_scripts, true);
                } else {
                    $strCombined .= self::read_css_blocks($list_scripts);
                }
            }

            // Write ---
            file_put_contents($cache_file_path, $strCombined, FILE_APPEND | LOCK_EX);
        }

        /** OUT **/
        $cache_file_url = self::$url_cache . '/' . $cache_fileName;

        if ($ext == 'js') {
            echo '<script type="text/javascript" src="' . $cache_file_url . '"></script>' . PHP_EOL;
        } elseif ($ext == 'css') {
            echo '<link type="text/css" href="' . $cache_file_url . '" rel="stylesheet">' . PHP_EOL;
        }
    }
    //---------------------------------------------------------------------
    // READ CONTENT
    //---------------------------------------------------------------------
    private static function read_files_combined(array $listFiles, $ext)
    {
        // Header ---------
        $header = '';
        if (self::$set_minify) {
            $header = '/* lib: angelrove\utils\CssJsLoad - url: ' . $_SERVER['REQUEST_URI'] . ' */' . PHP_EOL;
        } else {
            $header = '/**' . PHP_EOL .
            ' * lib: angelrove\utils\CssJsLoad' . PHP_EOL .
            ' *' . PHP_EOL .
            ' * url: ' . $_SERVER['REQUEST_URI'] . PHP_EOL .
            ' * files:' . PHP_EOL .
            ' ' . implode(PHP_EOL . ' ', $listFiles) . PHP_EOL .
                ' */' . PHP_EOL . PHP_EOL;
        }

        // List files -----
        $strCombined = '';
        $c           = 0;
        foreach ($listFiles as $file) {
            // Read file ---
            $strFile = file_get_contents($file);

            // Minify ---
            if (self::$set_minify) {
                if (!strpos($file, '.min.') && !strpos($file, '.packed.')) {
                    $strFile = self::{'minify_' . $ext}($strFile);
                }
            }

            //---
            $parts = pathinfo($file);
            // print_r2($parts);
            $strCombined .= PHP_EOL . '/**-- ' . $parts['basename'] . ' --**/' . PHP_EOL;
            $strCombined .= $strFile . PHP_EOL;
        }
        //-----------------

        return $header . $strCombined;
    }
    //---------------------------------------------------------------------
    private static function read_scripts(array $list_scripts, $combined = false)
    {
        $strScripts = '';

        foreach ($list_scripts as $key => $script) {
            if ($combined == true) {
                if (is_string($key)) {
                    // solo los que tienen clave
                    if (self::$set_minify) {
                        $script = self::minify_js($script);
                    }
                    $strScripts .= '/* ' . $key . ' */ ' . $script . PHP_EOL;
                    // print_r2("Combined >> script key: ".$key);
                }
            } else {
                if (is_numeric($key)) {
                    $strScripts .= "/*  $key  */ \n" . $script . PHP_EOL;
                    // print_r2("script key: ".$key);
                }
            }
        }

        //-------
        if ($strScripts) {
            return '/* -- read_scripts() -- */' . PHP_EOL . $strScripts;
        }
        return '';
    }
    //---------------------------------------------------------------------
    private static function read_css_blocks(array $list_css_blocks)
    {
        $strBlocks = '';
        foreach ($list_css_blocks as $key => $block) {
            if (self::$set_minify) {
                $block = self::minify_css($block);
            }
            $strBlocks .= '/* ' . $key . ' */ ' . $block . PHP_EOL;
        }

        //-----
        if ($strBlocks) {
            return '/* -- read_css_blocks() -- */' . PHP_EOL . $strBlocks;
        } else {
            return '';
        }
    }
    //---------------------------------------------------------------------
    // UTILS
    //---------------------------------------------------------------------
    private static function get_type($file)
    {
        $end_3 = substr($file, -3);

        if ($end_3 == '.js' || strpos($file, 'js?')) {
            return 'js';
        } else {
            return 'css';
        }
    }
    //---------------------------------------------------------------------
    private static function is_http($file)
    {
        if (strpos($file, '//') === false) {
            return false;
        } else {
            return true;
        }
    }
    //---------------------------------------------------------------------
    private static function minify_css($buffer)
    {
        return $buffer;

        /* remove comments */
        // $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

        /* remove tabs, spaces, newlines, etc. */
        // $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

        // return $buffer;
    }
    //---------------------------------------------------------------------
    private static function minify_js($buffer)
    {
        // TODO...

        return $buffer;
    }
    //---------------------------------------------------------------------
}
