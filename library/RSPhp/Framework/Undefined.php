<?php
/**
 * Undefined.php
 *
 * PHP Version 5
 *
 * Input File Doc Comment
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
 * This class has no functionality, it's only a type
 *
 * Manages global variables in the application
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
final class Undefined
{
    /**
     * Returns singleton
     *
     * @return Undefined
     */
    public static function instance()
    {
        return new Undefined;
    } // end function Instance

    /**
     * Private construct, no public instances
     */
    private function __construct()
    {
        // Do nothing
    } // end function __construct
} // end class Undefined
