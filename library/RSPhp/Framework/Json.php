<?php
/**
 * Json.php
 *
 * PHP Version 5
 *
 * Json File Doc Comment
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
 * Handles json functionality
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
class Json
{
    /**
     * Decode json string
     *
     * @param String $json The json string to decode
     * @param Bool $isAssoc If true return assoc arrays, else return object
     *
     * @return mixed|Array|Object
     */
    public static function decode( $json, $isAssoc = false)
    {
        return json_decode( $json, $isAssoc );
    } // end function clean

    /**
     * Takes $data and encode it into json string
     *
     * @param mixed|Array|Object $data The data to encode
     *
     * @return String
     */
    public static function encode( $data )
    {
        return json_encode( $data );
    } // end function encode

    /**
     * Returns last error string
     *
     * @return String
     */
    public static function lastErrorMsg()
    {
        return json_last_error_msg();
    } // end function lastErrorMsg

    /**
     * Returns last error
     *
     * @return Int
     */
    public static function lastError()
    {
        return json_last_error();
    } // end function lastErrorMsg
} // end class Json

