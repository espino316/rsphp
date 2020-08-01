<?php
require_once 'public/index.php';

use PHPUnit\Framework\TestCase;
use RSPhp\Framework\RS;

class RouteTest extends TestCase
{
    public function testRoute()
    {
        $method = "*";
        $url = "pruebas/inicio";
        $newUrl = "testing/index";

        RS::addRoute($method, $url, $newUrl);

        $fileName = 'config/app.json';

        //  Make sure file exists
        $this->assertTrue(
            file_exists( $fileName )
        );

        $json = file_get_contents($fileName);
        $json = json_decode($json);

        //  Make sure json is loaded
        $this->assertTrue(
            $json != null
        );

        $routes = $json->routes;

        //  Make sure json element exists
        $this->assertTrue(
            $json->routes != null
        ); // end assertTrue

        //  Get the first element
        $route = $routes[0];

        // Test method, url, newUrl
        $this->assertTrue(
            ($route->method == $method && $route->url == $url && $route->newUrl == $newUrl)
        ); // end assertTrue
    } // end public function testQuery
} // end class RouteTest
