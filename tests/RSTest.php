<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('TAB', "\t");
define('CRLF', "\r\n");
define('NEW_LINE',"\r\n");
define( 'IS_CLI', (php_sapi_name() === 'cli') );

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
} // end class DbHelperTest
