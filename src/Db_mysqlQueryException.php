<?php
/**
 * Db_mysqlQueryException
 */

namespace angelrove\utils;

use \Exception;

class Db_mysqlQueryException extends Exception
{
    protected $query;

    function __construct($message = "", $query = "", $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
    }

    public function getQuery(): string {
        return $this->query;
    }
}
