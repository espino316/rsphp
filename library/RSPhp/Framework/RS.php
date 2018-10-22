<?php
/**
 * RS.php
 *
 * PHP Version 5
 *
 * RS File Doc Comment
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
use RSPhp\Framework\Controller;
use RSPhp\Framework\View;
use RSPhp\Framework\Str;
use RSPhp\Framework\File;
use RSPhp\Framework\Directory;
use RSPhp\Framework\Html;

/**
 * This class contains functions for RS framework behavior
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */
class RS
{

    static $sapi;
    static $baseUrl;
    static $url;
    static $method;
    static $version = '1.0';

    /**
     * Creates an instance of class RS
     *
     * @return RS
     */
    function __construct()
    {
    } // end function __construct

    /**
     * Helper function to print_r comma separated arguments
     */
    static function debug($args) {
        $a = func_get_args();
        print_r($a);
    } // end function debug

    /**
     * Prints a line to the output, either CLI or WEB
     *
     * @param String $text The text to print
     *
     * @return void
     */
    static function printLine($text)
    {
        if (php_sapi_name() === 'cli') {
            print_r($text);
            echo "\n";
        } else {
            print_r($text);
            echo '<br />';
        }// end if IS_CLI
    } // end function printLine

    /**
     * Sets a encryption keys pair
     *
     * @param String $filePath The file path to create the file
     *
     * @return void
     */
    static function setEncryptionKeys($filePath )
    {
        $tripleDesKey = Crypt::generateKey(24);
        $tripleDesVector = Crypt::generateKey(8);

        $template = "<?php
    define('TRIPLEDES_KEY', hex2bin('$tripleDesKey'));
    define('TRIPLEDES_IV', hex2bin('$tripleDesVector'));";

        File::write($filePath, $template);
    } // end function setEncryptionKeys

    /**
     * Initialize an app structure
     *
     * @param String $dir The directory to create the application
     *        "default" for the same dir of the app
     *
     * @return Bool
     */
    static function init($dir )
    {
        $appName = self::forceReadLine("What's your app's name?:", "Must provide an app name");
        $email = self::defaultReadLine("What's your email?:", "admin@localhost.com");

        //  Get the current working directory
        //  this script must be in vendor/espino316/rsphp/library/RSPhp/Framework
        $cwd = dirname(dirname(dirname(dirname(__FILE__))));

        //  Set the dir
        if ($dir == "default" ) {
            //  this dir is in the actual home,
            //  it's an ../.. from the rsphp location
            $home = dirname(dirname(dirname($cwd)));
        } else {
            $home = $dir;
        } // end if default

        //  Check if is directory
        if (!is_dir($home) ) {
            self::printLine("Not a directory.");
            return;
        } // end if not is dir

        //  Create the directory structure:
        $appPath = "$home/application";
        if (Directory::exists($appPath) ) {
            self::printLine("Directory 'application' already exists");
        } else {
            Directory::create($appPath);
            self::printLine("Directory 'application' created");
        } // end if exists $appPath

        //  Create the config directory
        $configPath = "$home/config";
        if (Directory::exists($configPath) ) {
            self::printLine("Directory 'config' already exists");
        } else {
            Directory::create($configPath);
            self::printLine("Directory 'config' created");
        } // end if exists $appPath

        //  Create the public directory
        $publicPath = "$home/public";
        if (Directory::exists($publicPath) ) {
            self::printLine("Directory 'public' already exists");
        } else {
            Directory::create($publicPath);
            self::printLine("Directory 'public' created");
        } // end if exists $appPath

        //  Create the tmp directory
        $logsPath = "$home/logs";
        if (Directory::exists($logsPath) ) {
            self::printLine("Directory 'logs' already exists");
        } else {
            Directory::create($logsPath);
            self::printLine("Directory 'logs' created");
        } // end if exists $logsPath

        //  Copy the rsphp_help file
        File::copy(
            "$cwd/rsphp_help",
            "$home/rsphp_help"
        );
        self::printLine("Help file created");

        //  Create the rsphp file
        File::write("$home/rsphp", "(vendor/espino316/rsphp/rsphp $* )" );
        File::write(
            "$home/rsphp.bat",
            "@echo off\nphp vendor/espino316/rsphp/rsphp %*"
        );
        self::printLine("Shell file created");
        self::printLine("Bat file created");

        //  Copy the .htaccess file
        File::copy(
            "$cwd/.htaccess",
            "$home/.htaccess"
        );
        self::printLine(".htaccess file created");

        //  Create the controllers directory
        if (Directory::exists("$appPath/Controllers") ) {
            self::printLine("Directory 'application/Controllers' already exists");
        } else {
            Directory::create("$appPath/Controllers");
            self::printLine("Directory 'application/Controllers' created");
        } // end if exists $appPath/Controllers

        //  Create the models directory
        if (Directory::exists("$appPath/Models") ) {
            self::printLine("Directory 'application/Models' already exists");
        } else {
            Directory::create("$appPath/Models");
            self::printLine("Directory 'application/Models' created");
        } // end if exists $appPath/Models

        //  Create the libraries directory
        if (Directory::exists("$appPath/Libraries") ) {
            self::printLine("Directory 'application/Libraries' already exists");
        } else {
            Directory::create("$appPath/Libraries");
            self::printLine("Directory 'application/Libraries' created");
        } // end if exists $appPath/Libraries

        //  Create the views directory
        if (Directory::exists("$appPath/Views") ) {
            self::printLine("Directory 'application/Views' already exists");
        } else {
            Directory::create("$appPath/Views");
            self::printLine("Directory 'application/Views' created");
        } // end if exists $appPath/Views
        
        //  Create the Data directory
        if (Directory::exists("$appPath/Data") ) {
            self::printLine("Directory 'application/Data' already exists");
        } else {
            Directory::create("$appPath/Data");
            self::printLine("Directory 'application/Data' created");
        } // end if exists $appPath/Data

        //  Create the Data/Schema directory
        if (Directory::exists("$appPath/Datai/Schema") ) {
            self::printLine("Directory 'application/Data/Schema' already exists");
        } else {
            Directory::create("$appPath/Data/Schema");
            self::printLine("Directory 'application/Data/Schema' created");
        } // end if exists $appPath/Data

        //  Copy the app.json file
        File::copy(
            "$cwd/config/app.json",
            "$configPath/app.json"
        );
        self::printLine("Config app file created");

        //  Encription keys
        $keysFilePath = "$configPath/tdeskeys.php";

        if (File::exists($keysFilePath) ) {
            self::printLine("Encryption keys file already exists");
        } else {
            self::setEncryptionKeys($keysFilePath);
            self::printLine("Encryption keys file created");
        } // end if then else keysFilePath exists

        //  Create the css directory
        if (Directory::exists("$publicPath/css") ) {
            self::printLine("Directory 'public/css' already exists");
        } else {
            Directory::create("$publicPath/css");
            self::printLine("Directory 'public/css' created");
        } // end if exists $publicPath/css

        //  Create the js directory
        if (Directory::exists("$publicPath/js") ) {
            self::printLine("Directory 'public/js' already exists");
        } else {
            Directory::create("$publicPath/js");
            self::printLine("Directory 'public/js' created");
        } // end if exists $publicPath/js

        //  Create the img directory
        if (Directory::exists("$publicPath/img") ) {
            self::printLine("Directory 'public/img' already exists");
        } else {
            Directory::create("$publicPath/img");
            self::printLine("Directory 'public/img' created");
        } // end if exists $publicPath/js

        //  Copy the index.php file
        File::copy(
            "$cwd/public/index.php",
            "$publicPath/index.php"
        );
        self::printLine("Index file created");

        //  Copy the public/.htaccess file
        File::copy(
            "$cwd/public/.htaccess",
            "$publicPath/.htaccess"
        );
        self::printLine("public/.htaccess file created");

        //  Correct composer.json
        $composerJson = "$home/composer.json";
        $json = File::read($composerJson);
        $json = json_decode($json, true);

        //  If no has autoload
        if (!isset($json["autoload"]) ) {
            $json["autoload"] = array(
                "psr-4" => array(
                    "Application\\" => "application"
                )
            );
        } // end if not has autoload

        // If autoload, but not psr-4
        if (!isset($json["autoload"]["psr-4"]) ) {
            $json["autoload"]["psr-4"] = array(
                "Application\\" => "application"
            );
        } // end if not psr-4

        // If psr-4, but no "Application"
        if (!isset($json["autoload"]["psr-4"]["Application\\"]) ) {
            $json["autoload"]["psr-4"]["Application\\"] = "application";
        } // end if psr-4 but no "Application"

        // If "Application" different than "application"
        if ($json["autoload"]["psr-4"]["Application\\"] != "application" ) {
            self::printLine(
                "Error: composer autoload-psr-4->Application ".
                " occupied by another library"
            );
            self::printLine(
                "The framework may not function correctly."
            );
            return;
        } // end if "Application different than "application"

        // Here we write the json
        $json = json_encode($json, JSON_PRETTY_PRINT);
        File::write($composerJson, $json);
        self::printLine("Composer file updated.");

        self::printLine("Creating default controller" );
        require_once("$home/public/index.php" );
        self::createController("Default", "Default controller" );

        self::printLine("Updating configuration");
        $appJson = json_decode(file_get_contents("$configPath/app.json"));
        $appJson->appName = $appName;
        $appJson->globals->EMAIL_ADMIN = $email;
        file_put_contents(
            "$configPath/app.json",
            json_encode($appJson, JSON_PRETTY_PRINT)
        );

        self::printLine("");
        self::printLine("Success!!  All done :)");
        self::printLine("");
        self::printLine("Yo can use ./rsphp cli from this folder now");

    } // end function createAppSite

