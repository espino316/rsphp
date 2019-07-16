<?php declare(strict_types=1);

namespace RSPhp\Framework;

/**
 * Shared methods for DbConstraints
 */
interface IDbGen
{
    /**
     * Returns a string with the column names joined by a comma
     *
     * @param $columns An array of assoc arrays representing a collection of columns
     * @param $identityColumn Optional indicates the name of an identity column to ignore
     *
     * @return String
     */
    public function getColumnNames($columns, $identityColumn = null);

    /**
     * Returns a string with the columns names asigned from parameters
     *
     * @param $columns An array of assoc arrays representing a collection of columns
     * @param $columns An array of columns
     *
     * @return String
     */
    public function getUpdateColumns($columns, $primaryKeys);

    /**
     * Returns a string with the column names joined by a comma and as parameters "p_{name}"
     *
     * @param $columns An array of assoc arrays representing a collection of columns
     * @param $identityColumn Optional indicates the name of an identity column to ignore
     *
     * @return String
     */
    public function getColumnNamesParameters($columns, $identityColumn = null);

    /**
     * Returns a MySql formed datatype
     *
     * @param $column Assoc array representing a column
     *
     * @return String
     */
    public function getDataType($column);

    /**
     * Creates a primary key where statement
     *
     * @param $primaryKeys An array of primary key column names
     * @param $columns An array of columns
     *
     * @return String
     */
    public function getPrimaryKeyWhere($primaryKeys);

    /**
     * Returns a string with a list of columns as parameters
     *
     * @param $columns A collection of columns
     *
     * @return String
     */
    public function getColumnsParameters($columns, $identityColumn = null);

    /**
     * Returns a string with a list of primary key parameters
     *
     * @param $primary keys The primary keys list
     * @param $columns A collection of columns
     *
     * @return String
     */
    public function getPrimaryKeyParameters($primaryKeys, $columns);

    /**
     * Returns a string with the requested template
     *
     * @param $templateName The template name
     *
     * @return String
     */
    public function getTemplate($templateName);

    /**
     * Return a string representation the usp_{table_name}_select_all
     *
     * @param $tableName String the table name
     * @param $columns Array The table columns
     *
     * @return String
     */
    public function selectAll($tableName, $columns);

    /**
     * Returns a string with the stored procedure to select by key
     *
     * @param $tableName The name of the table
     * @param $columns The collection of collumnts
     * @param $primaryKeys The primary key list
     *
     * @return String
     */
    public function selectByKey($tableName, $columns, $primaryKeys);

    /**
     * Returns a string with the stored procedure to insert
     *
     * @param $tableName The name of the table to generate a stored procedure
     * @param $columns a list of columns
     * @param $identityColumn A string containing the identity column name if any
     */
    public function insert($tableName, $columns, $identityColumn = null);

    /**
     * Returns a string with the stored procedure to update
     *
     * @param $tableName The name of the table to generate a stored procedure
     * @param $columns a list of columns
     * @param $primaryKeys The list of primary keys
     */
    public function update($tableName, $columns, $primaryKeys);

    /**
     * Returns a string with the stored procedure to delete by key
     *
     * @param $tableName The name of the table
     * @param $columns The collection of collumnts
     * @param $primaryKeys The primary key list
     *
     * @return String
     */
    public function delete($tableName, $columns, $primaryKeys);

    /**
     * Generate stored procedures in the connected database
     */
    public function generateProcedures();
} // end class DbGen
