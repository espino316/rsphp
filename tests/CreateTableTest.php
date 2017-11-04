<?php
use PHPUnit\Framework\TestCase;
use RSPhp\Framework\DbTable;
use RSPhp\Framework\DbColumn;

class WebTest extends TestCase
{
    public function testCreateTable()
    {
        $table = new DbTable("myTable");
        $table->column("my_id")->autoIncrement();
        $table->column("my_name")->string(30)->unique();
        $table->column("datetime")->timestamp();
        $schema = $table->go();

        echo $schema;

        $this->assertTrue(
            ($schema !== null)
        );

    } // end function testConnectionArgumentsNowhere

} // end class DbTest
