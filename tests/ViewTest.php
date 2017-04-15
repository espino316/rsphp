<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\View;
use RSPhp\Framework\String;

class ViewTest extends TestCase
{
    public function testLoadToStringPhp()
    {
        $string = String::trim( View::loadToString( "testPhp" ) );
        $this->assertTrue(
            $string == "<h1>Hola</h1>"
        );
    } // end public function testQuery

    public function testLoadToStringHtml()
    {
        $string = String::trim( View::loadToString( "testHtml" ) );
        $this->assertTrue(
            $string == "<h1>Hola</h1>"
        );
    } // end public function testQuery

} // end class ViewTest
