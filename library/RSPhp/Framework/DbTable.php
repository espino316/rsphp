<?php

namespace RSPhp\Framework;

use Exception;
use stdClass;

class DbTable
{
    public $tableName;
    public $columns = [];
    public $connName;
    public $constraints = [];
    public $indexes = [];

    /**
     * Creates an instance of dbtable
     *
     * @param String $connName The name of the database connection
     * @param String $tableName The name of the table
     *
     * @return DbTable
     */
    public function __construct($connName, $tableName = null)
    {
        if ($tableName === null) {
            $tableName = $connName;
            $connName = 'default';
        } // end if tableName null

        $this->connName = $connName;
        $this->tableName = $tableName;
        $this->columns = array();
    } // end function construct

    /**
     * Adds a column to the table
     *
     * @param String $columnName
     *
     * @return DbColumn A reference to the just created column
     */
    public function column($columnName)
    {
        $column = new DbColumn($this, $columnName);
        $this->columns[] = $column;
        return $this->columns[count($this->columns)-1];
    } // end function column

    /**
     * Creates an unique constraint in the table
     *
     * @param mixed columns The unique columns
     */
    public function unique($columns)
    {
        //  Default the constraint
        $unq = null;

        //  Get the args
        $args = func_get_args();
        if (count($args) == 1) {
            if (is_string($columns)) {
                $unqName = "unq_$this->tableName"."_$columns";
                $unq = new DbUniqueConstraint($this, $unqName, array($columns));
            } // end if string

            if (is_object($columns)) {
                throw new Exception("DbUniqueConstraint does not accept objects as parameters");
            } // end if object

            if (is_array($columns)) {
                $unqName = "unq_$this->tableName"."_".implode("_", $columns);
                $unq = new DbUniqueConstraint($this, $unqName, $columns);
            } // end if array
        } else {
            //  Several columns, default ASC
            //  The name:
            $unqName = "unq_".$this->tableName;
            $cols = [];
            foreach($args as $arg) {
                $unqName .= "_".$arg;
                $cols[] = $args;
            } // end foreach

            //  We create the index
            //  Assign the columns
            $unq = new DbUniqueConstraint($this, $unqName, $cols);
        } // end if one arg

        if ($unq) {
            $this->constraints[] = $unq;
            return $this->constraints[count($this->constraints)-1];
        } // end if unq

        //  If we got here, it's an error
        throw new Exception("DbUniqueConstraint cannot be form");
    } // end function index

    /**
     * Creates an index in the table
     *
     * @param mixed    $name       The index's name
     */
    public function index($columns)
    {
        //  Default the index
        $index = null;

        //  Get the args
        $args = func_get_args();
        if (count($args) == 1) {
            if (is_string($columns)) {
                $indexName = "idx_$this->tableName"."_$columns";
                $index = new DbIndex($this, $indexName);
                $index->column($columns);
            } // end if string

            if (is_object($columns)) {
                if ($columns->name && $columns->sortType) {
                    $indexName = "idx_$this->tableName"."_$columns->name";
                    $index = new DbIndex($this, $indexName);
                    $index->column($columns->name, $columns->sortType);
                } // end if correct object
            } // end if object

            if (is_array($columns)) {
                $indexName = "idx_".$this->tableName;
                $cols = [];
                foreach($columns as $col) {
                    $indexName .= "_".$col;
                    $indexColumn = new stdClass;
                    $indexColumn->name = $col;
                    $indexColumn->sortType = DbSortTypes::Asc;
                    $cols[] = $indexColumn;
                } // end foreach

                //  We create the index
                //  Assign the columns
                $index = new DbIndex($this, $indexName, false, $cols);
            } // end if array
        } else {
            //  Several columns, default ASC
            //  The name:
            $indexName = "idx_".$this->tableName;
            $cols = [];
            foreach($args as $arg) {
                $indexName .= "_".$arg;
                $indexColumn = new stdClass;
                $indexColumn->name = $arg;
                $indexColumn->sortType = DbSortTypes::Asc;
                $cols[] = $indexColumn;
            } // end foreach

            //  We create the index
            //  Assign the columns
            $index = new DbIndex($this, $indexName, false, $cols);
        } // end if one arg

        if ($index) {
            $this->indexes[] = $index;
            return $this->indexes[count($this->indexes)-1];
        } // end if index

        //  If we got here, it's an error
        throw new Exception("Index cannot be form");
    } // end function index

    /**
     * Retrives the column from the table
     */
    private function getColumn($columnName)
    {
        $columns = array_filter(
            $this->columns,
            function ($column) use ($columnName) {
                return $column->name == $columnName;
            } // end anonymous array filter function
        ); // end arrayfilter

        return array_shift(
            $columns
        ); // end array_shift
    } // end function getColumn

