<?php

namespace RSPhp\Framework;
use Exception;
use stdClass;

class DbSchema
{
    private $tables;
    private $tableName;
    private $isCreate;
    private $isInsert;
    private $table;
    private $schema;
    private $insertData;
    private $dbConn;

    /**
     * List of data types supported
     */
    public $dataTypes = array(
        "string",
        "bool",
        "boolean",
        "bit",
        "int",
        "money",
        "decimal",
        "number",
        "date",
        "timestamp",
        "uuid",
        "point",
        "serial",
        "bigserial",
        "datetime",
        "auto_increment",
        "identity"
    ); // end dataTypes

    /**
     * List of db constraints supported
     */
    public $dataConstraints = array(
        "null",
        "pk",
        "unique",
        "fk",
        "fk|"
    ); // end dataConstraints

    /**
     * Creates an instance of DbSchema
     */
    public function __construct($connectionName = "default")
    {
        $this->tables = array();
        $this->insertData = array();
        $this->dbConn = $connectionName;
    } // end function __construct

    /**
     * Get the table definition
     * @param string $tableName The table name
     *
     * @return DbTable
     */
    public function getTableDefinition($tableName)
    {
        if (count($this->tables)) {
            return array_filter(
                $this->tables,
                function ($t) use ($tableName) {
                    return $t->tableName == $tableName;
                } // end anonymous function
            )[0]; // end array filter 0 index
        } // end if $tables

        if (!$this->dbConn) {
            throw new Exception("No tables parsed nor connection name specified");
        } // end if dbConn

        $db = new Db($this->dbConn);
        $dbTable = new DbTable($this->dbConn, $tableName);

        $columns = $db->getColumns($dbTable->tableName);

        foreach ($columns as $column) {
            $dbTable->column($column);
        } // end foreach table

        //  Get constraints
        $constraints = $db->getTableConstraintsColumns($dbTable->tableName);

        //  Map constraints
        $constraintMap = array();
        foreach($constraints as $constraint) {
            //  Get the name of the constraint
            $name = $constraint->constraint_name;

            //  If no exists the key, create a new one
            if (!array_key_exists($name, $constraintMap)) {
                $constraintMap[$name] = new stdClass;
                $constraintMap[$name]->constraint_type = $constraint->constraint_type;
                $constraintMap[$name]->columns = array();
            } // end if no array key exists

            //  If foreign key, create a definition
            if ($constraint->constraint_type == DbConstraintTypes::ForeignKey) {
                $foreignKeyColumns = new stdClass;
                $foreignKeyColumns->column_name = $constraint->column_name;
                $foreignKeyColumns->foreign_table_name = $constraint->foreign_table_name;
                $foreignKeyColumns->foreign_column_name = $constraint->foreign_column_name;
                $constraintMap[$name]->columns[] = $foreignKeyColumns;
            } else {
                //  If not, only pass the column name
                $constraintMap[$name]->columns[] = $constraint->column_name;
            } // end if foreign key
        } // end for each constraint

        //  Loop the constraints
        foreach ($constraintMap as $constraint) {
            //  If is primary
            if ($constraint->constraint_type == DbConstraintTypes::PrimaryKey) {
                $dbTable->primaryKey($constraint->columns);
            } // end if constraint is primary key

            //  If is unique
            if ($constraint->constraint_type == DbConstraintTypes::Unique) {
                $dbTable->unique($constraint->columns);
            } // end if constraint is primary key

            //  If is foreign key
            if ($constraint->constraint_type == DbConstraintTypes::ForeignKey) {
                foreach ($constraint->columns as $column) {
                    $dbTable->foreignKey(
                        $column->foreign_table_name,
                        $column->column_name,
                        $column->foreign_column_name
                    ); // end foreign key
                } // end for each column in constraint
            } // end if constraint is primary key
        } // end for each

        return $dbTable;
    } // end function getTables

    /**
     * Get the tables in the database
     */
    public function getTables()
    {
        if (count($this->tables)) {
            return $this->tables;
        } // end if $tables

        if (!$this->dbConn) {
            throw new Exception("No tables parsed nor connection name specified");
        } // end if dbConn

        $db = new Db($this->dbConn);
        $tables = $db->getTables();

        foreach ($tables as $table) {
            $dbTable = new DbTable($this->dbConn, $table->table_name);
            $this->tables[] = $dbTable;

            $columns = $db->getColumns($dbTable->tableName);

            foreach ($columns as $column) {
                $dbTable->column($column);
            } // end foreach table
        } // end foreach table

        return $this->tables;
    } // end function getTables

