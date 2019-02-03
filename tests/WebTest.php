<?php
use PHPUnit\Framework\TestCase;
use RSPhp\Framework\WebConnection;
use RSPhp\Framework\Web;
use RSPhp\Framework\HttpContentTypes;
use RSPhp\Framework\DataSource;

class WebConnTest extends TestCase
{
    public function testSetWebConnection()
    {
        $webConn = new WebConnection(
            array(
                "endPoint" => "http://jsonplaceholder.typicode.com",
                "method" => "GET",
                "headers" => null,
                "parameters" => null,
                "contentType" => HttpContentTypes::UrlEncoded
            )
        );

        Web::setWebConnection( "testConnection", $webConn );

        $this->assertTrue(
            Web::hasWebConnections()
        );

    } // end function testSetWebConnection

    public function testWebDataSource()
    {
        $webConn = new WebConnection(
            array(
                "endPoint" => "https://jsonplaceholder.typicode.com",
                "method" => "GET",
                "headers" => null,
                "parameters" => null,
                "contentType" => HttpContentTypes::UrlEncoded
            )
        );

        Web::setWebConnection("WebTestConnection", $webConn);

        $ds = new DataSource("WebTestConnection", "dsJsonPlaceHolderPosts", "HTTP", "posts");
        $resultSet = $ds->getResultSet();

        $this->assertTrue(
            count($resultSet) > 0
        );

    } // end function testConnectionArgumentsNowhere

} // end class DbTest
