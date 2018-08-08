<?php

namespace RSPhp\Framework;
use Exception;
use stdClass;

class DbPrimaryKey extends DbConstraint implements IDbConstraint
{
    //  An array of assoc arrays, column and sort type ASC or DESC
    public $columns = [];
    /**
     * @var DbTable $table The table parent of the constraint
     */
    private $table;

    /**
     * Creates an instance of DbIndex
     */
    public function __construct(
        $table, $name, $columns = []
    ) {
        $this->table = $table;
        $this->constraintType = DbConstraintTypes::PrimaryKey;
        $this->name = $name;
        $this->columns = $columns;
    } // end function __construct

    /**
     * Adds a column to the primary key
     *
     * @param string $columnName The name of the column
     * @param DbSortType $sortType The type of sorting, ASC|DESC, default is ASC
     *
     * @return DbIndex Returns a reference to itself
     */
    public function column(
        $columnName
    ) {
        $this->columns[] = $columnName;
        return $this;
    } // end function add column

    /**
     * Returns the actual sql statements for the constraint
     */
    public function getSql()
    {
        $columns = implode(", ", $this->columns);

        $result = "";
        $result = "CONSTRAINT $this->name \n\t\tPRIMARY KEY ( \n\t\t\t$columns \n\t\t)";

        return $result;
    } // end function getSql
} // end class DbPrimaryKey
