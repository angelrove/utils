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
    public static function debug_sql($flag)
    {
        self::$DEBUG_SQL = $flag;
    }
    //------------------------------------------------------------
    public static function query($query)
    {
        return DB::update($query);
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
    //------
    public static function getRowObject(string $query, $setHtmlSpecialChars = true): ?\stdClass
    {
        $row = DB::select($query);
        $row = $row[0];

        // Por si se va a mostrar en un input y hay algo de esto: &, ", ', <, >
        if ($row && $setHtmlSpecialChars === true) {
            $row = array_map('htmlspecialchars', $row);
        }

        return $row;
    }
    //---------------------------------------------------------
    /* Info */
    //------------------------------------------------------------
    public static function insert_id()
    {
        return DB::getPdo()->lastInsertId();
    }

    public static function count($sqlQuery)
    {
        return count(DB::select($sqlQuery));
    }

    public static function getTableColumns($tableName)
    {
        return DB::schema()->getColumnListing($tableName);
    }
    //------------------------------------------------------------
    /* Obtener un listado */
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
    //----------
    public static function getListObject(string $query, bool $noId = false): ?array
    {
        $listRows = [];

        $rows = DB::select($query);
        foreach ($rows as $row) {
            $listRows[$row->id] = $row;
        }

        return $listRows;
    }
    //----------
    public static function getListNoId(string $query): ?array
    {
        return DB::select($query);
    }
    //------------------------------------------------------------
}
