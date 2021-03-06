<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils\Images;

use angelrove\utils\FileSystem;
use angelrove\utils\CssJsLoad;
// use angelrove\membrillo\DebugTrace;

class ImageTransform
{
    //---------------------------------------------------------------------
    /**
     * <img src="scale.php?image='.$file."/>
     */
    public static function resizeOnFly($img_dir, $img_name, $width)
    {
        self::resize($img_dir, $img_name, $width, '', '', true);
    }
    //---------------------------------------------------------------------
    /**
     * Redimensionar todas las imágenes de un directorio (no recursivo)
     */
    public static function resizeOnFolder($folder, $thumb_altura, $th_prefijo = '')
    {
        $folder .= '/';
        $listFiles = FileSystem::getFiles($folder, false);
        //print_r2($listFiles);

        $strResult = '';
        foreach ($listFiles as $file) {
            $strResult .= "\n -> $file[name] \n";
            self::resize($file['ruta'], $file['name'], $thumb_altura, $th_prefijo);
        }

        return $strResult;
    }
    //---------------------------------------------------------------------
    public static function resize($img_dir, $img_name, $th_width, $th_height='', $th_prefijo='', $toScreen=false)
    {
        // $trazas_obj = 'ImageTransform.php';
        $ruta = $img_dir.'/'.$img_name;

        // Image data ----
        $img = self::getDatosImg($img_dir, $img_name);
        // DebugTrace::out($trazas_obj.': $img', $img);

        // Path ----
        $thName  = $th_prefijo . $img['name'];
        $th_ruta = $img['dir'] . '/' . $thName;

        // Calculate resized ratio ----
        if ($th_height) {
            // width
            $ratio    = ($img['height'] / $th_height);
            $th_width = round($img['width'] / $ratio);
        } else {
            // height
            $ratio     = ($img['width'] / $th_width);
            $th_height = round($img['height'] / $ratio);
        }

        // Create image ----
        // print_r2("$th_width, $th_height");
        $output = imagecreatetruecolor($th_width, $th_height);

        // white background
        // imagefill($output, 0, 0, imagecolorallocate($output, 255, 255, 255));

        imagecopyresampled($output,
                           self::imageCreateFrom($ruta, $img['mime']),
                           0, 0, 0, 0,
                           $th_width, $th_height,
                           $img['width'], $img['height']);

        // Output to screen ----
        if ($toScreen) {
            header('Content-Type: '.$img['mime']);
            header('Content-Disposition: filename=' . $img_name);

            $seconds_to_cache = (3600 * 24) * 365; // 1 year
            header("Pragma: cache");
            header("Expires: ".gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT");
            header("Cache-Control: max-age=$seconds_to_cache");

            $th_ruta = NULL;
        }

        // Save/Print image ----
        switch ($img['type']) {
            case 'JPEG':
                imagejpeg($output, $th_ruta);
                break;
            case 'GIF':
                imagegif($output, $th_ruta);
                break;
            case 'PNG':
                imagepng($output, $th_ruta);
                break;
            case 'WEBP':
                imagewebp($newImg, $ruta);
                break;
        }

        // $traza = "resize(): th_ruta = '$th_ruta';";
        // DebugTrace::out($trazas_obj, $traza);
    }
    //----------------------------------------------------------------
    public static function imageCropJs($id, $img, $crop)
    {
        CssJsLoad::set(__DIR__.'/ImageTransform.js');

        CssJsLoad::set_script('
         drawImageCrop("canvas_'.$id.'", "'.$img.'", '.$crop[0].', '.$crop[1].', '.$crop[2].', '.$crop[3].');
       ');

       return '<canvas class="imageCrop" id="canvas_'.$id.'" width="'.$crop[2].'" height="'.$crop[3].'">';
    }
    //----------------------------------------------------------------
    /*
     * Recortar una imagen
     *  $outputBuffer: volcar en pantalla o modificar el propio archivo
     *  $cropX: right,  left, center
     *  $cropY: bottom, top,  center
     *  Ejem.: cropImage('./app/cropImage', 'prueba.jpg', 0, 400, true, 'center', 'center');
     */
    public static function crop($img_dir,
                                $img_name,
                                $width,
                                $height,
                                $outputBuffer = true,
                                $cropX = 'right',
                                $cropY = 'bottom')
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
        imagecopyresampled(
            $newImg,
            self::imageCreateFrom($ruta, $img['mime']),
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
            case 'WEBP':
                imagecreatefromwebp($newImg, $ruta);
                break;
        }

        if ($outputBuffer === true) {
            imagedestroy($newImg);
        }

        return true;
    }
    //---------------------------------------------------------------------
    public static function human_filesize($bytes, $decimals = 2) {
       $sz = 'BKMGTP';
       $factor = floor((strlen($bytes) - 1) / 3);

       return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
    //---------------------------------------------------------------------
    public static function getDatosImg($img_dir, $img_name)
    {
        $ruta = $img_dir . '/' . $img_name;

        if (!file_exists($ruta)) {
            throw new \Exception("Error: la ruta del archivo no existe: '$ruta'");
        }

        // Datos image -----
        $datos['dir']  = $img_dir;
        $datos['name'] = $img_name;

        $datosImg = getimagesize($ruta);
        if (!$datosImg) {
            throw new \Exception("Error al obtener datos de la imagen: '$ruta'");
        }
        // print_r2($datosImg);

        if (!$datosImg) {
            echo ("ImageTransform: ERROR: No se puede abrir la imagen [$ruta]<br>");
            return false;
        }

        $datos['width']  = $datosImg[0];
        $datos['height'] = $datosImg[1];
        $datos['type']   = $datosImg[2];
        $datos['mime']   = $datosImg['mime'];

        // $file_size = filesize($ruta);
        // if ($file_size > 1300000) {
        //     print_r2($file_size.': '.self::human_filesize($file_size));
        //     throw new \Exception("Error imagen demasiado grande: '$ruta'");
        // }

        // imagecreate ------
        // print_r2("ruta: ".$ruta);
        // print_r2("size: ".self::human_filesize(filesize($ruta)));
        // print_r2("mime: ".$datos['mime']);

        switch ($datos['mime']) {
            case 'image/gif':
                $datos['type'] = 'GIF';
            break;

            case 'image/jpeg':
                $datos['type'] = 'JPEG';
            break;

            case 'image/png':
                $datos['type'] = 'PNG';
            break;

            case 'image/webp':
                $datos['type'] = 'WEBP';
            break;

            default:
                throw new \Exception(
                    '<b>Unsupported filetype!</b> '.$datos['mime'].' in '.$img_name.'<br>',
                    1);
            break;
        }

        return $datos;
    }
    //---------------------------------------------------------------------
    public static function imageCreateFrom($ruta, $mime)
    {
        switch ($mime) {
            case 'image/gif':
                return imagecreatefromgif($ruta);
            break;

            case 'image/jpeg':
                return imagecreatefromjpeg($ruta);
            break;

            case 'image/png':
                return imagecreatefrompng($ruta);
            break;

            case 'image/webp':
                return imagecreatefromwebp($ruta);
            break;

            default:
                throw new \Exception(
                    '<b>Unsupported filetype!</b> '.$mime.' in '.$ruta.'<br>',
                    1);
            break;
        }
    }
    //---------------------------------------------------------------------
}
