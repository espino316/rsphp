<?php
define( "DS", DIRECTORY_SEPARATOR);
define( "ROOT", dirname( dirname( __FILE__ ) ) );
define( "TAB", "\t");
define( "CRLF", "\r\n");
define( "NEW_LINE","\r\n");
define( "IS_CLI", (php_sapi_name() === 'cli') );
define( "BASE_URL", "http://localhost/" );

use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    public function testParseFile() {
        print_r(Spyc::YAMLload(ROOT.DS."tests".DS."test.yaml"));
    } // end function testGetGlobals
} // end class AppTest
