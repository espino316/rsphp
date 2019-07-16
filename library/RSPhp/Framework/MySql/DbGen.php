<?php declare(strict_types=1);

namespace RSPhp\Framework\MySql;

use Exception;
use stdClass;

class DbGen implements \RSPhp\Framework\IDbGen
{
    private $dbConn = null;
    private $db = null;

    public function __construct($dbConnName)
    {
        $this->db = new \RSPhp\Framework\Db($dbConnName);
        $this->dbConn = \RSPhp\Framework\Db::getDbConnection($dbConnName);

        if (!$this->dbConn) {
            throw new Exception("This is no valid connection: $dbConnName");
        } // end if not dbConn
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
        $columnNames =  array_map(
            function($col) use ($identityColumn) {
                if ($col["COLUMN_NAME"] == $identityColumn) {
                    return null;
                }
                return "    `".$col["COLUMN_NAME"]."`";
            },
            $columns
        );

        return implode(",\n", array_filter($columnNames));
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
        $columnNames =  array_map(
            function($col) use ($primaryKeys) {
                if (in_array($col["COLUMN_NAME"], $primaryKeys)) {
                    return null;
                }
                return "    `".$col["COLUMN_NAME"]."` = p_".$col["COLUMN_NAME"];
            },
            $columns
        );

        return implode(",\n", array_filter($columnNames));
    } // end function getUpdateColumns

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
        $columnNames =  array_map(
            function($col) use ($identityColumn) {
                if ($col["COLUMN_NAME"] == $identityColumn) {
                    return null;
                }
                return "    p_".\RSPhp\Framework\Str::replace(' ', '_', $col["COLUMN_NAME"]);
            },
            $columns
        );

        return implode(",\n", array_filter($columnNames));
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
        $result = $column["DATA_TYPE"];
        if ($column["CHARACTER_MAXIMUM_LENGTH"]) {
            $result .= "(".$column["CHARACTER_MAXIMUM_LENGTH"].")";
        } // end if char max len

