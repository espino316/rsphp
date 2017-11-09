<?php

namespace RSPhp\Framework;

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

    public function __construct()
    {
        $tables = array();
        $insertData = array();
    } // end function __construct

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
            $this->table = new DbTable($this->tableName);
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

    private function parseInsertData($data)
    {
        $db = new Db($this->dbConn);
        $db->insert(
            $this->tableName,
            $data
        );
    } // end function parseInsertData

    private function parseColumn($args)
    {
        //print_r(["parse column", $args]);
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
        //  Set up the config, from the fileName
        $this->parseConfig($fileName);

        //  Parse the file
        $schemaFile = new FileHandler($fileName);
        while (!$schemaFile->eof) {
            $command = $schemaFile->readLine();
            $this->parseCommand($command);
        } // end while
    } // end function run
} // end class DbSchema
