<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils\Images;

use angelrove\utils\FileSystem;

class ImageTransform
{
    //---------------------------------------------------------------------
    /**
     * Redimensionar todas las imagenes JPEG de un directorio (no recursivo)
     */
    public static function thumbsJpegDir($ruta, $thumb_altura, $thumb_prefijo = '')
    {
        $ruta .= '/';
        $listFiles = FileSystem::getFiles($ruta, false);
        //print_r2($listFiles);
        $strResult = '';
        foreach ($listFiles as $file) {
            $strResult .= "\n -> $file[name] \n";
            self::thumbsJpeg($file['ruta'], $file['name'], $thumb_altura, $thumb_prefijo);
        }

        return $strResult;
    }
    //---------------------------------------------------------------------
    /**
     * Redimensionar una imagen
     */
    public static function thumbsJpeg($img_dir, $img_name, $thumb_anchura, $thumb_altura = '', $thumb_prefijo = '')
    {
        $trazas_obj = 'ImageTransform.inc';

        if (!$img_name) {
            return;
        }
        // no hace nada

        if (!$thumb_prefijo && $thumb_prefijo != 'NADA') {
            $thumb_prefijo = 'th_';
        } else {
            $thumb_prefijo = '';
        }

        /** Datos imagen **/
        $img = self::getDatosImg($img_dir, $img_name);
        //DebugTrace::out($trazas_obj.': $img', $img);

        // Siempre debería crear el Thumbnail, ya que sino luego no se encuentra el 'th_'
        /*
        if($thumb_altura) {
        if($img['height'] <= $thumb_altura) return; // no hace nada
        }
        else {
        if($img['width'] <= $thumb_anchura) return; // no hace nada
        }
         */

        /** Thumbnail **/
        // Ruta
        $thName     = $thumb_prefijo . $img['nombre'];
        $thumb_ruta = $img['dir'] . '/' . $thName;

        if ($thumb_altura) {
            // Anchura
            $ratio         = ($img['height'] / $thumb_altura);
            $thumb_anchura = round($img['width'] / $ratio);
        } else {
            // Altura
            $ratio        = ($img['width'] / $thumb_anchura);
            $thumb_altura = round($img['height'] / $ratio);
        }

        // Imagen ----
        $thumb = imagecreatetruecolor($thumb_anchura, $thumb_altura);

        // background
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);

        //---
        imagecopyresampled($thumb, $img['image'],
            0, 0,
            0, 0,
            $thumb_anchura, $thumb_altura,
            $img['width'], $img['height']);

        // Guardar
        if (file_exists($thumb_ruta)) {
            unlink($thumb_ruta);
        }

        switch ($img['type']) {
            case 'JPEG':
                imagejpeg($thumb, $thumb_ruta);
                break;
            case 'GIF':
                imagegif($thumb, $thumb_ruta);
                break;
            case 'PNG':
                imagepng($thumb, $thumb_ruta);
                break;
        }

        $traza = "ImageTransform::thumbsJpeg: imagejpeg: thumb_ruta = '$thumb_ruta';";
        //DebugTrace::out($trazas_obj, $traza);

        return true;
    }
    //----------------------------------------------------------------
    /*
     * Recortar una imagen
     *  $outputBuffer: volcar en pantalla o modificar el propio archivo
     *  $cropX: right,  left, center
     *  $cropY: bottom, top,  center
     *  Ejem.: cropImage('./app/cropImage', 'prueba.jpg', 0, 400, true, 'center', 'center');
     */
    public static function crop($img_dir, $img_name, $width, $height, $outputBuffer = true, $cropX = 'right', $cropY = 'bottom')
    {
        /** Datos **/
        $ruta = $img_dir . '/' . $img_name;
        $img  = self::getDatosImg($img_dir, $img_name);

        if ($width == 0) {
            $width = $img['width'];
        }

        if ($height == 0) {
            $height = $img['height'];
        }

        /** cropX, cropY **/
        $x = 0;
        $y = 0;
        switch ($cropX) {
            case 'left':
                $x = $img['width'] - $width;
                break;
            case 'center':
                $x = ($img['width'] - $width) / 2;
                break;
        }
        //--------------
        switch ($cropY) {
            case 'top':
                $y = $img['height'] - $height;
                break;
            case 'center':
                $y = ($img['height'] - $height) / 2;
                break;
        }
        //--------------
        if (is_numeric($cropX)) {
            $x = $cropX;
        }
        if (is_numeric($cropY)) {
            $y = $cropY;
        }

        /** Crop **/
        $newImg = imagecreatetruecolor($width, $height);

        // background
        $white = imagecolorallocate($newImg, 255, 255, 255);
        imagefill($newImg, 0, 0, $white);

        //---
        $src_x = $x; // punto de origen x
        $src_y = $y; // punto de origen y

        $dst_w = $width; // Ancho del destino
        $dst_h = $height; // Alto del destino

        $src_w = $width; // Ancho original
        $src_h = $height; // Alto original
        imagecopyresampled($newImg, $img['image'],
            0, 0,
            $src_x, $src_y,
            $dst_w, $dst_h,
            $src_w, $src_h);

        /** OUT **/
        if ($outputBuffer === true) {
            $ruta = null;
            header('Content-type: image/' . $img['type']);
        }

        switch ($img['type']) {
            case 'JPEG':
                imagejpeg($newImg, $ruta);
                break;
            case 'GIF':
                imagegif($newImg, $ruta);
                break;
            case 'PNG':
                imagepng($newImg, $ruta);
                break;
        }

        if ($outputBuffer === true) {
            imagedestroy($newImg);
        }

        return true;
    }
    //---------------------------------------------------------------------
    public static function getDatosImg($img_dir, $img_name)
    {
        $ruta = $img_dir . '/' . $img_name;

        $datos['dir']    = $img_dir;
        $datos['nombre'] = $img_name;

        $datosImg = getimagesize($ruta);
        if (!$datosImg) {
            echo ("ImageTransform: ERROR: No se puede abrir la imagen [$ruta]<br>");
            return false;
        }
        $datos['width']  = $datosImg[0];
        $datos['height'] = $datosImg[1];
        $datos['type']   = $datosImg[2];

        //$datos['image'] = @imagecreatefromjpeg($ruta) or die("No se puede abrir la imagen JPEG [$ruta]");
        switch ($datos['type']) {
            case 1:
                $datos['image'] = imagecreatefromgif($ruta);
                $datos['type']  = 'GIF';
                break;
            case 2:
                $datos['image'] = imagecreatefromjpeg($ruta);
                $datos['type']  = 'JPEG';
                break;
            case 3:
                $datos['image'] = imagecreatefrompng($ruta);
                $datos['type']  = 'PNG';
                break;
            default:
                trigger_error('Unsupported filetype!: ' . $img_name . ' [' . $datos['type'] . ']', E_USER_WARNING);
                break;
        }

        //print_r2($datos);
        return $datos;
    }
    //---------------------------------------------------------------------
}
