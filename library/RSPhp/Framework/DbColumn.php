<?php declare(strict_types=1);

namespace RSPhp\Framework;

use Exception;
use stdClass;

class DbColumn
{
    public $name;
    public $isAutoIncrement;
    public $dataType;
    public $isNullable;
    public $characterLength;
    public $default;
    public $isPrimaryKey;
    public $isForeignKey;
    public $isUnique;
    public $isCheck;
    public $isIndexed;
    private $table;

    public function __construct($table, $columnName)
    {
        $this->table = $table;

        if (is_array($columnName) || is_object($columnName)) {
            return $this->init($columnName);
        } // end if array or object

        $this->name = $columnName;
        $this->isAutoIncrement = false;
        return $this;
    } // end function construct

    /**
     * Initialize the column with a definition, either array or stdClass
     *
     * @param mixed $columnDefinition   Is either an array or an object with properties
     *                                  that define the column
     * 
     * @return DbColumn
     */
    public function init ($columnDefinition)
    {
        $col = (object)$columnDefinition;
        $this->name = $col->column_name;
        $dataType = Str::toCamelCase(" ", $col->data_type);
        $this->$dataType($col->character_maximum_length);
        $this->characterLength = $col->character_maximum_length;
        $this->isNullable = ($col->is_nullable == 'YES');
    } // end function init

    /**
     * Adds a unique constraint to the parent table
     */
    public function unique()
    {
        $this->isUnique = true;
        $this->table->unique(
            $this->name
        ); // end table constraint
        return $this;
    } // end function unique

    /**
     * Adds a primary constraint to the parent table
     */
    public function primaryKey()
    {
        $this->isPrimaryKey = true;
        $this->table->primaryKey(
            $this->name
        ); // end table constraint
        return $this;
    } // end function primary key

    /**
     * Adds a index to the parent table
     */
    public function index()
    {
        $this->isIndex = true;
        $this->table->index(
            $this->name
        ); // end table constraint
        return $this;
    } // end function index
    
    public function null()
    {
        $this->isNullable = true;
        return $this;
    } // end function null

    /**
     * Returns the parent table
     */
    public function table()
    {
        return $this->table;
    } // end function table

    public function autoIncrement()
    {
        $this->dataType = "int";
        $this->isAutoIncrement = true;
        return $this;
    } // end function autoIncrement

    public function characterVarying($len = null)
    {
        return $this->string($len);
    } // end function characterVarying

    public function string($len = null)
    {
        $this->dataType = "varchar";
        if ($len) {
            $this->characterLength = $len;
        } // end if len
        return $this;
    } // end function string

    public function timestampWithoutTimeZone()
    {
        return $this->timestamp();
    } // end function timestampWithoutTimeZone

    public function timestamp()
    {
        $this->dataType = "timestamp";
        return $this;
    } // end function timestamp

    public function integer()
    {
        return $this->int();
    } // end function integer

    public function int()
    {
        $this->dataType = "int";
        return $this;
    } // end function integer

    public function bool()
    {
        $this->dataType = "bool";
        return $this;
    } // end function boolean

    public function boolean()
    {
        return $this->bool();
    } // end function boolean

    public function auto_increment()
    {
        return $this->AutoIncrement();
    } // end function auto_increment

    public function datetime()
    {
        return $this->timestamp();
    } // end function datetime

    public function identity()
    {
        $this->dataType = "int";
        $this->isAutoIncrement = true;
        return $this;
    } // end function

    public function bigint()
    {
        $this->dataType = "bigint";
        return $this;
    } // end function bigint

    public function bigserial()
    {
        $this->dataType = "bigserial";
        $this->isAutoIncrement = true;
        return $this;
    } // end function

    public function serial()
    {
        $this->dataType = "serial";
        $this->isAutoIncrement = true;
        return $this;
    } // end function

    public function point()
    {
        $this->dataType = "point";
        return $this;
    } // end function point

    public function uuid()
    {
        $this->dataType = "UUID";
        return $this;
    } // end function uuid

    public function date()
    {
        return $this->timestamp();
    } // end function date

    public function numeric()
    {
        return $this->decimal();
    } // end function numeric

    public function number()
    {
        return $this->decimal();
    } // end function number

    public function decimal()
    {
        $this->dataType = "decimal";
        return $this;
    } // end function decimal

    public function money()
    {
        $this->dataType = "decimal";
        return $this;
    } // end function money

    public function getFkDataType()
    {
        if ($this->dataType == "bigserial") {
            return "bigint";
        } // end if data type is bigserial
        
        if ($this->dataType == "serial") {
            return "int";
        } // end if data type is bigserial

        return $this->dataType;
    } // end function getFkDataType
    
    public function bit()
    {
        $this->dataType = "boolean";
    } // end function bit
} // end class DbColumn