    private function parseTableConstraint($table, $constraint)
    {
        $key = key($constraint);
        $columns = $constraint[$key];

        switch ($key) {
            case "~index":
                $table->index($columns);
            break;
            case "~unique":
                $table->unique($columns);
            break;
            default:
                throw new Exception ("Unknown command $key");
            break;
        } // end switch key
    } // end function parseTableConstraint

    /**
     * Parse a foreign key reference definition
     *
     * @param DbTable   $table      The parent table
     * @param string    $fkCommand  The command containing the fk reference
     *
     * @return null
     */
    private function parseForeignKeyReferenced($column, $fkCommand)
    {
        //  Get the table reference:
        $parts = explode("|", $fkCommand);
        $refTableName = $parts[1];

        if (count($parts) > 2) {
            print_r($parts);
            throw new Exception("More than two parts in reference");
        } // end if more than 2 parts

        $tables = array_filter(
            $this->tables,
            function ($t) use ($refTableName) {
                return $t->tableName == $refTableName;
            } // end anonymous array filter
        ); // end array_filter

        //  If not referenced to self, shift the first found tables
        if ($refTableName != "_self" && count($tables)) {
            $refTable = array_shift($tables);
        } else if ((!count($tables)) && $refTableName == "_self") {
            $refTable = $column->table();
            $refTableName = $column->table()->tableName;
        } else if (!count($tables)) { // if no tables found
            throw new Exception ("Referenced table $refTableName not found.");
        } else {
            throw new Exception("Error with reference $refTableName");
        }  // end if ref table name is self

        $columns = array_filter(
            $refTable->columns,
            function ($c) {
                return $c->isPrimaryKey;
            } // end anonoymous function array filter
        ); // end array_filter

        if (!count($columns)) {
            throw new Exception ("Referenced table $refTableName has no primary key columns");
        } // end if not count columns

        if (count($columns) > 1) {
            throw new Exception ("Referenced table $refTableName as composite key column");
        } // end if columns more than 1

        $refColumn = $columns[0];

        $column->dataType = $refColumn->getFkDataType();
        $column->characterLength = $refColumn->characterLength;
        $column->isForeignKey = true;
        $column->table()->foreignKey($refTableName, $column->name, $refColumn->name);
    } // end function parseForeignKeyComposite

