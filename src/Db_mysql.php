<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */

namespace angelrove\utils;

class Db_mysql
{
    public static $db_dbconn;
    private static $DEBUG_SQL = false;

    //------------------------------------------------------------
    public static function getConn($dbHost, $dbUser, $dbPassword, $database)
    {
        if (!$dbUser) {
            trigger_error("getConn(): faltan datos", E_USER_ERROR);
        }

        // connect ----------
        self::$db_dbconn = mysqli_connect($dbHost, $dbUser, $dbPassword);
        if (mysqli_connect_errno(self::$db_dbconn)) {
            trigger_error("getConn(): " . mysqli_connect_error(), E_USER_ERROR);
            exit();
        }
        if (!mysqli_select_db(self::$db_dbconn, $database)) {
            trigger_error("getConn(): " . mysqli_error(self::$db_dbconn), E_USER_ERROR);
            exit();
        }

        //---
        @mysqli_query(self::$db_dbconn, "SET NAMES 'utf8'");
        mysqli_set_charset(Db_mysql::$db_dbconn, 'utf8mb4');

        // scape strings ----
        if (!get_magic_quotes_gpc()) {
            foreach ($_REQUEST as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key => $var) {
                        if (is_array($var)) {
                            continue;
                        }
                        $_REQUEST[$key] = mysqli_real_escape_string(self::$db_dbconn, $var);
                        $_POST[$key]    = mysqli_real_escape_string(self::$db_dbconn, $var);
                    }
                } else {
                    $_REQUEST[$key] = mysqli_real_escape_string(self::$db_dbconn, $value);
                    if (isset($_POST[$key])) {
                        $_POST[$key] = mysqli_real_escape_string(self::$db_dbconn, $value);
                    } else {
                        $_GET[$key] = mysqli_real_escape_string(self::$db_dbconn, $value);
                    }
                }
            }

