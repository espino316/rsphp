<?php

namespace RSPhp\Framework;

use Exception;
use stdClass;

/**
 * Represents a index in the database
 *
 */
class DbIndex
{
    /**
     * @var DbTable The table that owns the index
     */
    public $table;

    //  The index name
    public $name;

    //  An array of assoc arrays, column and sort type ASC or DESC
    public $columns = [];

    //  Indicates is the index is unique
    public $isUnique = false;

    /**
     * Creates an instance of DbIndex
     */
    public function __construct(
        $table, $name, $isUnique = false, $columns = []
    ) {
        $this->table = $table;
        $this->name = $name;
        $this->isUnique = $isUnique;
        $this->columns = $columns;
    } // end function __construct

    /**
     * Sets the index as unique index
     *
     * @return DbIndex Return reference to itself
     */
    public function unique()
    {
        $this->unique = true;
        return $this;
    } // end function unique

    /**
     * Adds a column to the index
     *
     * @param string $columnName The name of the column
     * @param DbSortType $sortType The type of sorting, ASC|DESC, default is ASC
     *
     * @return DbIndex Returns a reference to itself
     */
    public function column(
        $columnName,
        $sort = DbSortTypes::Asc
    ) {
        $indexColumn = new stdClass;
        $indexColumn->name = $columnName;
        $indexColumn->sortType = $sort;
        $this->columns[] = $indexColumn;

        return $this;
    } // end function add column

    /**
     * Returns the parent table
     */
    public function table()
    {
        return $this->table;
    } // end function table
    
    /**
     * Returns the actual sql statements for the constraint
     */
    public function getSql()
    {
        $columns = "";
        foreach($this->columns as $column) {
            $comma = $columns ? ", " : "";
            $columns.= $comma."$column->name $column->sortType";
        } // end for each column

        $result = "";
        $result = "CREATE INDEX \n\t$this->name \nON\n\t ".$this->table->tableName." ( \n\t\t$columns \n\t);\n";
        return $result;
    } // end function getSql
} // end class DbIndex
