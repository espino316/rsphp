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
 * Config Class Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class Config
{

    private static $_data = array();

    /**
     * Loads the configuration
     *
     * @return void
     */
    static function load()
    {

        $folder = ROOT.DS.'config';
        $files = Directory::getFiles($folder, array( '.json' ));
        foreach ( $files as $file ) {
            self::_loadConfig($file);
        } // end foreach

        if (isset(self::$_data['configFiles']) ) {
            foreach ( self::$_data['configFiles'] as $file ) {
                self::_loadConfig($file);
            } // end foreach configFiles
        } // end if configFiles

        self::_processConfig();
    } // end function load

    /**
     * Return a configuration value
     *
     * @param String $key The configuration key name
     *
     * @return mixed[]
     */
    static function get($key = null)
    {
        if ($key == null ) {
             return self::$_data;
        } else {
            if (array_key_exists($key, self::$_data)) {
                return self::$_data[$key];
            } else {
                return null;
            } // end if array key exists
        } // end if $key is null
    } // end function get

    /**
     * Sets a cofiguration value
     *
     * @param String  $key   The configuration key for identification
     * @param mixed[] $value The configuration value
     *
     * @return void
     */
    static function set( $key, $value )
    {
        self::$_data[$key] = $value;
    } // end function set

    /**
     * Loads a configuration file
     *
     * @param String $file The file-to-load location
     *
     * @return void
     */
    private static function _loadConfig( $file )
    {
        //	Read json into array
        if (file_exists($file) ) {
            $config = file_get_contents($file);
            $config = json_decode($config, true);
             self::$_data = array_merge(self::$_data, $config);
        } else {
            throw new Exception("$file do not exists");
        } // end if file exists
    } // end function _loadConfig

    /**
     * Process the configuration
     *
     * @return void
     */
    private static function _processConfig()
    {
        $config = self::$_data;

        //	Appname
        if (isset($config['appName']) ) {
            if (!defined('APP_NAME') ) {
                define('APP_NAME', $config['appName']);
            } // end if not defined app name
        } // end if config->appName

        //	Data Connections
        if (isset($config["dbConnections"]) ) {
            foreach ( $config['dbConnections'] as $dbConn ) {
                Db::setDbConnection(
                    $dbConn["name"],
                    new DbConnection( $dbConn )
                );
                //print_r( Db:$connections['default'] );
            } // end foreach
        } // if isset dbConnections

        //	Routes
        $routes = array();
        if (isset($config["routes"]) ) {
            foreach ( $config['routes'] as $route ) {
                $routes[]
                    =   new Route(
                        $route['method'],
                        $route['url'],
                        $route['newUrl']
                    );
                //print_r( Db:$connections['default'] );
            } // end foreach
            App::set('routes', $routes);
        } // end if routes

        //	Global variables
        if (isset($config['globals']) ) {
            App::set($config['globals']);
        } // end if isset globals

        //	Load dataSources
        self::_loadDataSources();
    } // end function _processConfig

    /**
     * Load the datasources from the configuration
     *
     * @return void
     */
    private static function _loadDataSources()
    {
        if ( !array_key_exists( "datasources", self::$_data ) ) {
            return;
        } // end if not exist datasources
        $dataSources = self::$_data['dataSources'];

        foreach ( $dataSources as $ds ) {
            $dataSource = new DataSource(
                $ds['connection'],
                $ds['name'],
                $ds['type'],
                $ds['text']
            ); // end add dataSources

            if (isset($ds['parameters']) ) {
                  $params = $ds['parameters'];
                foreach ( $params as $param ) {

                    if (isset($param['defaultValue']) ) {
                        $default = $param['defaultValue'];
                    } else {
                            $default = null;
                    }
                       $dataSource->addParam(
                           $param['name'],
                           $param['type'],
                           $default
                       );
                } // end function foreach
            } // end if parameters

            if (isset($ds['filters']) ) {
                     $filters = $ds['filters'];
                foreach ( $filters as $key => $value ) {
                    $dataSource->addFilter(
                        $key,
                        $value
                    );
                } // end function foreach
            } // end if parameters
            Db::setDataSource( $db["name"], $dataSource );
        } // end foreach datasources
    } // end function _loadDataSources
} // end class Config