    /**
     * Creates an index in the table
     *
     * @param mixed    $name       The index's name
     */
    public function primaryKey($columns)
    {
        //  If we got here, there is no previous primary key
        //  Get the args
        $args = func_get_args();

        //  See if there is a primary key
        $pks =
            array_filter(
                $this->constraints,
                function($constraint) {
                    return $constraint->constraintType == DbConstraintTypes::PrimaryKey;
                } // end function array filter anonymous
        ); // end array filter

        $pk = count($pks) ? $pks[0] : null;

        //  If there is a primary key already:
        //      If there is just one column name
        if ($pk && count($args) == 1 && is_string($columns)) {
            $pk->column($columns);
            $this->getColumn($columns)->isPrimaryKey = true;
            return $pk;
        } // end if pk

        //      If there is an object with name and sort type
        if (is_object($columns)) {
            throw new Exception ("DbPrimaryKey needs only column name string, nor object allowed");
        } // end if one arg and string

        //      If there is a series of columns
        if ($pk && count($args) > 1) {

            foreach($args as $arg) {
                $pk->column($arg);
                $this->getColumn($arg)->isPrimaryKey = true;
            } // end foreach

            return $pk;
        } // end if one arg and string

        //  If we got here, there is no previous pk
        //      If there is just one column name
        if (count($args) == 1 && is_string($columns)) {
            $pkName = "pk_$this->tableName";
            $pk = new DbPrimaryKey($this, $pkName);
            $pk->column($columns);
            $this->getColumn($columns)->isPrimaryKey = true;
        } // end if one arg and string

        //  If we pass an array of strings:
        if (count($args) == 1 && is_array($columns)) {
            $pkName = "pk_$this->tableName";
            $pk = new DbPrimaryKey($this, $pkName);
            array_walk(
                $columns,
                function ($column) use ($pk) {
                    $pk->column($column);
                    $this->getColumn($column)->isPrimaryKey = true;
                } // end function
            ); // end array_walk
        } // end if is array

        //      If there is a series of columns
        if (count($args) > 1) {
            $pkName = "pk_$this->tableName";
            $cols = $args;

            //  We create the index
            //  Assign the columns
            $pk = new DbPrimaryKey($this, $pkName, $cols);
            foreach ($cols as $col) {
                $this->getColumn($col)->isPrimaryKey = true;
            } // end for each col
        } // end if one arg and string

        //  If no primary key was formed, then is an error
        if (!$pk) {
            //  If we got here, it's an error
            throw new Exception("Primary key cannot be form");
        } // end if index

        //  Add to the current collection and return it
        $this->constraints[] = $pk;
        return $this->constraints[count($this->constraints)-1];
    } // end function index

    /**
     * Adds a foreign key to the constraints collection
     *
     * @param string $tableReference        The name of the table
     * @param string $columnName            The name of the column in the table
     * @param string $columnReferenceName   The name of the column reference name
     *
     * @return DbForeignKey
     */
    public function foreignKey($tableReference, $columnName, $columnReferenceName)
    {
        $fk = new DbForeignKey($this, $tableReference);
        $fk->columnReference($columnName, $columnReferenceName);

        $fkName = $fk->name;
        $fks = array_filter(
            $this->constraints,
            function ($c) use ($fkName) {
                return $c->constraintType == DbConstraintTypes::ForeignKey && $c->name == $fkName;
            } // end anonymous
        ); // end array_filter

        $count = count($fks);
        if ($count) {
            $previousFk = array_shift($fks);
            $columnMap = array_filter(
                $previousFk->columnMap,
                function ($cM) use ($columnName) {
                    return $cM->columnName == $columnName;
                } // end anonymous function array filter
            ); // end array filter

            if ($columnMap) {
                throw new Exception ("Foreign key $fkName already added to table $this->tableName");
            } // end if column map

            $fk->name .= "_".($count);
        } // end if fks

        $this->getColumn($columnName)->isForeignKey = true;
        $this->constraints[] = $fk;
        return $this->constraints[count($this->constraints)-1];
    } // end function foreignKey

    public function go()
    {
        $db = new Db($this->connName);
        $template = "CREATE TABLE $this->tableName (\n\t@columns\n);";
        $columns = [];

        foreach($this->columns as $column) {

            $colDef = $column->name;

            if ($column->options->autoIncrement) {
                $colDef. " serial not null";
                continue;
            } // end if autoIncrement

            switch ($column->options->dataType) {
                case "int":
                    $colDef.=" int not null";
                break;
                case "string":
                    $len = $column->options->lenght;
                    $colDef.=" varchar($len) not null";
                break;
                case "timestamp":
                    $colDef.=" timestamp not null";
                break;
                case "boolean":
                    $colDef.=" bool not null";
                break;
                case "money":
                    $colDef.=" numeric(18,4) not null";
                break;
                case "numeric":
                    $colDef.=" numeric(18,4) not null";
                break;
                case "decimal":
                    $colDef.=" numeric(18,4) not null";
                break;
            } // end swith data type

            $columns[] = $colDef;
        } // end foreach column

        if (!$columns) {
            throw new Exception("No columns specified");
        } // end if not columns

        $columns = implode(",\n\t", $columns);

        $template = Str::replace("@columns", $columns, $template);
        return $template;
    } // end function

} // end class DbTable
