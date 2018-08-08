<?php

namespace RSPhp\Framework;

use Exception;

/**
 * Represents a check constraint in the database
 */
class DbCheckConstraint extends DbConstraint
{
    $condition;

    /**
     * Creates an instance of DbDbCheckConstraint
     *
     * @return DbCheckConstraint
     */
    public function __construct($table, $name, $condition =  null)
    {
        $this->table = $table;
        $this->name = $name;
        $this->constraintType = DbConstraintTypes::Check;
        $this->condiction = $condiction;
    } // end function __construct

    /**
     * Adds the condition of the check constraint
     *
     * @param string $condition The condition of the constraint
     *
     * @return DbConstraint
     *
     */
    public function condition($condiction)
    {
        $this->condition = $condiction;
        return $this;
    } // end function condition
} // end class DbCheckConstraint
