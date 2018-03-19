<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;

class FileUploaded
{
    //------------------------------------------------------------------
    // $file = "/www/movie.mpg";
    public static function getMime($file)
    {
        $handle      = finfo_open(FILEINFO_MIME);
        $ret['mime'] = finfo_file($handle, $file); //gives "video/mpeg"

        // type: IMAGE, IMAGE_SWF, FILE
        switch ($ret['mime']) {
            case 'image/gif':
            case 'image/pjpeg':
            case 'image/jpeg':
                $ret['type'] = 'IMAGE';
                break;
            case 'application/x-shockwave-flash':
                $ret['type'] = 'IMAGE_SWF';
                break;
            default:
                $ret['type'] = 'FILE';
                break;
        }

        return $ret;
    }
    //------------------------------------------------------------------
    /**
     * $DEFAULT_DIR: no es la ruta completa, es el directorio de uploads.
     * Nota: quizás debería estar en WObjects
     */
    public static function getInfo($bbdd_file, $DEFAULT_DIR = '')
    {
        global $CONFIG_APP;

        if (!$bbdd_file) {
            return array();
        }

        if (isset($CONFIG_APP)) {
            $URL_UPLOADS  = $CONFIG_APP['url_uploads'];
            $PATH_UPLOADS = $CONFIG_APP['path_uploads'];
        } else {
            $URL_UPLOADS  = '/_uploads';
            $PATH_UPLOADS = '_uploads';
        }

        //---------------
        $datos = array();

        $params_foto   = explode('#', $bbdd_file);
        $datos['data'] = $params_foto;

        /** Params **/
        $datos['name']     = $params_foto[0];
        $datos['nameUser'] = (isset($params_foto[1])) ? $params_foto[1] : '';
        $datos['fecha']    = (isset($params_foto[2])) ? $params_foto[2] : '';
        $datos['size']     = (isset($params_foto[3])) ? $params_foto[3] : '';
        $datos['mime']     = (isset($params_foto[4])) ? $params_foto[4] : '';
        $datos['dir']      = (isset($params_foto[5])) ? $params_foto[5] : '';

        //----
        if ($DEFAULT_DIR) {
            $datos['dir'] = $DEFAULT_DIR;
        }
        if ($datos['dir']) {
            $datos['dir'] .= '/';
        }
        //----

        /** Ruta (URL) **/
        $datos['ruta']             = $URL_UPLOADS . '/' . $datos['dir'];
        $datos['ruta_completa']    = $datos['ruta'] . $datos['name'];
        $datos['ruta_completa_th'] = $datos['ruta'] . 'th_' . $datos['name'];

        /** Ruta (path) **/
        $datos['path']             = $PATH_UPLOADS . '/' . $datos['dir'];
        $datos['path_completo']    = $datos['path'] . $datos['name'];
        $datos['path_completo_th'] = $datos['path'] . 'th_' . $datos['name'];

        return $datos;
    }
    //--------------------------------------------------------------
    public static function getInfo2($bbdd_file)
    {
        global $CONFIG_APP;

        if (!$bbdd_file) {
            return array();
        }

        $file        = array();
        $params_foto = explode('#', $bbdd_file);

        /** Params **/
        $file['name'] = $params_foto[0];
        $file['dir']  = (isset($params_foto[5])) ? $params_foto[5] : '';

        $file['dir'] .= '/';

        /** Ruta (URL) **/
        $file['ruta'] = $file['dir'] . $file['name'];
        $file['ruta_th'] = $file['dir'] . 'th_' . $file['name'];

        $file['ruta_completa'] = $CONFIG_APP['url_uploads'].$file['ruta'];

        return $file;
    }
    //----------------------------------------------------------
    // $type: '', 'lightbox'
    public static function getHtmlImg(array $datos,
                                      $type = '',
                                      $alt = '',
                                      $class = '',
                                      $op_nofoto = false,
                                      $link = '')
    {
        $img = '';

        //---------------
        // no foto
        if (!$datos) {
            if ($op_nofoto) {
                $img = '<i class="far fa-image fa-5x" aria-hidden="true"></i>';
            }
            return $img;
        }
        //---------------

        // alt
        $alt = ($alt) ? htmlentities($alt) : $datos['name'];

        // lightbox
        if ($type == 'lightbox') {
            $img =
                '<a class="htm_img img-thumbnail ' . $class . '"'.
                    'href="' . $datos['ruta_completa'] . '"'.
                    'data-lightbox="file_img">' .
                  '<img class="img-responsive"' .
                       'src="' . $datos['ruta_completa_th'] . '"' .
                       'onerror="this.onerror=null;this.src=\'' . $datos['ruta_completa'] . '\'"' .
                       'alt="' . $alt . '">' .
                '</a>';
        }
        //---------------
        // basic image
        else {
            $img = '<img class="htm_img img-responsive ' . $class . '" src="' . $datos['ruta_completa'] . '" alt="' . $alt . '">';
        }

        // Link ----
        if ($link) {
            $img = '<a href="' . $link . '">' . $img . '</a>';
        }

        return $img;
    }
    //------------------------------------------------------------------
}
