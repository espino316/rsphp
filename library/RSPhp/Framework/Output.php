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
    public static function get()
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
} // end class Output