            foreach ($_FILES as $key => $datos) {
                $_FILES[$key]['name'] = mysqli_real_escape_string(self::$db_dbconn, $datos['name']);
            }
        }

        return self::$db_dbconn;
    }
    //------------------------------------------------------------
    public static function real_escape_string($str)
    {
        return mysqli_real_escape_string(self::$db_dbconn, $str);
    }
    //------------------------------------------------------------
    public static function debug_sql($flag)
    {
        self::$DEBUG_SQL = $flag;
    }
    //------------------------------------------------------------
    /* Consulta */
    //------------------------------------------------------------
    public static function query($query)
    {
        global $CONFIG_APP;

        // DEBUG ----
        if (self::$DEBUG_SQL === true) {
            print_r2('Db_mysql::query(): ' . $query);
        }
        //-----------

        if (!$query) {
            return false;
        }

        //-----------
        try {
            if (!$result = mysqli_query(self::$db_dbconn, $query)) {
                $strErr = mysqli_error(self::$db_dbconn);
                throw new Db_mysqlQueryException($strErr, $query);
            }
        } catch (mysqli_sql_exception $e) {
            throw $e;
        }

        // if(!$result = mysqli_query(self::$db_dbconn, $query)) {
        //    $strErr = mysqli_error(self::$db_dbconn);
        //    trigger_error($strErr, E_USER_WARNING);
        // }

        return $result;
    }
    //------------------------------------------------------------
    /* Obtener un valor */
    //------------------------------------------------------------
    public static function getValue($query)
    {
        $result = self::query($query);
        if (!$result) {
            return;
        }

        if (!mysqli_num_rows($result)) {
            return;
        }

        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);

        return $row[0];
    }
    //------------------------------------------------------------
    /* Obtener una tupla */
    //------------------------------------------------------------
    public static function getRow($query, $setHtmlSpecialChars = true)
    {
        if (!$query) {
            return false;
        }

        $result = self::query($query);
        $row    = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        // Por si se va a mostrar en un input y hay algo de esto: &, ", ', <, >
        if ($row && $setHtmlSpecialChars === true) {
            $row = array_map('htmlspecialchars', $row);
        }

        return $row;
    }
    //------
    public static function getRowObject($query, $setHtmlSpecialChars = true)
    {
        $rowArr = self::getRow($query, $setHtmlSpecialChars);
        if (!$rowArr) {
            return $rowArr;
        }

        $row = new \stdClass();
        foreach ($rowArr as $field => $value) {
            $row->{$field} = $value;
        }

        return $row;
    }
    //------------------------------------------------------------
    /* Obtener un listado: array de arrays o array de objetos */
    //------------------------------------------------------------
    public static function getList($query)
    {
        $listRows = array();

        // Query
        $result = self::query($query);
        if (!$result) {
            return false;
        }

        // List rows
        while ($row = mysqli_fetch_assoc($result)) {
            $listRows[$row['id']] = $row;
        }
        mysqli_free_result($result);

        return $listRows;
    }
    //----------
    public static function getListObject($query, $noId = false)
    {
        $listRows = array();

        $result = self::query($query);
        if (!$result) {
            return false;
        }

        while ($row = mysqli_fetch_object($result)) {
            if ($noId) {
                $listRows[] = $row;
            } else {
                $listRows[$row->id] = $row;
            }
        }
        mysqli_free_result($result);

        return $listRows;
    }
    //----------
    public static function getListOneField($query, $noId = false)
    {
        $listRows = array();
        $result   = self::query($query);
        if (!$result) {
            return false;
        }

        if ($noId === true) {
            while ($row = mysqli_fetch_array($result)) {
                $listRows[] = $row[1];
            }
        } else {
            while ($row = mysqli_fetch_array($result)) {
                $listRows[$row[0]] = $row[1];
            }
        }

        mysqli_free_result($result);
        return $listRows;
    }
    //------
    public static function getListNoId($query)
    {
        $listRows = array();

        $result = self::query($query);
        if ($result === true) {
            return $listRows;
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $listRows[] = $row;
        }
        mysqli_free_result($result);

        return $listRows;
    }
    //------
    /*
    public static function getListRango($query, $inicio, $numRows, $asObject=true) {
    $listRows = array();

    // Query
    $result = self::query($query);

    // Num total results
    $DB_TOTAL_ROWS = mysqli_num_rows($result);

    if($DB_TOTAL_ROWS == 0) {
    return array('DB_TOTAL_ROWS'=>0,
    'listRows'     =>$listRows);
    }

    // Posicionar
    if(!@mysqli_data_seek($result, $inicio)) {
    $strErr = "mysqli_data_seek($inicio) no válido. REMOTE_ADDR: $_SERVER[REMOTE_ADDR],\tHTTP_REFERER: $_SERVER[HTTP_REFERER], REQUEST_URI: $_SERVER[REQUEST_URI]";
    trigger_error($strErr, E_USER_WARNING);
    return array('DB_TOTAL_ROWS'=>0,
    'listRows'     =>$listRows);
    }

    // List rows
    $c=1;
    if($asObject === true) {
    while($row = mysqli_fetch_object($result)) {
    $listRows[$row->id] = $row;
    if(++$c == $numRows) break;
    }
    }
    else {
    while($row = mysqli_fetch_assoc($result)) {
    $listRows[$row['id']] = $row;
    if(++$c == $numRows) break;
    }
    }

    mysqli_free_result($result);

    return array('DB_TOTAL_ROWS'=>$DB_TOTAL_ROWS,
    'listRows'     =>$listRows);
    }
     */
    //------
    public static function getListObjectNoId($query)
    {
        $listRows = array();

        $result = self::query($query);
        while ($row = mysqli_fetch_object($result)) {
            $listRows[] = $row;
        }
        mysqli_free_result($result);

        return $listRows;
    }
    //---------------------------------------------------------
    public static function duplicate_row($table, $sql_update, $id)
    {
        // Temporary table ---
        Db_mysql::query("CREATE TEMPORARY TABLE tmp SELECT * from $table WHERE id='$id'");
        Db_mysql::query("ALTER TABLE tmp drop id");

        // Update data ---
        if ($sql_update) {
            Db_mysql::query("UPDATE tmp SET $sql_update");
        }

        // Insert ---
        Db_mysql::query("INSERT INTO $table SELECT 0,tmp.* FROM tmp");
        $new_id = Db_mysql::insert_id();
        Db_mysql::query("DROP TEMPORARY TABLE tmp");

        return $new_id;
    }
    //------------------------------------------------------------
    // sqlFiltro: "AND column_name<>'fecha' AND column_name<>'id_dominio'"
    // Ejem.: self::duplicateRow('noticias_modulos', $id, "", 'id_web', $id_web);
    // public static function duplicateRow($table,
    //                                     $id,
    //                                     $sqlFiltro,
    //                                     $replace_field1 = '',
    //                                     $replace_field_value1 = '',
    //                                     $replace_field2 = '',
    //                                     $replace_field_value2 = '')
    // {
    //     $sqlListCampos = "SELECT column_name AS name FROM information_schema.columns WHERE table_name='$table' AND column_name<>'id' $sqlFiltro";
    //     $listCampos    = self::getListOneField($sqlListCampos, true);

    //     $sql_campos = implode(",", $listCampos);

    //     $sql_values = $sql_campos;

    //     $sql_values = str_ireplace($replace_field1, "'$replace_field_value1'", $sql_values);
    //     $sql_values = str_ireplace($replace_field2, "'$replace_field_value2'", $sql_values);

    //     //$prefig_debug = '_copy';
    //     $sql = "INSERT INTO $table" . $prefig_debug . "($sql_campos) \nSELECT $sql_values FROM $table WHERE id='$id'";
    //     //print_r2($sql);

    //     self::query($sql);
    //     return self::insert_id();
    // }
    //------------------------------------------------------------
    /* Info */
    //------------------------------------------------------------
    public static function insert_id()
    {
        return mysqli_insert_id(self::$db_dbconn);
    }
    //--------------
    public static function affected_rows()
    {
        return mysqli_affected_rows(self::$db_dbconn);
    }
    //--------------
    public static function count($sqlQuery)
    {
        return self::getNumRows($sqlQuery);
    }

    public static function getNumRows($sqlQuery)
    {
        // Eliminar saltos de linea
        $sqlQuery2 = str_replace("\r", '', $sqlQuery);
        $sqlQuery2 = str_replace("\n", '[#]', $sqlQuery2);

        //---------
        // Eliminar "ORDER"
        $patron    = '/ORDER BY (.)+/';
        $reemplazo = '';
        $sqlQuery2 = preg_replace($patron, $reemplazo, $sqlQuery2);

        // Eliminar "LIMIT"

        // Reemplazar los campos del "SELECT", por únicamente "id"
        $patron    = '/SELECT ([^,]*id).+ FROM/';
        $reemplazo = 'SELECT $1 FROM';
        $sqlQuery2 = preg_replace($patron, $reemplazo, $sqlQuery2);
        //---------

        // Añadir retornos de carro (solo por claridad)
        $sqlQuery2 = str_replace('[#]', "\n", $sqlQuery2);

        // Añadir COUNT()
        $sqlQuery2 = "SELECT COUNT(id) AS numRows FROM ($sqlQuery2) table_numRows";

        // Query
        //print_r2($sqlQuery); echo "//-----------"; print_r2($sqlQuery2);
        $numRows = self::getValue($sqlQuery2);

        return $numRows;
    }
    //------------------------------------------------------------
}