    /**
     * Parse a column definition command and set up the
     * corresponding clauses and constraints
     *
     * @param DbTable   $table The parent table
     * @param string    $line The actual column definition command
     *
     * @return bool     True if ok, false otherwise
     */
    private function parseTableColumn($table, $line)
    {
        if (is_array($line)) {
            $this->parseTableConstraint($table, $line);
            return;
        } // end if no string

        $tokens = explode(" ", $line);
        $count = count($tokens);

        // TODO clean empty tokens
        // TODO make case insensitive
        if ($count < 2) {
            throw new \Exception("Table $table->name column $line must specify at least column name and data type");
        } // end if only one or no tokens

        $columnName = $tokens[0];
        $column = $table->column($columnName);
        unset($tokens[0]);

        foreach($tokens as $token) {
            if ($token == "unique") {
                $column->unique();
                continue;
            } // end if unique

            if ($token == "null") {
                $column->null();
                continue;
            } // end if null

            if ($token == "index") {
                $column->index();
                continue;
            } // end if null

            if ($token == "pk") {
                $column->primaryKey();
                continue;
            } // end if is primary key

            if ($token == "fk") {
                //  TODO: Search through the current array ot tables
                //  find the one with only one column by the column name
                //  and get the table name

                //  Initialize foreign table name
                $foreignTableName = null;
                $referenceColumn = null;

                foreach ($this->tables as $index => $_table) {

                    //  Get the cols with the same name and primary key
                    $cols = array_filter(
                        $_table->columns,
                        function ($c) use($columnName) {
                            return $c->name == $columnName && $c->isPrimaryKey;
                        } // end array filter anonymous function
                    ); // end array filter

                    //  If any
                    if (!count($cols)) {
                        continue;
                    } // end if has columns with same name

                    //  Get the number of columns with primary key in the table
                    $pkCols = array_filter(
                        $_table->columns,
                        function ($col) {
                            return $col->isPrimaryKey;
                        } // end anonymous array filter function
                    ); // end array filter

                    //  If more than one, then error
                    if (count($pkCols) > 1) {
                        print_r($pkCols);
                        throw new Exception("Table $_table->tableName has composite primary key");
                    } // end if has more than one column as primary key

                    //  Set the first - and only - item as the referenced column
                    $col = $cols[0];
                    $foreignTableName = $_table->tableName;
                    $referenceColumn = $col;

                    //  end the loop
                    break;
                } // end foreach

                if (! $foreignTableName) {
                    throw new Exception("No table with pk $columnName, I couldn't find related table");
                } // end if not foreign table name

                if (! $referenceColumn) {
                    throw new Exception("No column in $foreignTableName with name $columnName");
                } // end if not reference column

                $fkName = "fk_$foreignTableName"."_$columnName";
                $table->foreignKey($foreignTableName, $columnName, $columnName);
                $column->dataType = $col->getFkDataType();
                $column->characterLength = $col->characterLength;
                $column->isForeignKey = true;

                continue;
            } // end if is primary key

            //  If is foreign key referenced
            if (Str::startsWith($token, "fk|")) {
                $this->parseForeignKeyReferenced($column, $token);
                continue;
            } // end if foreign different column name

            //  Control variable
            $isDataType = false;

            //  The data types
            foreach ($this->dataTypes as $dataType) {
                if ($token == $dataType) {
                    $column->$token();
                    $isDataType = true;
                } else if (Str::startsWith($token, "string")) {
                    $parts = explode("(", $token);
                    $len = str_replace(")", "", $parts[1]);
                    $column->string($len);
                    $isDataType = true;
                } // end if token is data type
            } // end foreach data type

            if (!$isDataType) {
                throw new Exception ("$token data type not found");
            } // end if no data type
        } // end foreach token

        if (!$column->dataType) {
            throw new Exception ("$column->name data type not found");
        } // end if not data type
    } // end function parseTableColumn

    /**
     * Take a DbColumn and return the correct sql
     *
     * @param DbColumn $column The DbColumn object to parse and get the sql
     *
     * @return string
     */
    private function getColumnSql($column)
    {
        $result = $column->name;

        if ($column->dataType == "varchar") {
            $result .= " $column->dataType"."($column->characterLength) ";
        } else {
            $result .= " $column->dataType ";

            if ($column->isAutoIncrement) {
                if ($dbConn = Db::getDbConnection($column->table->connName)) {
                    switch ($dbConn->driver) {
                        case 'mysql':
                            $result.= " NOT NULL AUTO_INCREMENT";
                        break;
                        case 'sqlsrv':
                            $result.= " IDENTITY NOT NULL";
                        break;
                        case 'dblib':
                            $result.= " IDENTITY NOT NULL";
                        break;
                    } // end switch
                } // end if db conn
            } // end if is auto increment
        } // end if data type is string

        if ($column->isNullable) {
            $result .= " NULL ";
        } // end if column is nullable

        return $result;
    } // end function displaysColumnSql

    private function getConstraintSql($constraint)
    {
        return $constraint->getSql();
    } // end function getConstraintSql

    private function getIndexSql($index)
    {
        return $index->getSql();
    } // end function get index sql