        return $result;
    } // end function getDataType

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
        $result = array_map(
            function($pk) {
                return $pk." = p_".$pk;
            }, // end anonymous function
            $primaryKeys
        ); // end array_map

        return implode("\n    AND ", $result);
    } // end function getPrimaryKeyWhere

    /**
     * Returns a string with a list of columns as parameters
     *
     * @param $columns A collection of columns
     *
     * @return String
     */
    public function getColumnsParameters($columns, $identityColumn = null)
    {
        $result = array_map(
            function($column) use ($identityColumn) {
                if ($column["COLUMN_NAME"] == $identityColumn) {
                    return null;
                }
                $dataType = $this->getDataType($column);
                return "IN p_".$column["COLUMN_NAME"]." ".$dataType;
            }, // end anonymous function
            $columns
        ); // end array_map

        return implode(",\n    ", array_filter($result));
    } // end function getColumnsParameters

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
        $result = array_map(
            function($pk) use($columns) {
                $column = array_filter(
                    $columns,
                    function($col) use($pk) {
                        return $col["COLUMN_NAME"] == $pk;
                    } // end anonymous function
                ); // end array_filter

                $column = array_shift($column);
                $dataType = $this->getDataType($column);
                return "IN p_".$pk." ".$dataType;
            }, // end anonymous function
            $primaryKeys
        ); // end array_map

        return implode(", ", $result);
    } // end function getPrimaryKeyParameters

    /**
     * Returns a string with the requested template
     *
     * @param $templateName The template name
     *
     * @return String
     */
    public function getTemplate($templateName)
    {
        $driver = $this->dbConn->driver;
        $path = dirname(__FILE__ );
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
        $template = \RSPhp\Framework\File::read($path.DS."templates".DS."db".DS.$driver.DS."procedures".DS.$templateName.".sql" );
        return $template;
    } // end function getTemplate

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
        $template = $this->getTemplate("select_all");
        $template = \RSPhp\Framework\Str::replace('$tableName', $tableName, $template);
        $tableColumns = $this->getColumnNames($columns);
        $template = \RSPhp\Framework\Str::replace('$tableColumns', $tableColumns, $template);
        return $template;
    } // end function generateSelectAll

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
        //  Get the template
        $template = $this->getTemplate("select_bykey");

        //  Set the table name
        $template = \RSPhp\Framework\Str::replace('$tableName', $tableName, $template);

        //  Get the table columns
        $tableColumns = $this->getColumnNames($columns);
        $template = \RSPhp\Framework\Str::replace('$tableColumns', $tableColumns, $template);

        //  Get the where clause
        $pkWhere = $this->getPrimaryKeyWhere($primaryKeys);
        $template = \RSPhp\Framework\Str::replace('$pkWhere', $pkWhere, $template);

        //  Get the parameters
        $pkParams = $this->getPrimaryKeyParameters($primaryKeys, $columns);
        $template = \RSPhp\Framework\Str::replace('$pkParams', $pkParams, $template);

        //  Set the columns
        return $template;
    } // end function selectByKey

    /**
     * Returns a string with the stored procedure to insert
     *
     * @param $tableName The name of the table to generate a stored procedure
     * @param $columns a list of columns
     * @param $identityColumn A string containing the identity column name if any
     */
    public function insert($tableName, $columns, $identityColumn = null)
    {
        // Get the template
        $template = $this->getTemplate("insert");

        // Set the table name
        $template = \RSPhp\Framework\Str::replace('$tableName', $tableName, $template);

        //  Get the table columns
        $tableColumns = $this->getColumnNames($columns, $identityColumn);
        $template = \RSPhp\Framework\Str::replace('$tableColumns', $tableColumns, $template);

        //  Get the inserted column clause
        $insertParams = $this->getColumnsParameters($columns, $identityColumn);
        $template = \RSPhp\Framework\Str::replace('$insertParams', $insertParams, $template);

        //  Get the parameters columns names
        $paramsNames = $this->getColumnNamesParameters($columns, $identityColumn);
        $template = \RSPhp\Framework\Str::replace('$paramsNames', $paramsNames, $template);

        //  Set the columns
        return $template;

    } // end function insert


    /**
     * Returns a string with the stored procedure to update
     *
     * @param $tableName The name of the table to generate a stored procedure
     * @param $columns a list of columns
     * @param $primaryKeys The list of primary keys
     */
    public function update($tableName, $columns, $primaryKeys)
    {
        if (count($primaryKeys) === 0) {
            \RSPhp\Framework\RS::printLine("** WARNING ** $tableName doesn't have primary keys defined. skipping update.\n");
            return "";
        } // end if count = 0

        //  Get the update columns clause
        $updateColumns = $this->getUpdateColumns($columns, $primaryKeys);

        if (!$updateColumns) {
            \RSPhp\Framework\RS::printLine("** WARNING ** $tableName doesn't have columns that aren't primary keys. Skipping update.\n");
            return "";
        } // end if not columns

        // Get the template
        $template = $this->getTemplate('update');
        $template = \RSPhp\Framework\Str::replace('$updateColumns', $updateColumns, $template);

        // Set the table name
        $template = \RSPhp\Framework\Str::replace('$tableName', $tableName, $template);

        //  Get the table columns
        $tableColumns = $this->getColumnsParameters($columns);
        $template = \RSPhp\Framework\Str::replace('$updateParams', $tableColumns, $template);

        //  Get the where clause
        $pkWhere = $this->getPrimaryKeyWhere($primaryKeys);
        $template = \RSPhp\Framework\Str::replace('$pkWhere', $pkWhere, $template);

        //  Set the columns
        return $template;
    } // end function update

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
        if (count($primaryKeys) === 0) {
            \RSPhp\Framework\RS::printLine("** WARNING ** $tableName doesn't have primary keys defined. skipping delete.\n");
            return "";
        } // end if count = 0

        //  Get the template
        $template = $this->getTemplate("delete");

        //  Set the table name
        $template = \RSPhp\Framework\Str::replace('$tableName', $tableName, $template);

        //  Get the where clause
        $pkWhere = $this->getPrimaryKeyWhere($primaryKeys);
        $template = \RSPhp\Framework\Str::replace('$pkWhere', $pkWhere, $template);

        //  Get the parameters
        $pkParams = $this->getPrimaryKeyParameters($primaryKeys, $columns);
        $template = \RSPhp\Framework\Str::replace('$pkParams', $pkParams, $template);

        //  Set the columns
        return $template;
    } // end function selectByKey

    /**
     * Generate stored procedures in the connected database
     */
    public function generateProcedures()
    {
        $driver = $this->dbConn->driver;
        $tables = $this->db->getTables();
        $procedure = "";
        $procedures = "";

        foreach ($tables as $table) {

            $columns = $this->db->getColumns($table->TABLE_NAME);
            $primaryKeys = $this->db->getPrimaryKeys($table->TABLE_NAME);
            $identityColumn = $this->db->getIdentityColumn($table->TABLE_NAME);

            //  Generate methods
            //  Replace @placeholders
            $procedures .= "\n/**** Here begins $table->TABLE_NAME procedures ****/\n";

            $selectAll = $this->selectAll($table->TABLE_NAME, $columns);
            //$db->nonQuery($selectAll);
            $procedures .= $selectAll;

            // Only if there's primary keys
            if ($primaryKeys) {
                $selectByKey = $this->selectByKey($table->TABLE_NAME, $columns, $primaryKeys);
                $procedures .= $selectByKey;
            } // end if primary keys

            // Set the insert
            $insert = $this->insert($table->TABLE_NAME, $columns, $identityColumn);
            $procedures .= $insert;

            // Set the update
            $update = $this->update($table->TABLE_NAME, $columns, $primaryKeys);
            $procedures .= $update;

            // Set the delete
            $delete = $this->delete($table->TABLE_NAME, $columns, $primaryKeys);
            $procedures .= $delete;
        } // end for each table

        return $procedures;
    } // end function generateSelectAll
} // end class DbGen
