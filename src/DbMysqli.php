<?
/**
 * José A. Romero Vegas, 2005
 * jangel.romero@gmail.com
 */

namespace angelrove\utils;

class DbMysqli
{
  //------------------------------------------------------------
  /* Conexión */
  //------------------------------------------------------------
  public static function db_getConn($dbHost, $dbUser, $dbPassword, $database) {
    if(!$dbUser) {
       trigger_error("db_getConn(): faltan datos", E_USER_ERROR);
    }

    // connect ----------
    global $db_dbconn;
    $db_dbconn = mysqli_connect($dbHost, $dbUser, $dbPassword);
    if(mysqli_connect_errno($db_dbconn)) {
       trigger_error("db_getConn(): ".mysqli_connect_error(), E_USER_ERROR);
       exit();
    }
    if(!mysqli_select_db($db_dbconn, $database)) {
       trigger_error("db_getConn(): ".mysqli_error($db_dbconn), E_USER_ERROR);
       exit();
    }

    //---
    @mysqli_query($db_dbconn, "SET NAMES 'utf8'");

    // scape strings ----
    if(!get_magic_quotes_gpc()) {
       foreach($_REQUEST as $key=>$value) {
          $_REQUEST[$key] = mysqli_real_escape_string($db_dbconn, $value);
       }
    }

    return $db_dbconn;
  }
  //------------------------------------------------------------
  /* Consulta */
  //------------------------------------------------------------
  public static function db_query($query) {
    global $db_dbconn, $CONFIG_APP;

    // DEBUG ----
    if($CONFIG_APP['debug']['SQL']) print_r('DEBUG_SQL: '.$query);
    //-----------

    //-----------
    $result = mysqli_query($db_dbconn, $query);
    if(!$result) {
       $strErr = mysqli_error($db_dbconn)." REMOTE_ADDR: $_SERVER[REMOTE_ADDR], REQUEST_URI: $_SERVER[REQUEST_URI]
  Query: $query
  ";
       trigger_error($strErr, E_USER_WARNING); //exit();
    }

    return $result;
  }
  //------------------------------------------------------------
  /* Obtener un valor */
  //------------------------------------------------------------
  public static function db_getValue($query) {
    $result = self::db_query($query);
    if(!$result) return;
    if(!mysqli_num_rows($result)) return;

    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);

    return $row[0];
  }
  //------------------------------------------------------------
  /* Obtener una tupla */
  //------------------------------------------------------------
  public static function db_getRow($query, $setHtmlSpecialChars=true) {
    $result = self::db_query($query);
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    // Por si se va a mostrar en un input y hay algo de esto: &, ", ', <, >
    if($row && $setHtmlSpecialChars === true) {
       $row = array_map('htmlspecialchars', $row);
    }

    return $row;
  }
  //------
  public static function db_getRowObject($query, $setHtmlSpecialChars=true) {
    $rowArr = self::db_getRow($query, $setHtmlSpecialChars);
    if(!$rowArr) return $rowArr;

    foreach($rowArr as $field => $value) {
       $row->{$field} = $value;
    }

    return $row;
  }
  //------------------------------------------------------------
  /* Obtener un listado: array de arrays o array de objetos */
  //------------------------------------------------------------
  public static function db_getList($query) {
    $listRows = array();

    $result = self::db_query($query);
    if(!$result) {
       return false;
    }

    while($row = mysqli_fetch_assoc($result)) {
       $listRows[$row['id']] = $row;
    }
    mysqli_free_result($result);

    return $listRows;
  }
  public static function db_getListOneField($query, $noId=false) {
    $listRows = array();
    $result = self::db_query($query);
    if(!$result) {
       return false;
    }

    if($noId) {
       while($row = mysqli_fetch_array($result)) {
          $listRows[] = $row['nombre'];
       }
    }
    else {
       while($row = mysqli_fetch_array($result)) {
          $listRows[$row['id']] = $row['nombre'];
       }
    }

    mysqli_free_result($result);
    return $listRows;
  }
  //------
  public static function db_getListNoId($query) {
    $listRows = array();

    $result = self::db_query($query);
    if($result === true) {
       return $listRows;
    }

    while($row = mysqli_fetch_assoc($result)) {
       $listRows[] = $row;
    }
    mysqli_free_result($result);

    return $listRows;
  }
  //----------
  public static function db_getListObject($query) {
    $listRows = array();

    $result = self::db_query($query);
    while($row = mysqli_fetch_object($result)) {
       $listRows[$row->id] = $row;
    }
    mysqli_free_result($result);

    return $listRows;
  }
  //------
  /*
  public static function db_getListRango($query, $inicio, $numRows, $asObject=true) {
    $listRows = array();

    // Query
    $result = self::db_query($query);

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
  public static function db_getListObjectNoId($query) {
    $listRows = array();

    $result = self::db_query($query);
    while($row = mysqli_fetch_object($result)) {
       $listRows[] = $row;
    }
    mysqli_free_result($result);

    return $listRows;
  }
  //------------------------------------------------------------
  /* Info */
  //------------------------------------------------------------
  public static function db_insert_id() {
    global $db_dbconn;
    return mysqli_insert_id($db_dbconn);
  }
  //--------------
  public static function db_affected_rows() {
    global $db_dbconn;
    return mysqli_affected_rows($db_dbconn);
  }
  //--------------
  public static function db_getNumRows($sqlQuery) {
    // Eliminar saltos de linea
    $sqlQuery2 = str_replace("\r", '',    $sqlQuery);
    $sqlQuery2 = str_replace("\n", '[#]', $sqlQuery2);

    //---------
    // Eliminar "ORDER"
    $patron = '/ORDER BY (.)+/';
    $reemplazo = '';
    $sqlQuery2 = preg_replace($patron, $reemplazo, $sqlQuery2);

    // Eliminar "LIMIT"

    // Reemplazar los campos del "SELECT", por únicamente "id"
    $patron = '/SELECT ([^,]*id).+ FROM/';
    $reemplazo = 'SELECT $1 FROM';
    $sqlQuery2 = preg_replace($patron, $reemplazo, $sqlQuery2);
    //---------

    // Añadir retornos de carro (solo por claridad)
    $sqlQuery2 = str_replace('[#]', "\n", $sqlQuery2);

    // Añadir COUNT()
    $sqlQuery2 = "SELECT COUNT(id) AS numRows FROM ($sqlQuery2) table_numRows";

    // Query
    //print_r($sqlQuery); echo "//-----------"; print_r($sqlQuery2);
    $numRows = self::db_getValue($sqlQuery2);

    return $numRows;
  }
  //------------------------------------------------------------
}