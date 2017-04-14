<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\Config;

class ConfigTest extends TestCase
{
    public function testLoadGet()
    {
        Config::load();
        $this->assertTrue(
            Config::get( "appName" ) == "My Application"
        );
    } // end public function testQuery

} // end class ConfigTest
