<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\RS;

class RSTest extends TestCase
{
    public function testCreateController()
    {
        $fileName = RS::createController(
            "Testing",
            "Controller for testing"
        );

        $this->assertTrue(
            file_exists( $fileName )
        );

        return $fileName;
    } // end public function testQuery

    public function testRemoveDataSources()
    {
        $fileDataSources = ROOT.DS.'config'.DS.'datasources.json';
        if ( file_exists( $fileDataSources ) ) {
            unlink( $fileDataSources );
        } // end if file exists

        return ( file_exists ( $fileDataSources ) );
    } // end function removeDataSources

    /**
     * @depends testRemoveDataSources
     */
    public function testAddDataSource( $fileExists )
    {
        if ( ! $fileExists ) {
            $fileName = RS::addDataSource(
                "default",
                "testDataSource",
                "query",
                "SELECT * FROM customers"
            );

            $this->assertTrue(
                file_exists( $fileName )
            );
        } else {
            $this->markTestIncomplete(
                'testAddDataSource incomplete! file exists.'
            );
        } // end if then else file exists
    } // end function testAddDataSource

    public function testReadLine()
    {
        RS::printLine("What's your connection name?: (default)");
        $name = RS::readLine();
        $name = ($name) ? $name : 'default';

        RS::printLine("What's your db engine?: (mysql | pgsql | sqlsrv | dblib)");
        $driver = RS::readLine();
        while (!$driver) {
            RS::printLine("You must provide a db driver");
            RS::printLine("What's your db engine?: (mysql | pgsql | sqlsrv | dblib)");
            $driver = RS::readLine();
        } // end while not drive

        RS::printLine("What's your database name?:");
        $databaseName = RS::readLine();
        while (!$databaseName) {
            RS::printLine("You must provide a database name");
            RS::printLine("What's your database name?:");
            $databaseName = RS::readLine();
        } // end while not drive

        RS::printLine("What's the db user's name?:");
        $userName = RS::readLine();
        while (!$userName) {
            RS::printLine("You must provide an user name");
            RS::printLine("What's the db user's name?:");
            $userName = RS::readLine();
        } // end while not drive

        RS::printLine("What's the db user's password?:");
        $pwd = RS::readLineSecret();
        while (!$pwd) {
            RS::printLine("You must provide an user's password");
            RS::printLine("What's the db user's password?:");
            $pwd = RS::readLineSecret();
        } // end while not drive

        RS::printLine("Any specific port?:");
        $port = RS::readLine();

        print_r(
            array(
                $name,
                $driver,
                $hostName,
                $databaseName,
                $userName,
                $pwd,
                $port
            )
        );
    } // end function testReadLine
} // end class DbHelperTest
