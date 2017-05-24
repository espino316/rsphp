<?php
/**
 * Outputt.php
 *
 * PHP Version 5
 *
 * Output File Doc Comment
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
 * Handles output functionality
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
class Output
{
    /**
     * Cleans the output
     *
     * @return Null
     */
    public static function clean()
    {
        ob_clean();
    } // end function clean

    /**
     * Cleans and terminate the output buffer
     *
     * @return Bool
     */
    public static function endClean()
    {
        return ob_end_clean();
    } // end function endClean

    /**
     * Sends the buffer to the browser and destroy output buffer
     *
     * @return Bool
     */
    public static function endFlush()
    {
        return ob_end_flush();
    } // end function endFlush

    /**
     * Sends the buffer to the browser
     *
     * @return Null
     */
    public static function flush()
    {
        ob_flush();
    } // end function flush

    /**
     * Gets the output buffer and cleans it
     *
     * @return String
     */
    public static function getClean()
    {
        return ob_get_clean();
    } // end function getClean

    /**
     * Gets the current output buffer
     */
    public static function getContents()
    {
        return ob_get_contents();
    } // end function get

    /**
     * Gets the output and send it to the browser
     *
     * @return String
     */
    public static function getFlush()
    {
        return ob_get_flush();
    } // end function getFlush

    /**
     * Return the length of the current buffer
     *
     * @return Int
     */
    public static function getLength()
    {
        return ob_get_length();
    } // end function getLength

    /**
     * Return the nesting level of the output buffer
     */
    public static function getLevel()
    {
        return ob_get_level();
    } // end function getLevel

    /**
     * Return an array with properties about the output buffer
     *
     * @param Bool $fullStatus default false If true, return all levels, else only top level
     *
     * @return Array
     */
    public static function getStatus( $fullStatus = false)
    {
        return ob_get_status( $fullStatus );
    } // end function getLevel

    /**
     * Compress output buffer
     *
     * @return Null
     */
    public static function gzip()
    {
        ob_start( "ob_gzhandler" );
    } // end function gzip

    /**
     * Indicates php if must flush every echo
     *
     * @param Bool $flag If true, flush every echo, else not
     *
     * @return void
     */
    public static function implicitFlush( $flag = true )
    {
        ob_implicit_flush( $flag );
    } // end function implicitFlush

    /**
     * List current output buffer handlers
     *
     * @return Array
     */
    public static function listHandlers()
    {
        return ob_list_handlers();
    } // end function listHandlers

    /**
     * Turn buffering on
     *
     * @param callable $callBack A function to call when flushed or cleaned. Takes a string and int for options
     * @param Int $chunkSize Number of bytes for every flush
     * @param int $flags Bitmask that control operations
     *
     * @return Bool
     */
    public static function start( $callBack = null, $chunkSize = 0, $flags = PHP_OUTPUT_HANDLER_STDFLAGS )
    {
        return ob_start( $callBack, $chunkSize, $flags );
    } // end function start

    /**
     * Adds name value pair to URL rewrite
     *
     * @param String $name The name of the pair
     * @param String $value The value of the pair
     *
     * @return Bool
     */
    public static function addRewriteVar( $name, $value )
    {
        return output_add_rewrite_var( $name, $value );
    } // end function addRewriteVar

    /**
     * Remove the rewrite vars
     *
     * @return Bool
     */
    public static function resetRewriteVars()
    {
        return output_reset_rewrite_vars();
    } // end function removeRewriteVars

    /**
     * Prints an array or object as Json
     *
     * @param mixed[] $data The data to convert to xml and print to the response
     *
     * @return void
     */
    public static function json( $data )
    {

        $output = self::getContents();
        if (Str::contains($output, '<b>Notice</b>: ') ) {
            self::endClean();
            $data = array();
            $data['error'] = "Notice error." . $output;
        } else if (Str::contains($output, '<b>Warning</b>: ') ) {
            self::endClean();
            $data = array();
            $data['error'] = "Warning error." . $output;
        }

        header('Content-Type: application/json');

        $result = Json::encode($data);
        echo $result;
    } // end function jsonResponse

    /**
     * Writes data as xml to the response
     *
     * @param Array $data The data to writo to response in xml format
     *
     * @return void
     */
    static function xml( $data )
    {
        echo Xml::getString( $data );
    } // end function xmlResponse

} // end class Output

