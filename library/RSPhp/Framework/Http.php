<?php
/**
 * Http.php
 *
 * PHP Version 5
 *
 * Http Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

/**
 * Helper to do Http requests
 *
 * Please report bugs on https://github.com/espino316/rsphp/issues
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class Http
{
    /**
     * Variable to hold headers
     */
    private static $_headers = array();

    /**
     * Function to retrive the headers
     *
     * @param Resource $curl The curl object
     * @param String $headerLine The header line returned
     *
     * @return Int
     */
    public static function headersCallBack( $curl, $headerLine )
    {
        self::$_headers[] = $headerLine;
        return strlen( $headerLine );
    } // end function headersCallBack

    /**
     * Makes an web request
     *
     * @param String $metho The method to use GET|POST|PUT|DELETE|OPTIONS
     * @param String $url The url for the request
     * @param Array $data The data to sent, assoc array, key value pair
     * @param Array $headers The headers to sent, assoc array, key value pair
     *
     * @return null
     */
    private static function _request(
        $method, $url, $data = null, $headers = null
    ) {
        //  Refresh headers
        self::$_headers = array();

        $curl = null;

        if ( $method == "GET" && $data && is_array( $data ) ) {
            $url .= "?" . http_build_query( $data );
            $curl = curl_init( $url );
        } else {
            $curl = curl_init();
        } // end if


        if ( $headers && is_array( $headers ) ) {
            $headersData = array();
            foreach( $headers as $key => $value ) {
                $headersData[] = "$key: $value";
            } // en forEach

            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                $headersData
            ); // end setopt
        } // end if headers

        $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_HEADER_OUT => true,
                CURLOPT_HEADERFUNCTION => array(
                    "RSPhp/Framework/Http",
                    "headerCallBack"
                ) // end callback data
            ); // end options array

        switch ( $method ) {
            case "POST":
                $options[CURLOPT_URL] = $url;
                $options[CURLOPT_POST] => true;
                if ( $data ) {
                    $options[CURLOPT_POSTFIELDS] => $data;
                } // end if data
            break;

            case "PUT":
                $options[CURLOPT_URL] = $url;
                $options[CURLOPT_CUSTOMREQUEST] = "PUT";
                if ( $data ) {
                    $options[CURLOPT_POSTFIELDS] => $data;
                } // end if data
            break;

            case "DELETE":
                $options[CURLOPT_URL] = $url;
                $options[CURLOPT_CUSTOMREQUEST] = "DELETE";
                if ( $data ) {
                    $options[CURLOPT_POSTFIELDS] => $data;
                } // end if data
            break;

            case "OPTIONS":
                $options[CURLOPT_URL] = $url;
                $options[CURLOPT_CUSTOMREQUEST] = "OPTIONS";
                if ( $data ) {
                    $options[CURLOPT_POSTFIELDS] => $data;
                } // end if data
            break;

            case "HEADER":
                $options[CURLOPT_URL] = $url;
                $options[CURLOPT_CUSTOMREQUEST] = "HEADER";
                if ( $data ) {
                    $options[CURLOPT_POSTFIELDS] => $data;
                } // end if data
            break;
        } // end switch

        curl_setopt_array( $curl, $options ); // end curl setopt array

        $response = curl_exec( $curl );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close( $curl );

        $response = new HttpResponse(
            $response, self::$_headers, $httpCode
        );
        return $response;
    } // end function request

    /**
     * Makes an web request, method GET
     *
     * @param String $url The url for the request
     * @param Array $data The data to sent, assoc array, key value pair
     * @param Array $headers The headers to sent, assoc array, key value pair
     *
     * @return null
     */
    public static function get( $url, $data = null, $headers = null )
    {
        return self::_request( "GET", $url, $data, $headers );
    } // end function request

    /**
     * Makes an web request, method POST
     *
     * @param String $url The url for the request
     * @param Array $data The data to sent, assoc array, key value pair
     * @param Array $headers The headers to sent, assoc array, key value pair
     *
     * @return null
     */
    public static function post( $url, $data = null, $headers = null )
    {
        return self::_request( "POST", $url, $data, $headers );
    } // end function request

    /**
     * Makes an web request, method PUT
     *
     * @param String $url The url for the request
     * @param Array $data The data to sent, assoc array, key value pair
     * @param Array $headers The headers to sent, assoc array, key value pair
     *
     * @return null
     */
    public static function put( $url, $data = null, $headers = null )
    {
        return self::_request( "PUT", $url, $data, $headers );
    } // end function request

    /**
     * Makes an web request, method DELETE
     *
     * @param String $url The url for the request
     * @param Array $data The data to sent, assoc array, key value pair
     * @param Array $headers The headers to sent, assoc array, key value pair
     *
     * @return null
     */
    public static function delete( $url, $data = null, $headers = null )
    {
        return self::_request( "DELETE", $url, $data, $headers );
    } // end function request

    /**
     * Makes an web request, method OPTIONS
     *
     * @param String $url The url for the request
     * @param Array $data The data to sent, assoc array, key value pair
     * @param Array $headers The headers to sent, assoc array, key value pair
     *
     * @return null
     */
    public static function delete( $url, $data = null, $headers = null )
    {
        return self::_request( "OPTIONS", $url, $data, $headers );
    } // end function request
} // end class Http