    /**
     * List all data connections
     *
     * @return void
     */
    static function listConnections()
    {
        $fileApp = ROOT.DS.'config'.DS.'app.json';

        if (file_exists($fileApp) ) {
            $app = json_decode(file_get_contents($fileApp), true);

            if (!isset($app["dbConnections"]) ) {
                self::printLine("No connections.");
                return;
            } // end if no connections

            $connections = $app["dbConnections"];

            foreach ($connections as $conn ) {
                self::printLine("    - " . $conn["name"]);
                self::printLine(
                    "        - driver: " . $conn["driver"]
                );
                self::printLine(
                    "        - host name: " . $conn["hostName"]
                );
                self::printLine(
                    "        - database: " . $conn["databaseName"]
                );

                self::printLine("");
            } // end foreach connection
        } // end if file exists
    } // end function connections

    /**
     * List all routes
     *
     * @return void
     */
    static function listRoutes()
    {
        $fileApp = ROOT.DS.'config'.DS.'app.json';

        if (file_exists($fileApp) ) {
            $app = json_decode(file_get_contents($fileApp), true);

            if (!isset($app["routes"]) ) {
                self::printLine("No routes.");
                return;
            } // end if no connections

            $routes = $app["routes"];

            foreach ($routes as $route ) {
                $toPrint = "    ";
                $toPrint.= $route["method"];
                $toPrint.= ": ";
                $toPrint.= $route["url"];
                $toPrint.= " => ";
                $toPrint.= $route["newUrl"];

                self::printLine($toPrint);
                self::printLine("");
            } // end foreach connection
        } // end if file exists
    } // end function connections

    /**
     * List all data sources
     *
     * @return void
     */
    static function listDataSources()
    {
        $fileDataSources = ROOT.DS.'config'.DS.'datasources.json';

        if (file_exists($fileDataSources) ) {
            $dataSources = json_decode(file_get_contents($fileDataSources), true);
            if (!array_key_exists("datasources", $dataSources ) ) {
                throw new \Exception(
                    "datasources key do not exists in datasources.json"
                );
            } // end if array key "datasources" not exists

            $dataSources = $dataSources["datasources"];

            foreach ($dataSources as $index => $ds ) {
                self::printLine("    - " . $ds["name"]);
                self::printLine(
                    "        - connection: " . $ds["connection"]
                );
                self::printLine(
                    "        - type: " . $ds["type"]
                );

                if (isset($ds["file"]) ) {
                    self::printLine(
                        "        - file: " . $ds["file"]
                    );
                } // end if isset file
                self::printLine("");
            } // end foreach datasource
        } // end if file exists
    } // end function listDataSources

    /**
     * List all controllers in console
     *
     * @return void
     */
    static function listControllers()
    {
        $files = Directory::getFiles(APPPATH . DS . "Controllers");
        foreach ($files as $file ) {
            $theController = basename($file);
            $theController = Str::replace(".php", "", $theController);
            $theController = "    - " . $theController;
            self::printLine($theController);
        } // end foreach file
    } // end function listControllers

    /**
     * List all models in console
     *
     * @return void
     */
    static function listModels()
    {
        $files = Directory::getFiles(APPPATH . DS . "Models");
        foreach ($files as $file ) {
            $theModel = basename($file);
            $theModel = Str::replace(".php", "", $theModel);
            $theModel = "    - " . $theModel;
            self::printLine($theModel);
        } // end foreach file
    } // end function listModels

    /**
     * Adds a connection to the configuration
     *
     * @param String      $name         The connection's name
     * @param String      $driver       The type of the datasource
     * @param String      $hostName     The actual query text or file path
     * @param String      $databaseName Indicates if is a file (default false)
     * @param String      $userName     The user's name
     * @param String      $password     The user's password
     * @param String|null $port         The database engine port
     *
     * @return void
     */
    static function addConnection(
        $name,
        $driver,
        $hostName,
        $databaseName,
        $userName,
        $password,
        $port = null
    ) {
        //  First we test the connection
        $db = new Db(
            array(
                "driver" => $driver,
                "hostName" => $hostName,
                "databaseName" => $databaseName,
                "userName" => $userName,
                "password" => $password,
                "port" => $port
            ) // end array connection
        ); // end new Db

        //  Test connection
        $db->connect();
        $db->conn = null;

        $configFile = ROOT.DS.'config'.DS.'app.json';
        $indexToRemove = null;
        $removeIndex = false;

        if (!File::exists($configFile) ) {
            $json["dbConnections"] = array();
            $json = json_encode($json, JSON_PRETTY_PRINT);
            File::write($configFile, $json);
        } // end if not exists

        $connections = array();
        $appConfig = File::read($configFile);
        $appConfig = json_decode($appConfig, true);

        if (isset($appConfig["dbConnections"]) ) {
            $connections = $appConfig["dbConnections"];
        } // end if dbConnection exists

        foreach ($connections as $index => $conn ) {
            if ($conn["name"] == $name ) {
                self::printLine("Data connection " . $name . " already exists");
                self::printLine("Overriding...");
                $removeIndex = true;
                $indexToRemove = $index;
            } // end if conn name
        } // end foreach connection

        if ($removeIndex ) {
            array_splice($connections, $indexToRemove, 1);
        } // end if removeIndex

        $connection = array(
            "name" => $name,
            "driver" => $driver,
            "hostName" => $hostName,
            "databaseName" => $databaseName,
            "userName" => $userName,
            "password" => $password
        );

        if ($port ) {
            $connection["port"] = $port;
        } // end if port

        $connections[] = $connection;
        $appConfig["dbConnections"] = $connections;
        $json = json_encode($appConfig, JSON_PRETTY_PRINT);
        File::write($configFile, $json);

        return $connection;
    } // end function add connection

