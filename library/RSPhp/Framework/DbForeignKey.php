<?php

namespace RSPhp\Framework;
use Exception;

/**
 * Represents a foreign key constraint in Db
 */
class DbForeignKey extends DbConstraint implements IDbConstraint
{
    public $tableName;
    public $tableReference;
    public $onUpdate;
    public $onDelete;
    //  Collection of DbKeyColumnReference
    public $columnMap;

    /**
     * Creates an instance of DbForeignKey
     */
    function __construct(
        $table,
        $tableReference,
        $columnMap = [],
        $onUpdate = DbReferenceOptions::NoAction,
        $onDelete = DbReferenceOptions::NoAction
    ) {
        $this->name = "fk_$table->tableName"."_$tableReference";
        $this->constraintType = DbConstraintTypes::ForeignKey;
        $this->tableName = $table->tableName;
        $this->tableReference = $tableReference;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
        $this->columnMap = $columnMap;
    } // end function __construct

    /**
     * Set up the ON DELETE ACTION
     *
     * @param string The action to perform on changes on the referenced table on changes
     *
     * @return DbForeignKey
     */
    function onUpdate ($action)
    {
        $this->onUpdate = $action;
        return $this;
    } // end function onUpdate

    /**
     * Set up the ON DELETE ACTION
     *
     * @param string The action to perform on changes on the referenced table on changes
     *
     * @return DbForeignKey
     */
    function onDelete ($action)
    {
        $this->onDelete = $action;
        return $this;
    } // end function onDelte

    /**
     * Adds a column foreign key reference to the collection
     *
     * @param string $columnName The name of the column in the present table
     * @param string $columnReferenceName The name of the column in the referenced table
     *
     * @return DbForeignKey
     */
    function columnReference($columnName, $columnReferenceName)
    {
        $this->columnMap[] = new DbKeyColumnReference($columnName, $columnReferenceName);
        return $this;
    } // end function columnReference

    public function getSql() 
    {
        $columns = "";
        $refColumns = "";

        foreach ($this->columnMap as $map) {
            $comma = $columns ? "," : "";
            $columns = $comma.$map->columnName;
            $comma = $refColumns ? "," : "";
            $refColumns = $comma.$map->columnReference;
        } // end for each column map

        if (!$columns || !$refColumns) {
            throw new Exception("No columns for foreign key $this->name");
        } // end if columns

        $result = "CONSTRAINT $this->name\n\t\t";
        $result .= "FOREIGN KEY ($columns)\n\t\t\t";
        $result .= "REFERENCES $this->tableReference ($refColumns)\n\t\t\t";
        $result .= "ON UPDATE $this->onUpdate\n\t\t\t";
        $result .= "ON DELETE $this->onDelete";

        return $result;
    } // end function getSql
} // end class DbForeignKey
