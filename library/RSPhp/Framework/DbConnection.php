<?php
/**
 * DbConnection.php
 *
 * PHP Version 7
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
        $this->driver = $options['driver'];
        $this->hostName = $options['hostName'];
        $this->databaseName = $options['databaseName'];
        $this->userName = $options['userName'];
        if ( !isset( $options["password"] ) ) {
            $this->password = "";
        } else {
            $this->password = $options['password'];
        } // end if not password

        if (array_key_exists('port', $options) ) {
            $this->port = $options['port'];
        }

        if (Str::startsWith($this->hostName, "ENV::")) {
            $this->hostName = Env::get(Str::replace('ENV::', '', $this->hostName));
        } // end if startsWith 'ENV::'

        if (Str::startsWith($this->databaseName, "ENV::")) {
            $this->databaseName = Env::get(Str::replace('ENV::', '', $this->databaseName));
        } // end if startsWith 'ENV::'

        if (Str::startsWith($this->userName, "ENV::")) {
            $this->userName = Env::get(Str::replace('ENV::', '', $this->userName));
        } // end if startsWith 'ENV::'

        if (Str::startsWith($this->password, "ENV::")) {
            $this->password = Env::get(Str::replace('ENV::', '', $this->password));
        } // end if startsWith 'ENV::'

    } // end __construct

} // end class
