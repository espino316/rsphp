<?php
/**
 * DbConnection.php
 *
 * PHP Version 5
 *
 * DbConnection File Doc Comment
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
 * Represents a connection to a database
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
class DbConnection
{

    public $driver;
    public $hostName;
    public $databaseName;
    public $userName;
    public $password;
    public $port;

    /**
     * Creates an instance of DbConnection
     *
     * @param Array $options An array with the options parameters
     * to construct the connection.
     *
     * @return void
     */
    function __construct( $options )
    {
        print_r( $options );

        $this->driver = $options['driver'];
        $this->hostName = $options['hostName'];
        $this->databaseName = $options['databaseName'];
        $this->userName = $options['userName'];
        $this->password = $options['password'];

        if (array_key_exists('port', $options) ) {
            $this->port = $options['port'];
        }

    } // end __construct

} // end class
