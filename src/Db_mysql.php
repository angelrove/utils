<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 *
 */

namespace angelrove\utils;

use Illuminate\Database\Capsule\Manager as DB;

class Db_mysql
{
    private static $DEBUG_SQL = false;

    //------------------------------------------------------------
    public static function debug_sql(bool $flag): void
    {
        self::$DEBUG_SQL = $flag;
    }
    //------------------------------------------------------------
    public static function parseRequest(): void
    {
        // REQUEST ---
        foreach ($_REQUEST as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    if (is_array($value2)) {
                        continue;
                    }

                    $_REQUEST[$key][$key2] = self::parseVarRequest($value2);
                    $_POST   [$key][$key2] = $_REQUEST[$key][$key2];
                }
            } else {
                $_REQUEST[$key] = self::parseVarRequest($value);

                if (isset($_POST[$key])) {
                    $_POST[$key] = $_REQUEST[$key];
                } else {
                    $_GET[$key]  = $_REQUEST[$key];
                }
            }
        }

        // FILES "name" ---
        foreach ($_FILES as $key => $datos) {
            $_FILES[$key]['name'] = self::parseVarRequest($datos['name']);
        }
    }
    //------------------------------------------------------------
    private static function parseVarRequest($value): string
    {
        if (!$value || is_numeric($value)) {
            return $value;
        }
        return addslashes($value);
    }
    //------------------------------------------------------------
    // Selects
    //------------------------------------------------------------
    public static function getList(string $query): ?array
    {
        $listRows = [];

        $rows = DB::select($query);

        foreach ($rows as $row) {
            $listRows[$row->id] = (array)$row;
        }

        return $listRows;
    }
    //------------------------------------------------------------
    public static function getValue(string $query)
    {
        $row = self::getRow($query);
        if (!$row) {
            return null;
        }

        return $row[array_key_first($row)];
    }
    //------------------------------------------------------------
    public static function getRow(string $query, $setHtmlSpecialChars = true): ?array
    {
        $row = DB::select($query);
        if (!$row) {
            return null;
        }

        $row = (array)$row[0];

        // Por si se va a mostrar en un input y hay algo de esto: &, ", ', <, >
        if ($row && $setHtmlSpecialChars === true) {
            $row = array_map('htmlspecialchars', $row);
        }

        return $row;
    }
    //---------------------------------------------------------
    public static function count(string $sqlQuery)
    {
        return count(DB::select($sqlQuery));
    }
    //------------------------------------------------------------
    public static function insert_id()
    {
        return DB::getPdo()->lastInsertId();
    }
    //------------------------------------------------------------
}
