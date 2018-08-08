<?php

namespace RSPhp\Framework;
use Exception;
use stdClass;

class DbUniqueConstraint extends DbConstraint implements IDbConstraint
{
    //  An array of string enumerating the columns
    public $columns = [];

    /**
     * Creates an instance of DbIndex
     */
    public function __construct(
        $table, $name, $columns = []
    ) {
        $this->table = $table;
        $this->constraintType = DbConstraintTypes::Unique;
        $this->name = $name;
        $this->columns = $columns;
    } // end function __construct

    /**
     * Adds a column to the unique key
     *
     * @param string $columnName The name of the column
     *
     * @return DbIndex Returns a reference to itself
     */
    public function column($columnName) {
        $this->columns[] = $columName;
        return $this;
    } // end function add column

    /**
     * Returns the sql for the constraint
     */
    public function getSql()
    {
        //  If no columns, error:
        if (!count($this->columns)) {
            throw new Exception ("No columns for constraint $this->name");
        } // end if no columns

        //  Get the columns
        $columns = implode(", ", $this->columns);

        //  Get the sql
        $result = "CONSTRAINT $this->name\n\t\t";
        $result .= "UNIQUE ( $columns )";

        //  Return the sql
        return $result;
    } // end function getSql
} // end class DbPrimaryKey
