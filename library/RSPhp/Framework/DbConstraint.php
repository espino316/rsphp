<?php

namespace RSPhp\Framework;

use Exception;

class DbConstraint
{
    public $name;
    public $constraintType;
    private $table;

    public function __construct($table, $name, $constraintType)
    {
        $this->table = $table;
        $this->name = $name;
        $this->constraintType = $constraintType;
    } // end function construct

    /**
     * Returns the parent table
     */
    public function table()
    {
        return $this->table;
    } // end function table

} // end class DbColumn
