<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;

class FileUpload
{
    private static $UPLOAD_ERRORS = array(
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
    );

    //------------------------------------------------------------------
    /**
     * Params:
     *    fieldName:  campo del $_POST que contiene el archivo
     *    saveAs:     nombre(sin extensión) con el que se guardará el archivo, si es '', el archivo se guarda con el nombre original
     *    ruta:       carpeta donde se copiará el archivo.
     *    validTypes: ejem... 'image/bmp,image/gif,image/pjpeg,image/jpeg,application/pdf,application/msword,...'
     *    maxSize:    tamaño máximo permitido en KB.
     *
     * Return:
     *   - true
     *   - Un array con la descripción y tipo del error ($UP_ERROR['COD']['MSG']).
     *        COD: InputIsEmptyError, FileTypeError, FileSizeError, CopyFileError
     */
    public static function getFile($fieldName, $saveAs, $ruta, $validTypes = '', $maxSize = 0)
    {
        $UP_ERROR = array();
        $f_params = $_FILES[$fieldName];
        //print_r2($f_params);

        /** Obtener archivo **/
        // ¿Es un form "multipart/form-data"?
        if (!isset($_FILES)) {
            $msg = 'El "form" no es [multipart/form-data]';
            user_error('FileUpload->getFile(): ' . $msg, E_USER_WARNING);
            return false;
        }

        // ¿Existe el [input type="file"]?
        if (!$f_params) {
            $msg = 'no existe el [input type="file" name="' . $fieldName . '">]';
            user_error('FileUpload->getFile(): ' . $msg, E_USER_WARNING);
            return false;
        }

        // No hay archivo a subir
        if (!$f_params['name']) {
            $UP_ERROR['COD'] = 'InputIsEmptyError';
            $UP_ERROR['MSG'] = 'no existe un valor para el archivo';
            return $UP_ERROR;
        }

        // Error del servidor
        if ($f_params['error']) {
            $msg = 'UPLOAD ERROR: ' . self::$UPLOAD_ERRORS[$f_params['error']];
            user_error('FileUpload->getFile(): ' . $msg, E_USER_WARNING);
            return false;
        }

        // Get file name
        $fileName = $f_params['name'];

        /** Comprobaciones **/
        // Tipo
        //echo "DEBUG - validTypes: $validTypes <br> f_params['type']: ".$f_params['type'];
        if ($validTypes && !strstr($validTypes, $f_params['type'])) {
            $UP_ERROR['COD'] = 'FileTypeError';
            $UP_ERROR['MSG'] = 'Formato de archivo no valido [' . $f_params['type'] . ']';
            return $UP_ERROR;
        }

        // Tamaño máximo
        if ($maxSize && ($f_params['size'] / 1024) > $maxSize) {
            $UP_ERROR['COD'] = 'FileSizeError';
            $UP_ERROR['MSG'] = 'Ha sobrepasado el tamaño maximo: [' . $fileName . ']';
            return $UP_ERROR;
        }

        /** $saveAs: new $fileName **/
        if ($saveAs) {
            $fileExtension = substr(strrchr($fileName, '.'), 1);
            $fileName      = $saveAs . '.' . $fileExtension;
        }

        /** Upload **/
        if (!move_uploaded_file($f_params['tmp_name'], $ruta . '/' . $fileName)) {
            if (!copy($f_params['tmp_name'], $ruta . '/' . $fileName)) {
                // por si utilizo algo como extracción por url
                $UP_ERROR['COD'] = 'CopyFileError';
                $UP_ERROR['MSG'] = 'Error to copy file: [' . $f_params['tmp_name'] . ' >> ' . $fileName . ']';
                return $UP_ERROR;
            }
        }

        return true;
    }
    //------------------------------------------------------------------
}
