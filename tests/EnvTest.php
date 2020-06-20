<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\Env;

class EnvTest extends TestCase
{
    public function testSetEnv() {
        Env::set( 'varString', 'Hello world!' );
        Env::set( 'varArray', array( "key" => "value" ) );
        return true;
    } // end function testSetGlobals

    /**
     * @depends testSetEnv
     */
    public function testGetEnv() {

        $varString = Env::get( 'varString' );
        $varArray = Env::get( 'varArray' );

        $this->assertEquals(
            $varString,
            'Hello world!'
        );

        $this->assertEquals(
            $varArray,
            array( "key" => "value" )
        );
    } // end function testGetGlobals

    /**
     * @depends testSetEnv
     */
    public function testRemoveEnv() {

        Env::remove('varString');

        $this->assertEquals(
            Env::get('varString'),
            null
        ); // end assert equals
    } // end function remove ent
} // end EnvTest class