    /**
     * Adds a datasource to the configuration
     *
     * @param String  $connection The connection's name
     * @param String  $name       The name of the datasource
     * @param String  $type       The type of the datasource
     * @param String  $text       The actual query text or file path
     * @param boolean $isFile     Indicates if is a file (default false)
     *
     * @return void
     */
    static function addDataSource(
        $connection,
        $name,
        $type,
        $text,
        $isFile = false
    ) {
        $fileDataSources = ROOT.DS.'config'.DS.'datasources.json';
        $indexToRemove = null;
        $removeIndex = false;

        if (file_exists($fileDataSources) ) {
            $dataSources = json_decode(file_get_contents($fileDataSources), true);
            if (!array_key_exists("datasources", $dataSources ) ) {
                // remove the datasources, must contain "datasources" key
                $dataSources = null;
            } else {
                $dataSources = $dataSources["datasources"];
                foreach ($dataSources as $index => $ds ) {
                    if ($ds['name'] == $name ) {
                        self::printLine('Data source ' . $name . ' already exists');
                        self::printLine('Overriding...');
                        $removeIndex = true;
                        $indexToRemove = $index;
                    } // end if $name == $name
                } // end foreach datasource
            } // end if array key do not exists
        } // end if file exists

        if ($removeIndex ) {
            array_splice($dataSources, $indexToRemove, 1);
        } // end if removeIndex

        $dataSource['connection'] = $connection;
        $dataSource['name'] = $name;
        $dataSource['type'] = $type;

        if (!$isFile ) {
            $dataSource["text"] = $text;
        } else {
            $dataSource["file"] = $text;
        } // end if isFile

        $dataSources[] = $dataSource;
        $result = array(
            "datasources" => $dataSources
        );

        $json = json_encode($result, JSON_PRETTY_PRINT);
        file_put_contents($fileDataSources, $json);

        self::printLine('Datasource added');

        return $fileDataSources;
    } // end function addDataSource

    /**
     * Adds a route to the configuration
     *
     * @param String $method The http method
     * @param String $url    The original url
     * @param String $newUrl The new url
     *
     * @return Array
     */
    static function addRoute(
        $method,
        $url,
        $newUrl
    ) {
        $configFile = ROOT.DS.'config'.DS.'app.json';
        $indexToRemove = null;
        $removeIndex = false;

        if (!File::exists($configFile) ) {
            $json["dbConnections"] = array();
            $json = json_encode($json, JSON_PRETTY_PRINT);
            File::write($configFile, $json);
        } // end if not exists

        $routes = array();
        $appConfig = File::read($configFile);
        $appConfig = json_decode($appConfig, true);

        if (isset($appConfig["routes"]) ) {
            $routes = $appConfig["routes"];
        } // end if dbConnection exists

        foreach ($routes as $index => $route ) {
            if ($route["url"] == $url
                && $route["method"] == $method
            ) {
                self::printLine("Route " . $url . " already exists");
                self::printLine("Overriding...");
                $removeIndex = true;
                $indexToRemove = $index;
            } // end if conn name
        } // end foreach connection

        if ($removeIndex ) {
            array_splice($routes, $indexToRemove, 1);
        } // end if removeIndex

        $route = array (
            "method" => $method,
            "url" => $url,
            "newUrl" => $newUrl
        );

        $routes[] = $route;
        $appConfig["routes"] = $routes;
        $json = json_encode($appConfig, JSON_PRETTY_PRINT);
        File::write($configFile, $json);

        return $route;
    } // end function addRoute

    /**
     * Process standard input and returns
     * a command and it's parameters
     *
     * @param array $args The standard input
     *
     * @return array
     */
    static function processStdIn($args )
    {
        $command = array();
        $commandParams = array();
        $params = array();

        //  Loop the args, form the parameters and the command
        $cont = 0;
        $isParam = false;
        foreach ($args as $arg ) {
            if (Str::startsWith($arg, "--") ) {
                $isParam = true;
                $commandParams[] = $arg;
            } elseif ($cont > 0 ) {
                if ($isParam ) {
                    $tmp = $commandParams[count($commandParams)-1];
                    $tmp .= " $arg";
                    $commandParams[count($commandParams)-1] = $tmp;
                } else {
                    $command[] = $arg;
                } // end if then else isParam
            } // end if

            // Increment the counter
            $cont++;
        } // end foreach

        //  Set the command
        $command = implode(" ", $command);

        //  Loop the command parameters
        foreach ($commandParams as $commandParam ) {
            $paramLine = Str::replace("--", "", $commandParam);
            $parts = explode("=", $paramLine, 2);
            $value = "";
            if (isset($parts[1]) ) {
                $value = $parts[1];
            } // end if parts 1
            $params[ $parts[0] ] = $value;
        } // end foreach

        return array(
                "command" => $command,
                "parameters" => $params
            );

    } // end function processStdIn

    /**
     * Start up the framework
     *
     * @return void
     */
    static function startUp()
    {

        $method = "CLI";
        $sapi = php_sapi_name();
        $url = "";
        $baseUrl = "";
        if ($sapi != 'cli' ) {
            //	Gets the protocol
            if (isset($_SERVER['HTTPS']) ) {
                $protocol = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
            } else if (isset($_SERVER['SERVER_PROTOCOL']) ) {
                $protocol = 'http://';
            }

            //	This is the path to index
            $indexPath = '/public/index.php';

            $scriptUrl = $_SERVER['PHP_SELF'];

            //	Base url, without "public", use for controller references
            $baseUrl = $protocol . $_SERVER['HTTP_HOST']. $scriptUrl;
            $baseUrl = str_replace($indexPath, '', $baseUrl);

            if (strpos($baseUrl, '.php') !== false ) {
                $baseUrl = explode('/', $baseUrl);
                array_pop($baseUrl);
                $baseUrl = implode('/', $baseUrl);
            }

            define('BASE_URL', $baseUrl); // directory or domain web accesible

            //	This is the directory in wich the app is hosted, within the server
            if ($scriptUrl != $indexPath ) {
                $directory = str_replace($indexPath, '', $_SERVER['PHP_SELF']);
                $url = str_replace($directory . '/', '', $_SERVER['REQUEST_URI']);
            } else {
                $url = $_SERVER['REQUEST_URI'];
                $url = substr($url, 1);
            }
            $method = $_SERVER['REQUEST_METHOD'];
            $headers = getallheaders();
        }

        $inputString = file_get_contents("php://input");
        $inputData = array();
        parse_str($inputString, $inputData);

        self::$sapi = $sapi;
        self::$baseUrl = $baseUrl;
        self::$url = $url;
        self::$method = $method;

        Config::load();
        Input::load();
        self::removeMagicQuotes();
        self::unregisterGlobals();
        self::setReporting();
        if (! IS_CLI ) {
            self::doRouting();
        } // end if not cli
    } // end function startUp

