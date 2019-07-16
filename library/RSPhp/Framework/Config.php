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

    private static $data = array();

    /**
     * Loads the configuration
     *
     * @return void
     */
    static function load()
    {
        try {
            $folder = ROOT.DS.'config';
            $files = Directory::getFiles($folder, array( '.json' ));
            foreach ( $files as $file ) {
                self::loadConfig($file);
            } // end foreach

            if (isset(self::$data['configFiles']) ) {
                foreach ( self::$data['configFiles'] as $file ) {
                    self::loadConfig($file);
                } // end foreach configFiles
            } // end if configFiles

            self::processConfig();
        } catch ( \Exception $ex ) {
            RS::printLine( "ERROR:" );
            RS::printLine( $ex->getMessage() );
        }
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
             return self::$data;
        } else {
            if (array_key_exists($key, self::$data)) {
                return self::$data[$key];
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
        self::$data[$key] = $value;
    } // end function set

    /**
     * Loads a configuration file
     *
     * @param String $file The file-to-load location
     *
     * @return void
     */
    private static function loadConfig( $file )
    {
        //	Read json into array
        if (file_exists($file) ) {
            $config = file_get_contents($file);
            $config = json_decode($config, true);
            self::$data = array_merge(self::$data, $config);
        } else {
            throw new \Exception("$file do not exists");
        } // end if file exists
    } // end function loadConfig

    /**
     * Process the configuration
     *
     * @return void
     */
    private static function processConfig()
    {
        $config = self::$data;

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

        if (isset($config["webConnections"]) ) {
            foreach ( $config['webConnections'] as $webConn ) {
                Web::setWebConnection(
                    $webConn["name"],
                    new WebConnection( $webConn )
                );
            } // end foreach
        } // if isset webConnections

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
        self::loadDataSources();

        //  Load languages
        if (App::get("language")) {
            Translation::load();
        } // end if language
    } // end function processConfig

    /**
     * Load the datasources from the configuration
     *
     * @return void
     */
    private static function loadDataSources()
    {
        if ( !array_key_exists( "datasources", self::$data ) ) {
            return;
        } // end if not exist datasources

        $dataSources = self::$data['datasources'];

        foreach ( $dataSources as $ds ) {
            $dataSource = new DataSource(
                $ds['connection'],
                $ds['name'],
                $ds['type'],
                $ds['text'],
                $ds['file']
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

            Db::setDataSource( $ds["name"], $dataSource );
        } // end foreach datasources
    } // end function loadDataSources
} // end class Config
