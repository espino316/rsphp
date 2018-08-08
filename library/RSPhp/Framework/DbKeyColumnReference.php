<?php

namespace RSPhp\Framework;
use Exception;

/**
 * Represents a column reference
 */
class DbKeyColumnReference
{
    public $columnName;
    public $columnReference;

    /**
     * Represents a reference between two columns
     */
    public function __construct(
        $columnName,
        $columnReference
    ) {
        $this->columnName = $columnName;
        $this->columnReference = $columnReference;
    } // end function DbKeyColumnReference
} // end function