    /**
     * Display errors only in development
     *
     * @return void
     */
    static function setReporting()
    {

        if (App::get('DEVELOPMENT_ENVIRONMENT') == true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', ROOT.DS.'tmp'.DS.'logs'.DS.'error.log');
        }
    } // end function setReporting

    /**
     * Removes the magic quotes
     *
     * @param String $value The value to strip slashes
     *
     * @return String
     */
    static function stripSlashesDeep($value)
    {
        if (is_array($value) ) {
            array_map('stripSlashesDeep', $value);
        } else {
            stripslashes($value);
        }
        return $value;
    } // end function stripSlashedDeep

    /**
     * Removes the magic quotes
     *
     * @return void
     */
    static function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc() ) {
            $_GET    = stripSlashesDeep($_GET);
            $_POST   = stripSlashesDeep($_POST);
            $_COOKIE = stripSlashesDeep($_COOKIE);
        } // end if
    } // end function removeMagicQuotes

    /**
     * Unregister the globals
     *
     * @return void
     */
    static function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array(
            '_SESSION',
            '_POST',
            '_GET',
            '_COOKIE',
            '_REQUEST',
            '_SERVER',
            '_ENV',
            '_FILES'
            );

            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    } // end if
                } // end foreach
            } //end foreach
        } // end if
    } // end function unregisterGlobals

    /**
     * This function does the routing process
     *
     * @return void
     */
    static function doRouting()
    {

        $url = self::$url;
        $method = self::$method;

        $routes = App::get('routes');

        $controller = '';
        $model = '';
        $action = '';
        $queryString = array();

        if ($method == 'GET' ) {
            $urlParts = explode('?', $url);
            if (count($urlParts) > 1 ) {
                $url = $urlParts[0];
                Input::setQueryString($urlParts[1]);
            }
        } // end if method is get

        /* Here we search routes */


        //Loop through routes
        $defaultController = 'Default';
        if ($routes ) {
            foreach ($routes as $route) {
                if ($route->uri == "") {
                    $defaultController = ucwords(strtolower($route->newUri));
                } // end if default

                $route->match($url);

                if ($route->method == "*" || strtoupper($route->method) == strtoupper($method)) {
                    $url = str_replace($route->uri, $route->newUri, $url);
                } // end if method
            } // end foreach
        } // end if route

        if ($url == "" || $url == "/") {
            $cont = 0;
        } else {
            $urlArray = explode("/", $url);
            $cont = sizeof($urlArray);
        } // end if then else

        if (!empty($urlArray) ) {
            Uri::setSegments($urlArray);
        } // end if not empty urlArray

        switch ($cont) {
        case 0:
            $controller = $defaultController; // go to default controller
            $action = 'index';
            break;
        case 1:
            $controller = $urlArray[0];
            $action = 'index';
            break;
        case 2:
            $controller = $urlArray[0];
            if ($urlArray[1] == '') {
                $action = 'index';
            } else {
                $action = $urlArray[1];
            }
            break;
        default:
            $controller = $urlArray[0];
            array_shift($urlArray);
            $action = $urlArray[0];
            array_shift($urlArray);
            $queryString = $urlArray;
            break;
        }

        $controllerName = $controller;
        $controller = ucwords($controllerName);
        $controller .= 'Controller';
        $defaultController .= 'Controller';

        if ((int)method_exists(
            "\\Application\\Controllers\\".$controller,
            $action
        )) {
            $dispatch = "Application\\Controllers\\$controller";
            $dispatch = new $dispatch();
            call_user_func_array(array($dispatch,$action), $queryString);
        } else {
            $action = $controllerName;
            if ((int)method_exists(
                "\\Application\\Controllers\\".$defaultController,
                $action
            )) {
                $dispatch = "Application\\Controllers\\$defaultController";
                $dispatch = new $dispatch();
                call_user_func_array(
                    array(
                        $dispatch,
                        $action
                    ),
                    $queryString
                );
            } else if (file_exists(
                ROOT.DS.$action
            )) {
                self::serveFile(ROOT.DS.$action);
            } else {

                try {
                    $dispatch = "Application\\Controllers\\$controller";
                    $dispatch = new $dispatch();
                    call_user_func_array(array($dispatch,'index'), $queryString);
                } catch (Exception $ex) {
                    throw new Exception(
                        $ex->getMessage() ."\n".
                        'Controller or action do not exist: ' .
                        $controller . ' / ' . $action . ' nor file ' .
                        ROOT.DS.$action
                    );
                } // end try catch
            } // end if then else file method or file exists
        } // end if then else method exists
    } // end callHook

    /**
     * Write a file to the response
     *
     * @param String $file The file path
     *
     * @return void
     */
    static function serveFile($file )
    {
        ob_end_clean();

        if (Str::endsWith($file, '.xml') ) {
            header('Content-type: application/xml');
        } // end if ends with xml

        if (Str::endsWith($file, '.json') ) {
            header('Content-type: application/json');
        } // end if ends with xml

        echo file_get_contents($file);
    } // end serveFile


    /**
     * Creates a new controller
     *
     * @param String $name        The controller's name
     * @param String $description The controller's description
     *
     * @return void
     */
    static function createController($name, $description )
    {

        $ucName = ucwords($name);
        $controllerName = $ucName.'Controller';
        $filename = $controllerName.".php";
        $filename = ROOT.DS.'application'.DS.'controllers'.DS.$filename;

        $path = dirname(__FILE__ );
        $path = dirname($path );
        $path = dirname($path );
        $path = dirname($path );
        $template = File::read($path.DS."templates".DS."Controller_template" );
        $template  = Str::replace(
            array(
                "@controllerName" => $controllerName,
                "@description" => $description,
                "@name" => $ucName,
            ), // end array data
            $template
        ); // end View::

        file_put_contents($filename, $template);
        self::dumpAutoload();
        return $filename;
    } // end function createController

    /**
     * Composer dump autoload
     *
     * @return void
     */
    static function dumpAutoload()
    {
        $output = shell_exec("composer dump-autoload");
        self::printLine($output);
    } // end private function dumpAutoload

    /**
     * Remove all files and directories in /application
     *
     * @return void
     */
    static function cleanApp()
    {
        $dirs[] = ROOT.DS.'application'.DS.'Controllers';
        $dirs[] = ROOT.DS.'application'.DS.'Data';
        $dirs[] = ROOT.DS.'application'.DS.'Libraries';
        $dirs[] = ROOT.DS.'application'.DS.'Models';
        $dirs[] = ROOT.DS.'application'.DS.'Views';
        $dirs[] = ROOT.DS.'public'.DS.'css';
        $dirs[] = ROOT.DS.'public'.DS.'files';
        $dirs[] = ROOT.DS.'public'.DS.'images';
        $dirs[] = ROOT.DS.'public'.DS.'img';
        $dirs[] = ROOT.DS.'public'.DS.'js';
        $dirs[] = ROOT.DS.'application';
        $dirs[] = ROOT.DS.'public';
        $dirs[] = ROOT.DS.'config';
        $dirs[] = ROOT.DS.'logs';
        $dirs[] = ROOT.DS.'rsphp';
        $dirs[] = ROOT.DS.'rsphp.bat';
        $dirs[] = ROOT.DS.'rsphp_help';

        foreach ($dirs as $dir ) {
             RS::printLine($dir);

            if (Directory::exists($dir) ) {
                RS::printLine("Directory exists");
                $files = scandir($dir);
                foreach ($files as $file ) {
                    if ($file == '.' || $file == '..' ) {
                        continue;
                    }

                     $file = $dir.DS.$file;
                    if (is_dir($file) ) {
                        self::printLine('remove dir '.$file);
                        Directory::delete($file, true);
                    } else {
                        self::printLine('remove file '.$file);
                        File::delete($file);
                    } // end if is dir
                } // end foreach $file
                Directory::delete($dir, true );
            } else {
                if (File::exists ($dir ) ) {
                    File::delete($dir );
                } // end if file exists
            } // end if dir exists
        } // end foreach dir
    } // end function cleanApp

    /**
     * Creates a new view for insert record
     *
     * @param String $tableName The table's name for record creation
     *
     * @return void
     */
    static function createViewNewRecord($tableName )
    {

        self::printLine('Creating view for new record, table ' . $tableName);
        if (!isset(DB::$connections) ) {
            throw new Exception("No connections are set up", 1);
        } // end if isset DBConn
        $db = new Db();

        $sql = "SELECT
  		column_name,
  		data_type,
  		ordinal_position,
  		is_nullable,
  		character_maximum_length
  		FROM
  			information_schema.columns
  		WHERE
  			table_catalog = :databaseName
  			AND
  				table_name = :tableName";

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $db->dbConn->databaseName;
        $result = $db->query($sql, $queryParams);

        foreach ($result as $row) {
            $row2 = $row;
            $sql = "SELECT  t.table_name, kcu.column_name AS value_field,
        (SELECT
                column_name
            FROM
               information_schema.columns
            WHERE table_name = t.table_name
                AND column_name <> kcu.column_name
                AND data_type like '%char%'
            ORDER BY ordinal_position
            LIMIT 1
        ) AS display_field
  		FROM    INFORMATION_SCHEMA.TABLES t
  		         LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
  		                 ON tc.table_catalog = t.table_catalog
  		                 AND tc.table_schema = t.table_schema
  		                 AND tc.table_name = t.table_name
  		                 AND tc.constraint_type = 'PRIMARY KEY'
  		         LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
  		                 ON kcu.table_catalog = tc.table_catalog
  		                 AND kcu.table_schema = tc.table_schema
  		                 AND kcu.table_name = tc.table_name
  		                 AND kcu.constraint_name = tc.constraint_name
  		WHERE   t.table_catalog = :databaseName
  	                and t.table_name IS NOT NULL
  	                and kcu.column_name = :columnName
  		ORDER BY t.table_catalog,
  		         t.table_schema,
  		         t.table_name,
  		         kcu.constraint_name,
  		         kcu.ordinal_position";

            $relParams['databaseName'] = $db->dbConn->databaseName;
            $relParams['columnName'] = $row['column_name'];
            $namingRelations = $db->query($sql, $relParams);

            if ($namingRelations ) {
                $firstRow = $namingRelations[0];
                $row2['related_to'] = $firstRow['table_name'];
                $row2['value_field'] = $firstRow['value_field'];
                $row2['display_field'] = $firstRow['display_field'];

                $text = "SELECT value_field, display_field FROM table_name";
                $text = Str::stringReplace($firstRow, $text);

                $type = 'SQLQUERY';
                $connection = 'default';
                $name = 'ds'.$firstRow['table_name'].'ComboBox';

                self::printLine('Before datasource');
                addDataSource($connection, $name, $type, $text);

            } else {
                $row2['related_to'] = '';
                $row2['value_field'] = '';
                $row2['display_field'] = '';
            }    // end if namingRelations

            $result2[] = $row2;
        } // end foreach

        $html = '<table>'.CRLF;
        foreach ($result2 as $row ) {
            if ($row['related_to'] ) {
                self::printLine('related_to');
                $select = '<select id="@name" data-source="@dataSource"' .
                    ' data-value-field="@valueField" data-display-field="' .
                    '@displayField"></select>';
                $keys
                    = array(
                        '@name',
                        '@dataSource',
                        '@valueField',
                        '@displayField'
                    );

                $values
                    = array(
                        $row['column_name'],
                        'ds'.$row['related_to'].'ComboBox',
                        $row['value_field'],
                        $row['display_field']
                    );
                $select = str_replace($keys, $values, $select);
                $html .= TAB.'<tr><td>'.$row['column_name'].'</td><td>'.
                    $select.'</td></tr>'.CRLF;
            } else {
                $html .= TAB.'<tr><td>'.$row['column_name'].'</td><td>'.
                    Html::formInputText($row['column_name'], '').
                    '</td></tr>'.CRLF;
            }
        }

        $html.='</table>';
        $file = ROOT.DS.'application'.DS.'views'.DS.'new'.$tableName.'.php';

        $html = View::dataBind($html);
        if (file_exists($file) ) {
            unlink($file);
        } // end if file exists
        file_put_contents($file, $html);
    } // end function createViewNewRecord

    /**
     * Generates a controller with the restful api for each table in each method
     *  Command: ./rsphp restapi create --controller="controllerName" --conn="connName" --tables-exception="tables,exception"
     *
     * @param $controllerName The controller name
     * @param $connName The connection name
     * @param $tablesException The tables exceptions
     *
     * @return null
     */
    public static function generateRestfulApi($controllerName, $connName = null, $tablesExceptions = null) {
        try {
            if (! Db::hasDbConnections() ) {
                throw new Exception("No connections are set. Try adding a connection first." );
            } // end if no connections

            //  Get the class definition
            $path = dirname(__FILE__ );
            $path = dirname($path );
            $path = dirname($path );
            $path = dirname($path );
            $classDefinition = File::read($path.DS."templates".DS."RestApiModelController_template" );

            $db = new Db($connName);
            $tablesResultSet = $db->getTables();

            $tables = [];
            $tablesExceptions = explode(",", $tablesExceptions);
            foreach ($tablesResultSet as $table) {
                if ($tablesExceptions) {
                    //  Formamos el array

                    //  End foreach table exception
                    foreach ($tablesExceptions as $tableEx) {
                        if ($table->table_name != $tableEx) {
                            $tables[] = $table;
                        } // end if table name is not the same
                    } // end foreach table exception
                } else {
                    $tables[] = $table;
                } // end if tables exceptions
            } // end foreach table

            //  Then we perform the methods for: POST, PUT, DELETE, GET
            //  The we look for relationships and add the methods to get the related tables

            $methods = '';

            foreach ($tables as $table) {

                //  Generate methods
                //  Replace @placeholders
                $tableMethods = "\n       /**** Here begins $table->table_name Rest API ****/\n";

                //      Get
                $tableMethods .= self::generateRestfulApiGetMethods($db, $table->table_name);

                //      Post
                $tableMethods .= self::generateRestfulApiPostMethods($db, $table->table_name);

                //      Put
                $tableMethods .= self::generateRestfulApiPutMethods($db, $table->table_name);

                //      Delete
                $tableMethods .= self::generateRestfulApiDeleteMethods($db, $table->table_name);

                //      End
                $tableMethods .= "\n       /**** Here ends $table->table_name Rest API ****/\n";

                $methods .= $tableMethods;
            } // end for each table


            $classDefinition = Str::replace(
                array(
                    '@methods' => $methods,
                    "\r\n" => "\n",
                    '@endPoint' => Str::toLower($controllerName),
                    '@controllerName' => "$controllerName"."Controller",
                    '@description' => "$controllerName Rest Api"
                ),
                $classDefinition
            ); // end string replace

            $filename = ROOT.DS.'application'.DS.'controllers'.DS.$controllerName. "Controller.php";

            if (File::exists($filename)) {
                File::delete($filename);
            } // end if file exists

            File::write($filename, $classDefinition);

        } catch (Exception $ex) {
            self::printLine($ex->getMessage());
        } // end try catch
    } // end function generateRestfulApi

    /**
     * Generates the code for get method for each table
     *
     * @param $db The db connection to perform
     * @param $tableName The table name for wich we must generate the method
     */
    private static function generateRestfulApiGetMethods($db, $tableName) {

        //  This is where we're gonna stored the results
        $result = "";

        //  This is the options template
        $options = '
        //  Options to enable previous check from JS clients
        $this->options(
            "@tableName",
            function(@params) {
                Output::setStatusCode(200);
                $this->setCORSHeaders();
            } // end anonymous function
        );
';
        //  This is the get template
        $get = '
        //  GET all records
        $this->get(
            "@tableName",
            function (@params) {
                $this->jsonResponse(
                    $this->db->get("@tableName")
                ); // end jsonResponse
            } // end anonoymous get
        ); // end get
';
        //  Here we replace the table name and set them to options and get
        $result .= Str::replace(array('@tableName' => $tableName, '@params' => ''), $options);
        $result .= Str::replace(array('@tableName' => $tableName, '@params' => ''), $get);

        //  Here we're gonna get the primary key
        $pks = $db->getPrimaryKeys($tableName);
        $paramsUrl = '';
        $paramsFunction = '';
        $paramsWhere = array();

        foreach ($pks as $pk) {
            $paramsUrl .= '/:' . $pk;
            $paramsFunction = ($paramsFunction) ? ', $'.$pk : '$'.$pk;
            $paramsWhere[] = "('$pk', $$pk)";
        } // end for each $pks

        $paramsWhere = implode('->andWhere', $paramsWhere);

        $options = '
        //  OPTIONS by keys
        $this->options(
            "@tableName@paramsUrl",
            function(@paramsFunction) {
                Output::setStatusCode(200);
                $this->setCORSHeaders();
            } // end anonymous function
        );
';
        //  This is the get template
        $get = '
        //  GET by keys
        $this->get(
            "@tableName@paramsUrl",
            function (@paramsFunction) {
                $this->jsonResponse(
                    $this->db->
                        get("@tableName")->
                            where@paramsWhere
                ); // end jsonResponse
            } // end anonoymous get
        ); // end get
';

        //  Here we replace the table name and set them to options and get
        $result .= Str::replace(
            array(
                '@tableName' => $tableName,
                '@paramsUrl' => $paramsUrl,
                '@paramsFunction' => $paramsFunction,
                '@paramsWhere' => $paramsWhere
            ),
            $options
        );

        $result .= Str::replace(
            array(
                '@tableName' => $tableName,
                '@paramsUrl' => $paramsUrl,
                '@paramsFunction' => $paramsFunction,
                '@paramsWhere' => $paramsWhere
            ),
            $get
        );

        return $result;
    } // end function generateRestulApiGetMethod

    /**
     * Generates the code for post method for each table
     *
     * @param $db The db connection to perform
     * @param $tableName The table name for wich we must generate the method
     */
    private static function generateRestfulApiPostMethods($db, $tableName) {

        //  This is where we're gonna stored the results
        $result = '';

        //  This is the post template
        $post = '
        //  POST
        $this->post(
            "@url",
            function () {
                $this->db->insert(
                    "@url",
                    Input::get()
                );

                $@urlId =
                    $this->db->
                        select("@paramsSelect")->
                        from("@url")->
                        where(Input::get())->
                        getScalar();

                //  Created and location:
                Output::setStatusCode(201);
                Output::setHeader("Location", BASE_URL."/".$this->endPoint."@url@paramsUrl");
                $this->setCORSHeaders();
            } // end anonymous post
        ); // end get verb route
';

        //  Here we're gonna get the primary key
        $pks = $db->getPrimaryKeys($tableName);
        $paramsUrl = '';
        $paramsSelect = '';

        foreach ($pks as $pk) {
            $paramsUrl .= '/$' . $pk;
            $paramsSelect = ($paramsSelect) ? ','.$pk : $pk;
        } // end for each $pks

        //  Here we replace the table name and set them to the post
        $result .=
            Str::replace(
                array(
                    '@url' => $tableName,
                    '@paramsUrl' => $paramsUrl,
                    '@paramsSelect' => $paramsSelect
                ),
                $post
            ); // end replace

        return $result;
    } // end function generateRestulApiGetMethod

    /**
     * Generates the code for post method for each table
     *
     * @param $db The db connection to perform
     * @param $tableName The table name for wich we must generate the method
     */
    private static function generateRestfulApiPutMethods($db, $tableName) {

        //  This is where we're gonna stored the results
        $result = '';

        //  This is the post template
        $put = '
        // PUT
        $this->put(
            "@tableName@paramsUrl",
            function (@paramsFunction) {
                $this->db->update("@tableName", Input::get(), array(@paramsArray));

                //  Created and location:
                Output::setStatusCode(201);
                Output::setHeader("Location", BASE_URL."/".$this->endPoint."@tableName@paramsVars");
                $this->setCORSHeaders();
            } // end anonymous function
        ); // end put
';

        //  Here we're gonna get the primary key
        $pks = $db->getPrimaryKeys($tableName);
        $paramsUrl = '';
        $paramsFunction = '';
        $paramsArray = '';
        $paramsVars = '';

        foreach ($pks as $pk) {
            $paramsUrl .= '/:' . $pk;
            $paramsVars .= '/' . $pk;
            $paramsFunction = ($paramsFunction) ? ', $'.$pk : '$'.$pk;
            $colName = $pk;
            $arrayItem = "'$colName' => $$colName";
            $paramsArray = ($paramsArray) ? ",\n". $arrayItem : $arrayItem;
        } // end for each $pks

        //  Here we replace the table name and set them to the put
        $result .=
            Str::replace(
                array(
                    '@tableName' => $tableName,
                    '@paramsUrl' => $paramsUrl,
                    '@paramsVars' => $paramsVars,
                    '@paramsFunction' => $paramsFunction,
                    '@paramsArray' => $paramsArray
                ),
                $put
            ); // end replace

        return $result;
    } // end function generateRestulApiGetMethod

    /**
     * Generates the code for get method for each table
     *
     * @param $db The db connection to perform
     * @param $tableName The table name for wich we must generate the method
     */
    private static function generateRestfulApiDeleteMethods($db, $tableName) {

        //  This is where we're gonna stored the results
        $result = '';

        //  This is the get template
        $delete = '
        //  DELETE
        $this->delete(
            "@tableName@paramsUrl",
            function (@paramsFunction) {
                $this->db->
                    delete("@tableName")->
                        where@paramsWhere;
                //  No Content
                Output::setStatusCode(204);
                $this->setCORSHeaders();
            } // end anonoymous get
        ); // end get
';

        //  Here we're gonna get the primary key
        $pks = $db->getPrimaryKeys($tableName);
        $paramsUrl = '';
        $paramsFunction = '';
        $paramsWhere = array();

        foreach ($pks as $pk) {
            $paramsUrl .= '/:' . $pk;
            $paramsFunction = ($paramsFunction) ? ', $'.$pk : '$'.$pk;
            $paramsWhere[] = "('$pk', $$pk)";
        } // end for each $pks

        $paramsWhere = implode('->andWhere', $paramsWhere);

        //  Here we replace the table name and set them to options and get
        $result .= Str::replace(
            array(
                '@tableName' => $tableName,
                '@paramsUrl' => $paramsUrl,
                '@paramsFunction' => $paramsFunction,
                '@paramsWhere' => $paramsWhere
            ),
            $delete
        );

        return $result;
    } // end function generateRestulApiGetMethod

    /**
     * Create model
     *
     * @param $tableName The table name
     * @param $db The db object
     *
     * @return null
     */
    private static function createModelClass($tableName, $db)
    {
        $path = dirname(__FILE__ );
        $path = dirname($path );
        $path = dirname($path );
        $path = dirname($path );
        $classDefinition = File::read($path.DS."templates".DS."Model_template" );

        //	Get the id:
        $id = $db->getIdentityColumn($tableName);

        //  Here we're gonna get the primary key
        $pks = $db->getPrimaryKeys($tableName);

        $paramsWhere = array();
        $arrayWhere = '';
        $arrayItem = '';
        $constructorParams = '';

        foreach ($pks as $pk) {
            $arrayItem = "'$pk' => "."$"."this->$pk";
            $paramsWhere[] = "('$pk', $$pk)";
            $arrayWhere .= ($arrayWhere) ? ",\n". $arrayItem : $arrayItem;
            $constructorParams = ($constructorParams) ? ", $$pk" : "$$pk";
        } // end for each $pks

        $arrayWhere = "array($arrayWhere)";

        if (count($paramsWhere)) {
            $paramsWhere = "\n\t\t\twhere".implode('->andWhere', $paramsWhere)."->\n\t\t\t";
        } else {
            $paramsWhere = '';
        } // end if then else are paramsWhere

        //	Public properties
        $publicProperties = $db->getPublicProperties($tableName);

        //  Columns
        $columns = $db->getColumns($tableName);

        //	Load properties
        $loadProperties = "";
        $colNameStr = 'COLUMN_NAME';
        if (!isset($columns[0][$colNameStr]) ) {
            $colNameStr = 'column_name';
        }

        foreach ($columns as $row ) {
             $columnName = $row[$colNameStr];
             $loadProperties .=
                 ($loadProperties) ?
                    "\t\t$"."this->$columnName = $".
                    "result['$columnName'];\n" :
                    "$"."this->$columnName = $"."result['$columnName'];\n";
        }

        //	Undefined properties
        $undefinedProperties = "";
        $colNameStr = 'COLUMN_NAME';
        if (!isset($columns[0][$colNameStr])) {
            $colNameStr = 'column_name';
        }

        foreach ($columns as $row) {
            $columnName = $row[$colNameStr];
            $undefinedProperties .=
                ($undefinedProperties) ?
                    "\t\t$"."this->$columnName = Undefined::instance();\n" :
                    "$"."this->$columnName = Undefined::instance();\n";
        }

        //	Save properties
        $saveProperties = "";
        foreach ($columns as $row ) {
             $columnName = $row[$colNameStr];
             $saveProperties .=
                 ($saveProperties) ?
                     "\t\t\t'$columnName' => $"."this->$columnName,\n" :
                     "'$columnName' => $"."this->$columnName,\n";
        }

        $setSerialFieldTemplate = '
        if ( $this->@id === Undefined::instance() ) {
            $this->@id =
                parent::$db->from($this->getTableName())->
                @paramsWhere->
                max("@id");
        }';

        $setSerialField = $setSerialFieldTemplate;
        if ($id) {
            $setSerialField = Str::replace(
                array(
                    '@id' => $id,
                    '@paramsWhere' => $paramsWhere
                ),
                $setSerialFieldTemplate
            );
        } else {
            $setSerialField = '';
        } // end if id

        $text = $classDefinition;
        $text = str_replace("@tableName", ucfirst($tableName), $text);
        $text = str_replace("@publicProperties", $publicProperties, $text);
        $text = str_replace("@loadProperties", $loadProperties, $text);
        $text = str_replace("@undefinedProperties", $undefinedProperties, $text);
        $text = str_replace("@saveProperties", $saveProperties, $text);
        $text = str_replace("@constructorParams", $constructorParams, $text);
        $text = str_replace("@arrayWhere", $arrayWhere, $text);
        $text = str_replace("@paramsWhere", $paramsWhere, $text);
        $text = str_replace("@setSerialField", $setSerialField, $text);
        $text = str_replace("\r", "", $text);

        $filename = ROOT.DS.'application'.DS.'models'.DS.ucfirst($tableName) . "Model.php";

        if (file_exists($filename ) ) {
            unlink($filename);
        } // end if file exists

        file_put_contents($filename, $text);
        self::printLine("Model for table $tableName created in $filename.");
        self::printLine("");
    } // end function crete model class

    /**
     * Creates a model in the application
     *
     * @param String $tableName The table that the model will, well, model.
     *
     * @return void
     */
    static function createModel($tableName = null)
    {
        try {

            if (! Db::hasDbConnections() ) {
                throw new Exception("No connections are set. Try adding a connection first." );
            } // end if no connections

            $db = new Db();
            if (!$tableName) {
                $tables = $db->getTables();
                foreach ($tables as $table) {
                    self::createModel($table->table_name, $db);
                } // end for each table

                //  Inform
                self::printLine("Done creating models.");
                //  Exit
                return;
            } // end if not table name

            //  Create the model class
            self::createModelClass($tableName, $db);

        } catch(Exception $ex ) {
            self::printLine($ex->getMessage());
        } // end try catch
    } // end function createModel

    /**
     * Loads the properties from the table
     *
     * @return String
     */
    private static function _getLoadProperties()
    {
        $sql = 'SELECT \'$this->\' || column_name || \'' .
           ' = $result[\'\'\' || column_name || \'\'\'];\' AS property
  FROM information_schema.columns
  WHERE table_schema = \'public\'
    AND table_name   = :tableName';

        $queryParams['tableName'] = $tableName;

        $result = $db->query($sql, $queryParams);

        foreach ($result as $row ) {
            $loadProperties .= "\t\t" . $row['property'] . "\n";
        }

        return $loadProperties;
    } // end function _getLoadProperties

    /**
     * Handles and exception
     *
     * @param Exception $ex The exception to handle
     *
     * @return void
     */
    static function handleException($ex )
    {
        //  Clean output
        //ob_end_clean();

        //  Get app name
        if (!App::get('appName')) {
            $data['$appName'] = 'RS Php';
        } else {
            $data['$appName'] = App::get('appName');
        } // end if then else appName

        //  Get Error
        $data['$errorMessage'] = $ex->getMessage();

        //  Show Error
        if (View::exists("ErrorPage")) {
            View::load('ErrorPage', $data);
        } else {
            echo "No exist!"; return;
            $template = '<html>
                <head><title>Error Page</title>
                <body><div style="border: 1px solid red; color: red;">@errorMessage</div>
                <a href="@baseUrl">Home</a>
                </body>
            </html>';
            echo Str::replace(
                array(
                    "@errorMessage" => $ex->getMessage(),
                    "@baseUrl" => BASE_URL
                ),
                $template
            ); // end template string replace
        } // end if ErrorPage exists
    } // end function handleExeption

    /**
     * Creates a view
     */
    static function createView($viewName, $viewType ) {
        $viewsFolder = ROOT.DS."application".DS."Views";

        if (Str::contains($viewName, "/" ) ) {
            $values = explode("/", $viewName );
            $max = count($values ) - 2;
            $cont = 0;
            while ($cont <= $max ) {
                $dir = $viewsFolder.DS.$values[$cont];
                if (! Directory::exists($dir ) ) {
                    Directory::create($dir );
                } // end if Directory not exists
                $cont++;
            } // end while cont

            $viewName = Str::replace("/", DS, $viewName );

        } // end if contains "/"

        $viewName = $viewsFolder.DS.$viewName;
        if (! Str::endsWith($viewName, ".html" ) ) {
            $viewName .= ".html";
        } // end if contains ".html"

        if ($viewType == "content" ) {
            File::write(
                $viewName,
                ""
            ); // end File::write
        } // end if content

        if ($viewType == "page" ) {
            $templatesPath = dirname(dirname(dirname(dirname(__FILE__ ) ) ) );
            $templatesPath .= DS."templates";
            File::write(
                $viewName,
                File::read($templatesPath . "/page.html" )
            ); // end File::write
        } // end if page

        self::printLine("View $viewName created." );
    } // end function createView

    /**
     * Updates the application schema
     */
    public static function schemaUpdate()
    {
        $schema = new DbSchema;
        $schemaUpdatesHistory = array();
        $schemaUpdates = array();
        $timestamps = array();
        $controlFilePath
            = getenv("HOME").DS.
            Str::replace(" ", "_", Config::get("appName"));
        $controlFile = $controlFilePath.DS."schema-update.json";

        //  If exists the control file, load the history
        if (File::exists($controlFile)) {
            $schemaUpdatesHistory = json_decode(File::read($controlFile), true);
        } else {
            if (!Directory::exists($controlFilePath)) {
                Directory::create($controlFilePath);
            } // end if not exists control file directory
        } // end if file exists

        //  Get the files in the schema update folder
        $files = Directory::getFiles(ROOT.DS."application".DS."Data".DS."Schema");

        //  Loop the files
        foreach ($files as $file) {

            if (Str::endsWith($file, ".yaml")) {
                //  Get the data from the file format
                $tmp = Str::replace(ROOT.DS."application".DS."Data".DS."Schema".DS, "", $file);
                $data = explode("_", Str::replace(".yaml", "", $tmp));

                //  Add the timestamp to the array
                $timestamps[] = $data[2];

                //  Add the schema update to the array
                $schemaUpdates[] = array(
                    "connectionName" => $data[0],
                    "name" => $data[1],
                    "timestamp" => $data[2],
                    "file" => $file
                ); // end schemaUpdates
            } // end if ends with
        } // end foreach file

        //  Sort the timestamps by date
        sort($timestamps);

        //  Loop the sorted tiemstamps.
        //      If not in history, execute
        foreach ($timestamps as $timestamp) {
            //  Look for the file, if alreay parsed
            $row = Db::resultSetFilter($schemaUpdatesHistory, "timestamp", $timestamp);

            //  If no row, not parsed, then parse and control
            if (!$row) {
                //  Not executed yet
                //  Get the record
                $schemaUpdate = Db::resultSetFilter($schemaUpdates, "timestamp", $timestamp)[0];

                //  Execute it
                $schema->parseYaml($schemaUpdate["file"]);

                //  Add to history (already executed)
                $schemaUpdatesHistory[] = $schemaUpdate;
            } // end if not row
        } // end for each schema update

        //  Save the records
        File::write($controlFile, json_encode($schemaUpdatesHistory));

        RS::printLine("Schema updated successfully.");
    } // end function schemaUpdate

    /**
     * Creates a new empty schema file
     *
     * @param $shortDescription Short description for the task
     *
     * @return null
     */
    public static function createEmptySchemaFile($shortDescription)
    {
        $filePath = ROOT.DS."application";
        $filePath .= DS."Data";

        if (!Directory::exists($filePath)) {
            Directory::create($filePath);
        } // end if not exists directory Data

        $filePath .= DS."Schema";
        if (!Directory::exists($filePath)) {
            Directory::create($filePath);
        } // end if not exists directory Data

        $timestamp = Date::timestamp();
        $filePath .= DS."default_".$shortDescription."_"."$timestamp.yaml";

        File::write($filePath, "");

        return $filePath;
    } // end function createEmptySchemaFile

    /**
     * Read a line from command line
     *
     * @return String
     */
    public static function readLine()
    {
        if (PHP_OS == 'WINNT') {
            echo "\n$ ";
            $stdIn = stream_get_line(STDIN, 1024, "\n");
        } else {
          $stdIn = readline('$ ');
        } // end if then else os is windows

        return $stdIn;
    } // end function readLine

    public static function readLineSecret($promptMessage = 'Password:')
    {
        $promptMessage = addslashes($promptMessage);
        $command = "/usr/bin/env bash -c 'read -s -p \"$promptMessage\" mypassword && echo \$mypassword'";
        $password = rtrim(shell_exec($command));
        echo "\n";
        return $password;
    } // end function readSecret

    /**
     * Returns from the command line input, possible null
     *
     * @param $message The message to show
     * @param $defaultValue The default value to assign
     */
    public static function defaultReadLine($message, $defaultValue)
    {
        self::printLine($message);
        $result = self::readLine();
        return self::ifNull($result, $defaultValue);
    } // end function optionalReadLine

    /**
     * Return $evaluated if exists, else return $value
     *
     * @param $evaluated The expression to evaluate
     * @param $value The value to return is not $evaluated
     *
     * @return Object
     */
    public static function ifNull($evaluated, $value)
    {
        $result = ($evaluated) ? $evaluated : $value;
        return $result;
    } // end if null function

    /**
     * Request input from the user and then keeps asking until typed or cancelled
     *
     * @param $message The input message
     * @param $forceMessage The message to show to the user about required input
     *
     * @return string
     */
    public static function forceReadLine($message, $forceMessage)
    {
        self::printLine($message);
        $result = RS::readLine();
        while (!$result) {
            self::printLine($forceMessage);
            self::printLine($message);
            $result = RS::readLine();
        } // end while not drive

        return $result;
    } // end function forceReadLine
} // end function class RS
