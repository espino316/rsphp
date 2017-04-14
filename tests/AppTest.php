<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname( dirname( __FILE__ ) ) );
define('TAB', "\t");
define('CRLF', "\r\n");
define('NEW_LINE',"\r\n");
define( 'IS_CLI', (php_sapi_name() === 'cli') );

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\App;

class AppTest extends TestCase
{
    public function testSetGlobals() {
        App::set( 'varString', 'Hello world!' );
        App::set( 'varArray', array( "key" => "value" ) );
        return true;
    } // end function testSetGlobals

    /**
     * @depends testSetGlobals
     */
    public function testGetGlobals() {
        $varString = App::get( 'varString' );
        $varArray = App::get( 'varArray' );

        $this->assertEquals(
            $varString,
            'Hello world!'
        );

        $this->assertEquals(
            $varArray,
            array( "key" => "value" )
        );
    } // end function testGetGlobals
} // end class AppTest
