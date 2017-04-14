<?php
use PHPUnit\Framework\TestCase;
use RSPhp\Framework\Db;

class DbTest extends TestCase
{
    /**
     * @expectedException invalidArgumentException
     */
    public function testConnectionNoArguments()
    {
        $db = new Db();
    } // end public function testQuery

    /**
     * @expectedException invalidArgumentException
     */
    public function testConnectionIncompleteArguments()
    {
        $db = new Db(
            array(
                "driver" => "pgsql",
                "hostName" => "localhost",
                "databaseName" => "rsphp",
                "userame" => "postgres",
                "password" => "Sp1n4l01"
            )
        );

        return $db;
    } // end function testConnectionArguments

    public function testConnectionArguments()
    {
        $db = new Db(
            array(
                "driver" => "pgsql",
                "hostName" => "localhost",
                "databaseName" => "rsphp",
                "userName" => "postgres",
                "password" => "Sp1n4l01"
            )
        );

        return $db;
    } // end function testConnectionArguments

    public function testConnectionArgumentsNowhere()
    {
        $db = new Db(
            array(
                "driver" => "pgsql",
                "hostName" => "192.168.1.1",
                "databaseName" => "rsphp",
                "userName" => "postgres",
                "password" => "Sp1n4l01"
            )
        );

        return $db;
    } // end function testConnectionArgumentsNowhere

    /**
     * @depends testConnectionArgumentsNowhere
     * @expectedException PDOException
     */
    public function testQueryFailConnect( $db )
    {
        $this->markTestSkipped(
            'Speed up test'
        );
        return true;
        $db->query( "SELECT * FROM customers" );
    } // end function testConnectServerOff

    /**
     * @depends testConnectionArguments
     */
    public function testQueryConnect( $db )
    {
        $result = $db->query( "SELECT * FROM clientes" );
        $this->assertTrue(
            count( $result ) >= 1
        );
    } // end function testConnectServerOff
} // end class DbTest
