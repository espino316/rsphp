<?php

namespace RSPhp\Framework;

use Exception;

class DbTable
{
    private $tableName;
    private $columns;
    private $connName;

    public function __construct($connName, $tableName)
    {
        $this->connName = $connName;
        $this->tableName = $tableName;
        $this->columns = array();
    } // end function construct

    public function column($columnName)
    {
        $column = new DbColumn($columnName);
        $this->columns[] = $column;
        return $this->columns[count($this->columns)-1];
    } // end function column

    public function go()
    {
        $db = new Db($this->connName);
        $template = "CREATE TABLE $this->tableName (\n\t@columns\n);";
        $columns = "";

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
        print_r($template);
    } // end function

} // end class DbTable
