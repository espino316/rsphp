<?php declare(strict_types=1);

namespace RSPhp\Framework;

use Exception;
use stdClass;

class DbGen implements IDbGen
{
    private $dbConn = null;
    private $db = null;
    private $generator = null;

    public function __construct($dbConnName)
    {
        $this->db = new Db($dbConnName);
        $this->dbConn = Db::getDbConnection($dbConnName);

        if (!$this->dbConn) {
            throw new Exception("This is no valid connection: $dbConnName");
        } // end if not dbConn

        switch ($this->dbConn->driver) {
            case "mysql":
                $this->generator = new MySql\DbGen($dbConnName);
                break;
            case "pgsql":
                $this->generator = new Postgresql\DbGen($dbConnName);
                break;
            default:
                throw new Exception("Driver $driver not supported");
                break;
        } // end switch driver
    } // end function __construct

    /**
     * Returns a string with the column names joined by a comma
     *
     * @param $columns An array of assoc arrays representing a collection of columns
     * @param $identityColumn Optional indicates the name of an identity column to ignore
     *
     * @return String
     */
    public function getColumnNames($columns, $identityColumn = null)
    {
        return $this->generator->getColumnNames($columns, $identityColumn);
    } // end function getColumnNames

    /**
     * Returns a string with the columns names asigned from parameters
     *
     * @param $columns An array of assoc arrays representing a collection of columns
     * @param $columns An array of columns
     *
     * @return String
     */
    public function getUpdateColumns($columns, $primaryKeys)
    {
        return $this->generator->getUpdateColumns($columns, $primaryKeys);
    } // end function getColumnNames

    /**
     * Returns a string with the column names joined by a comma and as parameters "p_{name}"
     *
     * @param $columns An array of assoc arrays representing a collection of columns
     * @param $identityColumn Optional indicates the name of an identity column to ignore
     *
     * @return String
     */
    public function getColumnNamesParameters($columns, $identityColumn = null)
    {
        return $this->generator->getColumnNamesParameters($columns, $identityColumn);
    } // end function getColumnNames

    /**
     * Returns a MySql formed datatype
     *
     * @param $column Assoc array representing a column
     *
     * @return String
     */
    public function getDataType($column)
    {
        return $this->generator->getDataType($column);
    } // end function getColumnNames

    /**
     * Creates a primary key where statement
     *
     * @param $primaryKeys An array of primary key column names
     * @param $columns An array of columns
     *
     * @return String
     */
    public function getPrimaryKeyWhere($primaryKeys)
    {
        return $this->generator->getPrimaryKeyWhere($primaryKeys);
    } // end function getColumnNames

    /**
     * Returns a string with a list of columns as parameters
     *
     * @param $columns A collection of columns
     *
     * @return String
     */
    public function getColumnsParameters($columns, $identityColumn = null)
    {
        return $this->generator->getColumnsParameters($columns, $identityColumn);
    } // end function getColumnNames

    /**
     * Returns a string with a list of primary key parameters
     *
     * @param $primary keys The primary keys list
     * @param $columns A collection of columns
     *
     * @return String
     */
    public function getPrimaryKeyParameters($primaryKeys, $columns)
    {
        return $this->generator->getPrimaryKeyParameters($primaryKeys, $columns);
    } // end function getColumnNames

    /**
     * Returns a string with the requested template
     *
     * @param $templateName The template name
     *
     * @return String
     */
    public function getTemplate($templateName)
    {
        return $this->generator->getTemplate($templateName);
    } // end function getColumnNames

    /**
     * Return a string representation the usp_{table_name}_select_all
     *
     * @param $tableName String the table name
     * @param $columns Array The table columns
     *
     * @return String
     */
    public function selectAll($tableName, $columns)
    {
        return $this->generator->selectAll($tableName, $columns);
    } // end function getColumnNames

    /**
     * Returns a string with the stored procedure to select by key
     *
     * @param $tableName The name of the table
     * @param $columns The collection of collumnts
     * @param $primaryKeys The primary key list
     *
     * @return String
     */
    public function selectByKey($tableName, $columns, $primaryKeys)
    {
        return $this->generator->selectByKey($tableName, $columns, $primaryKeys);
    } // end function getColumnNames

    /**
     * Returns a string with the stored procedure to insert
     *
     * @param $tableName The name of the table to generate a stored procedure
     * @param $columns a list of columns
     * @param $identityColumn A string containing the identity column name if any
     */
    public function insert($tableName, $columns, $identityColumn = null)
    {
        return $this->generator->insert($tableName, $columns, $identityColumn);
    } // end function getColumnNames

    /**
     * Returns a string with the stored procedure to update
     *
     * @param $tableName The name of the table to generate a stored procedure
     * @param $columns a list of columns
     * @param $primaryKeys The list of primary keys
     */
    public function update($tableName, $columns, $primaryKeys)
    {
        return $this->generator->update($tableName, $columns, $primaryKeys);
    } // end function getColumnNames

    /**
     * Returns a string with the stored procedure to delete by key
     *
     * @param $tableName The name of the table
     * @param $columns The collection of collumnts
     * @param $primaryKeys The primary key list
     *
     * @return String
     */
    public function delete($tableName, $columns, $primaryKeys)
    {
        return $this->generator->delete($tableName, $columns, $primaryKeys);
    } // end function getColumnNames

    /**
     * Generate stored procedures in the connected database
     */
    public function generateProcedures()
    {
        return $this->generator->generateProcedures();
    } // end function getColumnNames
} // end class DbGen