    public function parseYaml($fileName)
    {
        $yaml  = new Yaml;
        $array = $yaml->load($fileName);
        $dataTables = [];

        //  Loopt throught items
        foreach ($array as $tableName => $columns) {
            //  If not directive
            if (!Str::startsWith($tableName, "~")) {

                //  Create new table
                $table = new DbTable("default", $tableName);

                //  Parse the column commands
                foreach ($columns as $column) {
                    $this->parseTableColumn($table, $column);
                } // end foreach column

                //  Add the table
                $this->tables[] = $table;
            } // end if just table definition

            //  Here begins the data insertion
            if ($tableName == '~data') {
                $dataTables = $columns;
            } // end if data
        } // end foreach

        $content = "";
        $db = new Db($this->dbConn);
        foreach($this->tables as $table) {

            //  Clear content
            $content = "";
            //  TODO Validate if table exists, if not exists then create, else
            //  must validate any columns and add only the new ones

            if ($db->tableExists($table->tableName)) {
                RS::printLine("Table $table->tableName already exists");
                continue;
            } // end exists exists

            $content.= "CREATE TABLE $table->tableName (";
            $isDirty = false;

            foreach($table->columns as $column) {
                $comma = $isDirty ? "," : "";
                $content.="$comma\n\t" . $this->getColumnSql($column);
                $isDirty = true;
            } // end foreach column

            foreach($table->constraints as $constraint) {
                $content.=",\n\t" . $this->getConstraintSql($constraint);
            } // end for each constraint

            $content.= "\n);\n\n";

            foreach($table->indexes as $index) {
                $content.="\n\n" . $this->getIndexSql($index);
            } // end for each index

            $db->nonQuery($content);

            foreach ($dataTables as $tableName => $lines) {

                if ($table->tableName == $tableName) {

                    //  Split the lines
                    $lines = explode("\n", $lines);

                    foreach ($lines as $line) {
                        $data = explode(",", $line);
                        $this->insertTableData($table, $data);
                    } // end for each line
                } // end if name
            } // end foreach
        } // end foreach table

        if (File::exists("tables.sql")) {
            File::delete("tables.sql");
        } // end if file exists
        File::write("$fileName.sql", $content);
    } // end function parseYaml

    /**
     * Parse a command
     *
     * @param String $command
     *
     * @return Null
     */
    private function parseCommand($command)
    {
        $command = Str::toLower($command);
        if ($command == "" || $command == "\n") {
            if ($this->isCreate) {
                $this->isCreate = false;
                $this->tableName = null;
                echo $this->table->go();
                $this->tables[] = clone $this->table;
            } // end if is open table
        } // end if ""

        if (Str::startsWith($command, "create")) {
            $this->tableName = trim(Str::replace("create ", "", $command));
            $this->isCreate = true;
            $this->table = new DbTable("default", $this->tableName);
        } // end if

        if (Str::startsWith($command, "insert")) {
            $this->tableName = trim(Str::replace("insert ", "", $command));
            $this->isInsert = true;
        } // end if

        if (
            (Str::startsWith($command, " ") || Str::startsWith($command, "\t")) &&
            $this->isCreate && $this->tableName !== null
        ) {
            $command = trim($command);
            $args = explode(" ", $command);
            $this->parseColumn($args);
        }  // end if is column

        if (
            (Str::startsWith($command, " ") || Str::startsWith($command, "\t")) &&
            $this->isInsert && $this->tableName !== null
        ) {
            $command = trim($command);
            $data = explode(",", $command);
            $this->parseInsertData($data);
        }  // end if is column

    } // end function parseCommand

    /**
     * Inserts the data into a table
     *
     * @param $table The table to insert data in
     * @param $data The data to be inserted in
     *
     * @return null
     */
    private function insertTableData($table, $data) {
        $db = new Db($this->dbConn);
        $i = 0;
        $result = [];
        foreach ($table->columns as $column) {
            if (!$column->isAutoIncrement) {

                //  Here we clean the input strip quotes
                $text = $data[$i];
                $text = str_replace("\'","~", $text);
                $text = str_replace("'","", $text);
                $text = str_replace("~","'", $text);
                $text = htmlspecialchars($text);

                //  Here we assign result to the array
                $result[$column->name] = $text;
                //  Increment counter
                $i++;
            } // end if not auto increment
        } // end foreach

        $db->insert(
            $table->tableName,
            $result
        );
    } // end function

    private function parseInsertData($data)
    {
        $db = new Db($this->dbConn);
        $db->insertTableData(
            $this->tableName,
            $data
        );
    } // end function parseInsertData

    private function parseColumn($args)
    {
        $name = $args[0];
        $type = $args[1];

        switch($type) {
            case "auto_increment":
                $this->table->column($name)->autoIncrement();
            break;
            case "string":
                $len = null;
                if ($args[2] && is_numeric($args[2])) {
                    $len = $args[2];
                } // end if len

                $this->table->column($name)->string($len);
            break;
            case "int":
                $this->table->column($name)->int();
            break;
        } // end switch type
    } // end function parseColumn

    public function update($fileName)
    {
        //  Parse the file
        $schemaFile = new FileHandler($fileName);
        while (!$schemaFile->eof) {
            $command = $schemaFile->readLine();
            $this->parseCommand($command);
        } // end while
    } // end function run
} // end class DbSchema
