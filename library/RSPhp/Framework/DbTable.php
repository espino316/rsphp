<?php

namespace RSPhp\Framework;

use Exception;

class DbTable
{
    private $tableName;
    private $columns;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        $this->columns = array();
    } // end function construct

    public function column($columnName)
    {
        $column = new DbColumn($columnName);
        $this->columns[] = $column;
        return $this->columns[count($this->columns)-1];
    } // end function column

    public function go()
    {
        return print_r($this, true);
    } // end function

} // end class DbTable
