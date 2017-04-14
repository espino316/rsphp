<?php

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\Controller;
use RSPhp\Framework\Output;
use RSPhp\Framework\Json;

class ControllerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testJsonResponseOutputGetCleanJsonDecode()
    {
        $data["name"] = "Luis";
        $data["lastName"] = "Espino";

        $controller = new Controller();

        $controller->jsonResponse( $data );

        $json = Output::getClean();

        $json = Json::decode( $json, true );

        $this->assertTrue(
            $data == $json
        );
    } // end public function testQuery

} // end class ConfigTest
