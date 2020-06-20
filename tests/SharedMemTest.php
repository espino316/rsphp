<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\SharedMem;

class SharedMemTest extends TestCase
{
    public function testSetSharedMem() {
        SharedMem::set( 'varString', 'Hello world!' );
        SharedMem::set( 'varArray', array( "key" => "value" ) );
        return true;
    } // end function testSetGlobals

    /**
     * @depends testSetSharedMem
     */
    public function testGetSharedMem() {

        $varString = SharedMem::get( 'varString' );
        $varArray = SharedMem::get( 'varArray' );

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
     * @depends testSetSharedMem
     */
    public function testRemoveSharedMem() {

        SharedMem::remove('varString');

        $this->assertEquals(
            SharedMem::get('varString'),
            null
        ); // end assert equals
    } // end function remove ent
} // end EnvTest class
