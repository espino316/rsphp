<?php
/**
 * Input.php
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
 * App Class Doc Comment
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
class App
{
    protected static $variables;

    /**
     * Set a variable in the application
     *
     * @param String       $name  The variable's name
     * @param mixed[]|null $value The variable's value
     *
     * @return void
     */
    public static function set($name, $value = null)
    {
        if (!self::$variables ) {
            self::$variables = array();
        }

        if (is_array($name) ) {
            foreach ( array_keys($name) as $key ) {
                self::$variables[$key] = $name[$key];
            }
        } else {
            self::$variables[$name] = $value;
        }
    } // end function set

    /**
     * Returns a global variable value
     *
     * @param String $name The variable's name
     *
     * @return mixed[]
     */
    public static function get($name)
    {
        if (self::$variables ) {
            if (isset(self::$variables[$name]) ) {
                 return self::$variables[$name];
            } else {
                return null;
            } // end if then else self::variables[name] is set
        } else {
            return null;
        } // end if then else self::variables exists
    } // end function get

} // end class App
