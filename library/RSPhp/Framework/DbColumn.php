<?php

namespace RSPhp\Framework;

use Exception;
use stdClass;

class DbColumn
{
    private $columnName;
    private $options;

    public function __construct($columnName)
    {
        $this->columnName = $columnName;
        $this->options = new stdClass();
        return $this;
    } // end function construct

    public function autoIncrement()
    {
        $this->options->autoIncrement = true;
        return $this;
    } // end function autoIncrement

    public function string($len = null)
    {
        $this->options->dataType = "string";
        return $this;
    } // end function string

    public function timestamp()
    {
        $this->options->dataType = "timestamp";
        return $this;
    } // end function timestamp

    public function unique()
    {
        $this->options->index = "unique";
        return $this;
    } // end function unique

    public function int()
    {
        $this->options->dataType = "int";
        return $this;
    } // end function integer

    public function bool()
    {
        $this->options->boolean = "bool";
        return $this;
    } // end function boolean

} // end class DbColumn
