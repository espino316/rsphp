<?php
/**
 * Web.php
 *
 * PHP Version 5
 *
 * Web File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use Exception;
use InvalidArgumentException;

/**
 * Helper for database manipulation
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
class Web
{
    private static $webConnections = array();
    private static $dataSources = array();

    /**
     * Sets a datasource into the array of datasources
     *
     * @param String $dsName The datasource's name
     * @param DataSource $ds The actual datasource
     *
     * @return Null
     */
    public static function setDataSource( $dsName, $ds )
    {
        self::$dataSources[$dsName] = $ds;
    } // end function setDataSource

    /**
     * Returns a datasource from it's name
     *
     * @param String $dsName The datasource's name
     *
     * @return DataSource
     */
    public static function getDataSource( $dsName )
    {
        if (isset(self::$dataSources[$dsName]) ) {
            return self::$dataSources[$dsName];
        } // end if then else

        return null;
    } // end getDataSource

    /**
     * Sets a web connection into the array of connections
     *
     * @param String $connName The connection name
     * @param WebConnection $conn The actual connection
     *
     * @return Null
     */
    public static function setWebConnection( $connName, $conn )
    {
        self::$webConnections[$connName] = $conn;
    } // end function setDataSource

    /**
     * Returns a WebConnection from it's name
     *
     * @param String $connName The webConnections's name
     *
     * @return WebConnection
     */
    public static function getWebConnection( $connName )
    {
        if ( isset( self::$webConnections[$connName] ) ) {
            return self::$webConnections[$connName];
        } else {
            return null;
        } // end if then else
    } // end getConnection

    /**
     * Return true if connections are set, else return false
     *
     * @return Bool
     */
    public static function hasWebConnections()
    {
        if ( count( self::$webConnections ) ) {
            return true;
        } else {
            return false;
        } // end if count $webConnections
    } // end function hasWebConnections
} // end class Db
