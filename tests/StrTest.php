<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\Str;

class StrTest extends TestCase
{
    public function testIsUpperCase()
    {

        $this->assertTrue(
            Str::isUpperCase( "HELLO" )
        );

        $this->assertFalse(
            Str::isUpperCase( "hello" )
        );
    } // end public function testQuery

    public function testContains()
    {
        $this->assertTrue(
            Str::contains( "HolaLuis", "ola" )
        );

        $this->assertFalse(
            Str::contains( "HolaLuis", "Martin" )
        );
    } // end public function testContains

    public function testStartsWith()
    {
        $this->assertTrue(
            Str::startsWith( "Hola Mundo", "Hola" )
        );

        $this->assertFalse(
            Str::startsWith( "Hola Mundo", "Mundo" )
        );
    } // end function testStartsWith

} // end class StrTest
