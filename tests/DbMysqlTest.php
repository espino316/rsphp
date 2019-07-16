<?php
use PHPUnit\Framework\TestCase;
use RSPhp\Framework\DbConnection;
use RSPhp\Framework\Db;

class DbMySqlTest extends TestCase
{
    private $connArr = array(
                "driver" => "mysql",
                "hostName" => "localhost",
                "databaseName" => "rsphp_test",
                "userName" => "root",
                "password" => "Sp1n4l01"
            );

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
        $this->expectException(InvalidArgumentException::class);
        $db = new Db(
            array(
                "driver" => "mysql",
                "hostName" => "localhost",
                "databaseName" => "rsphp_test",
                "password" => "Sp1n4l01"
            )
        );
    } // end function testConnectionArguments

    public function testConnectionArguments()
    {
        $db = new Db($this->connArr);
        $this->assertInstanceOf(Db::class, $db);
        return $db;
    } // end function testConnectionArguments

    /**
     * @depends testConnectionArguments
     */
    public function testQueryConnect( $db )
    {
        $result = $db->query( "SELECT NOW();" );
        $this->assertTrue(
            count( $result ) >= 1
        );

        return $db;
    } // end function testConnectServerOff

    public function testGenerateSelectAllProcedure()
    {
        $procStatement = $dbGen->generateSelectAllProcedure("test");

    } // end function testGenerateSelectAllProcedure
} // end class DbTest
