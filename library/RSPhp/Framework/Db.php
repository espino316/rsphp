<?php
/**
 * Db.php
 *
 * PHP Version 7
 *
 * Db File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use PDO;
use Exception;
use InvalidArgumentException;
use stdClass;

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
class Db
{
    private static $dbConnections = array();
    private static $dataSources = array();
    private $isOpen = false;
    private $inTransaction = false;

    public $utf8 = false;

    /**
     * @var DbConnection The connection details to the database
     */
    public $dbConn;

    /**
     *
     * @var PDO The actual connection
     */
    public $conn;

    /**
     *
     * @var The select statement
     */
    protected $selectStatement;

    /**
     *
     * @var string The where conditions
     */
    protected $whereStatement;

    /**
     *
     * @var string The table to query on
     */
    protected $from;

    /**
     *
     * @var array The params for the array
     */
    protected $whereParams = array();

    /**
     *
     * @var string Order by statement
     */
    protected $orderByStatement;

    /**
     * The array for store the join tables
     *
     * @var array
     */
    protected $joinStatement;

    /**
     * Number or rows to return statement
     *
     * @var string
     */
    protected $limitStatement;

    /**
     * This will indicate if the limit is at beginning (2) or at the end (1)
     *
     * @var int
     */
    protected $limitType;

    /**
     * For SqlServer only. Indicates where to start
     *
     * @var int
     */
    protected $limitStartAt;

    /**
     * This is the name of the class to return, if any
     *
     * @var string
     */
    public $returnClassName;

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
        } else {
            $fileName = APPPATH.DS."DataSources".DS."default".DS.$dsName.".sql";
            if ( File::exists( $fileName ) ) {
                //  Create new datasource
                $dataSource = new DataSource(
                    "default",
                    $dsName,
                    "SQLQUERY",
                    File::read( $fileName )
                ); // end add dataSources
                return $dataSource;
            } // end if file exists
            return null;
        } // end if then else
    } // end getDataSource

    /**
     * Sets a dbconnection into the array of connections
     *
     * @param String $connName The connection name
     * @param DbConnection $conn The actual connection
     *
     * @return Null
     */
    public static function setDbConnection( $connName, $conn )
    {
        self::$dbConnections[$connName] = $conn;
    } // end function setDataSource

    /**
     * Returns a DbConnection from it's name
     *
     * @param String $connName The dbConnections's name
     *
     * @return DbConnection
     */
    public static function getDbConnection( $connName )
    {
        if ( isset( self::$dbConnections[$connName] ) ) {
            return self::$dbConnections[$connName];
        } else {
            return null;
        } // end if then else
    } // end getConnection

    /**
     * Return true if connections are set, else return false
     *
     * @return boolean
     */
    public static function hasDbConnections()
    {
        if ( count( self::$dbConnections ) ) {
            return true;
        } else {
            return false;
        } // end if count $dbConnections
    } // end function hasDbConnections

    /**
     * Set the class to return
     *
     * @param string $returnClassName The name of the class to return
     *
     * @return void
     */
    function setReturnClass( $returnClassName )
    {
        $this->returnClassName = $returnClassName;
    } // end function setReturnClass

    /**
     * Creates an instance of Db
     *
     * @param string $dbConnName The name of the connection to the database
     *
     * @return void
     */
    function __construct( $dbConnName = null )
    {

        if ( $dbConnName === null ) {
			if ( ! Db::hasDbConnections() ) {
				throw new InvalidArgumentException("No connections are set up", 1);
			} // end if isset DBConn
			if ( ! Db::getDbConnection( "default" ) ) {
				throw new InvalidArgumentException('Default database not set up!');
			}
			$this->dbConn = Db::getDbConnection( "default" );
		} else {
			if ( is_string( $dbConnName ) ) {
				$this->dbConn = Db::getDbConnection( $dbConnName );
			} else if ( is_array ( $dbConnName ) ) {
				$driver = array_key_exists( 'driver', $dbConnName );
				$hostName = array_key_exists( 'hostName', $dbConnName );
				$databaseName = array_key_exists( 'databaseName', $dbConnName );
				$userName = array_key_exists( 'userName', $dbConnName );
				$password = array_key_exists( 'password', $dbConnName );

				if ( $driver
						&& $hostName
						&& $databaseName
						&& $userName
						&& $password
				) {
					$this->dbConn = new DbConnection( $dbConnName );
				} else {
					throw new InvalidArgumentException("No connection data or incomplete", 1);
				} // end if valid
			} // end if is array
		}// end if is null
    } // end function __construct

    /**
     * Clears the variables in the class
     *
     * @return void
     */
    public function clear()
    {
        $this->from = null;
        $this->joinStatement = null;
        $this->limitStatement = null;
        $this->orderByStatement = null;
        $this->selectStatement = null;
        $this->whereParams = null;
        $this->whereStatement = null;
    } // end function clear

    /**
     * Returns the connection status
     */
    public function getConnStatus()
    {
        if ($this->conn) {
            return $this->conn->getAttribute(PDO::ATTR_CONNECTION_STATUS);
        } // end if $this conn

        return false;
    } // end function get conn status

    /**
     * Connect to the database
     *
     * @return void
     */
    public function connect()
    {
        if ($this->isOpen) {
            return;
        } // end if already open

        $driver = $this->dbConn->driver;
        $hostName = $this->dbConn->hostName;
        $databaseName = $this->dbConn->databaseName;
        $userName = $this->dbConn->userName;
        $password = $this->dbConn->password;

        if ($this->dbConn->port !== null ) {
            if (is_numeric($this->dbConn->port) ) {
                if ($driver == 'sqlsrv' ) {
                    $hostName = $hostName . ',' . $this->dbConn->port;
                } else {
                    $hostName = $hostName . ':' . $this->dbConn->port;
                } // end is sqlserv
            } // end if numeric port
        } // end if port

        $host = 'host';
        $dbname = 'dbname';

        if ($driver == 'sqlsrv' ) {
            $host = 'Server';
            $dbname = 'Database';
        }

        if ($driver == 'mysql' && $this->utf8) {
            $this->conn = new PDO(
                "$driver:$host=$hostName;$dbname=$databaseName",
                $userName,
                $password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
            );
        } else {
            $this->conn = new PDO(
                "$driver:$host=$hostName;$dbname=$databaseName",
                $userName,
                $password
            );
        } // end if mysql and utf8

        if (!$this->conn ) {
               throw new Exception("Connection error");
        } // if not conn

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->isOpen = true;
    } // end function connect

    /**
     * Disconnects
     */
    private function disconnect()
    {
        if ($this->inTransaction) {
            return;
        } // end if in transaction

        $this->conn = null;
        $this->clear();
        $this->isOpen = false;
    } // end function disconnect

    /**
     * Begin a new transaction
     */
    public function beginTransaction()
    {
        //  If not open, connect
        if (!$this->isOpen) {
            $this->connect();
        } // end if not is open

        if (!$this->inTransaction) {
            $this->inTransaction = true;
            $this->conn->beginTransaction();
        } // end if in transaction
    } // end function beginTransaction

    /**
     * Rollbacks current transaction
     */
    public function rollback()
    {
        if ($this->inTransaction) {
            $this->conn->rollback();
            $this->inTransaction = false;
            $this->disconnect();
        } // end if in transaction
    } // end function rollback

    /**
     * Commits current transaction
     */
    public function commit()
    {
        if ($this->inTransaction) {
            $this->conn->commit();
            $this->inTransaction = false;
            $this->disconnect();
        } // end if in transaction
    } // end function commit

    /**
     * Returns the insert statement
     *
     * @param string $tableName The name of the table
     * @param array  $params    The key value par of column name and value
     *
     * @return String
     */
    function getInsertStatement( $tableName, $params )
    {
        //	The sql instruction
        $sql = "INSERT INTO $tableName (";

        //	To check if is the first key
        $isInit = true;

        //	Loop the keys and add the column names
        foreach ( array_keys($params) as $colName ) {
            if ($isInit ) {
                $isInit = false;
                $sql .= "$colName";
            } else {
                $sql .= ", $colName";
            }
        }

        $sql .= ") VALUES ( ";

        //	Init again
        $isInit = true;

        //	New array with the ":" at the beginning
        //	and continuation of the insert statement

        //	Loop the keys and add the column Names for values
        foreach ( array_keys($params) as $colName ) {
            if ($isInit ) {
                $isInit = false;
                $sql .= ":$colName";
            } else {
                $sql .= ", :$colName";
            }

            $queryParams[":$colName"] = $params[$colName];
        }

        $sql .= ");";

        return $sql;
    } // end insert

    /**
     * Inserts a row to the database
     *
     * @param string $tableName The name of the table
     * @param array  $params    The key value par of column name and value
     *
     * @return void
     */
    function insert( $tableName, $params )
    {

        //	Connect
        $this->connect();

        //	The sql instruction
        $sql = "INSERT INTO $tableName (";

        //	To check if is the first key
        $isInit = true;

        //	Loop the keys and add the column names
        foreach ( array_keys($params) as $colName ) {
            if ($isInit ) {
                $isInit = false;
                $sql .= "$colName";
            } else {
                $sql .= ", $colName";
            }
        }

        $sql .= ") VALUES ( ";

        //	Init again
        $isInit = true;

        //	New array with the ":" at the beginning
        //	and continuation of the insert statement

        //	Loop the keys and add the column Names for values
        foreach ( array_keys($params) as $colName ) {
            if ($isInit ) {
                $isInit = false;
                $sql .= ":$colName";
            } else {
                $sql .= ", :$colName";
            }

            $queryParams[":$colName"] = $params[$colName];
        }

        $sql .= ");";

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Execute statement
        $statement
            = $this->conn->prepare(
                $sql,
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );
        $statement->execute($queryParams);

        //	Disconnect
        $this->disconnect();
    } // end insert

    /**
     * Updates records in the database
     *
     * @param string $tableName the name of the table
     * @param array  $params    the column name -> value for parameters
     * @param array  $where     the column name -> value for where clause
     *
     * @return void
     */
    function update( $tableName, $params, $where)
    {
        //	remove the wheres from params
        foreach ( array_keys($where) as $whereKey ) {
            if (array_key_exists($whereKey, $params) ) {
                unset($params[$whereKey]);
            }
        }

        //	Connect
        $this->connect();

        //	$sql will hold the sql statements
        $sql = "UPDATE $tableName SET ";

        //	isInit indicates if is the first column,
        //	to handle commas
        $isInit = true;

        //	New array with the ":" at the beginning
        //	and continuation of the update statement
        //	Loop the keys and add the column Names for values
        foreach ( array_keys($params) as $colName ) {
            if ($isInit ) {
                $isInit = false;
                $sql .= "$colName = :$colName";
            } else {
                $sql .= ", $colName = :$colName";
            }

            $queryParams[":$colName"] = $params[$colName];
        }

        if ($where != null ) {
            $sql .= " WHERE ";

            //	Loop for the where clause
            //	again isInit
            $isInit = true;

            foreach ( array_keys($where) as $colName ) {
                if ($isInit ) {
                    $isInit = false;
                    $sql .= " $colName = :$colName ";
                } else {
                    $sql .= " AND $colName = :$colName ";
                }

                //	Same array as data to update
                $queryParams[":$colName"] = $where[$colName];
            } // end foreach
        } // end if

        $sql .= ";";

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Execute statement
        $statement
            = $this->conn->prepare(
                $sql,
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );
        $statement->execute($queryParams);

        //	Disconnect
        $this->disconnect();
    } // end update

    /**
     * Returns an array with only distinct values
     *
     * @param Array $array The array to filter by unique values
     *
     * @return Array
     */
    function distinctArray($array)
    {
        $new = array();
        foreach ( array_keys($array) as $key ) {
            if (!array_key_exists($key, $new)) {
                $new[$key] = $array[$key];
            }
        }
        return $new;
    } // end function distinctArray

    /**
     * Updates or Insert a row, depending if data previously exists
     *
     * @param String $tableName The name of the table
     * @param Array  $params    The key value par of columns for insert / update
     * @param Array  $where     The key value par of columns for where clause.
     *        NULL if none.
     *
     * @return void
     */
    function upsert($tableName, $params, $where)
    {

        //	Connect
        $this->connect();

        if ($where == null) {

            return $this->insert($tableName, $params);
        } else {
            $sql = "SELECT COUNT(*) FROM $tableName WHERE ";
            //	Loop for the where clause
            //	we assume all AND because is restrictive to the primary key
            //	isInit for first item
            $isInit = true;


            foreach ( array_keys($where) as $colName ) {
                if ($isInit ) {
                    $isInit = false;
                    $sql .= " $colName = :$colName ";
                } else {
                    $sql .= " AND $colName = :$colName ";
                }

                //	Same array as data to update
                $queryParams[":$colName"] = $where[$colName];
            } // end foreach

            //	Execute statement
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $this->conn->prepare($sql);
            $statement->execute($queryParams);
            $result = $statement->fetchColumn();

            //	If 0 the is insert, else is update
            if ($result == 0 ) {

                $this->insert(
                    $tableName,
                    $this->distinctArray(
                        array_merge(
                            $params,
                            $where
                        )
                    )
                );
            } else {

                return $this->update($tableName, $params, $where);
            } // end else

        } // end else
    } // end upsert


    /**
     * Deletes records on the database
     *
     * @param String $tableName The name of the table
     * @param Array  $where     The where clause column name -> values array
     *
     * @return void
     */
    function delete($tableName, $where)
    {
        //	Connect
        $this->connect();

        //	$sql will store the sql statements
        $sql = "DELETE FROM $tableName WHERE ";

        //	isInit indicates if is the first column,
        //	to handle commas
        $isInit = true;

        //	Loop the keys and add the column Names for values
        foreach ( array_keys($where) as $colName ) {
            if ($isInit ) {
                $isInit = false;
                $sql .= " $colName = :$colName ";
            } else {
                $sql .= " AND $colName = :$colName ";
            }

            $queryParams[":$colName"] = $where[$colName];
        }

        $sql .= ";";

        //	Execute statement
        $statement
            = $this->conn->prepare(
                $sql,
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        $statement->execute($queryParams);

        //	Disconnect
        $this->disconnect();
    } // end delete

    /**
     * Deletes all rows in a table
     *
     * @param string $tableName The name of the table
     *
     * @return void
     */
    function truncate($tableName)
    {
        //	Connect
        $this->connect();

        // $sql will holde the sql statements
        $sql = "DELETE FROM $tableName;";

        //	Execute statement
        $statement
            = $this->conn->prepare(
                $sql,
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );
        $statement->execute();

        //	Disconnect
        $this->disconnect();
    } // end truncate

    /**
     * Query a specific table the database.
     * Returns a bidimensional array as resultset
     *
     * @param String $tableName   The name of the table to query
     * @param Array  $queryParams The actual key value pair collection of params
     * @param String $className   (optional) The name of the class to return
     *
     * @return Assoc Array
     */
    function queryTable($tableName, $queryParams = null, $className = null)
    {
        $where = "";
        if ($queryParams != null ) {
            foreach ( array_keys($queryParams) as $key ) {
                if ($where == "") {
                    $where .= " WHERE " . str_replace(":", "", $key) . " = $key ";
                } else {
                    $where .= " AND " . str_replace(":", "", $key) . " = $key ";
                }
            }
        }

        $sql = "SELECT * from $tableName $where";

        //	Connect
        $this->connect();

        //	Prepare
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $statement = $this->conn->prepare($sql);

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Desicion if params
        if ($queryParams != null ) {
            $statement->execute($queryParams);
        } else {
            $statement->execute();
        }

        if ($className != null) {
            $resultset = $statement->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $className);
        } else {

            if ($this->returnClassName !== null ) {
                $resultset
                    = $statement->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->returnClassName);
            } else {
                //	The result set to return
                $resultset = array();

                //	Loop throw rows and create an array
                //	assign the array to the resultset
                foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $v) {
                    $result = array();
                    foreach ( array_keys($v) as $colName ) {
                        $result[$colName] = $v[$colName];
                    }
                    $resultset[] = $result;
                }
            }
        }

        //	Disconnect
        $this->disconnect();

        //	Return the resultset
        return $resultset;
    } // end query

    /**
     * Returns a DbReader
     *
     * @return DbReader
     */
    function getReader($sql, $queryParams = null, $className = null)
    {
        return new DbReader( $this, $sql, $queryParams, $className );
    } // end function getReader

    /**
     * Query the database. Returns a bidimensional array as resultset
     *
     * @param string $sql         The query, with named params format ":paramName"
     * @param array  $queryParams The actual key value pair collection of params
     * @param string $className   (optional) The name of the class to return
     *
     * @return assoc_array
     */
    function query($sql, $queryParams = null, $className = null)
    {
        //	Connect
        $this->connect();

        //	Prepare
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $statement = $this->conn->prepare($sql);

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Desicion if params
        if ($queryParams != null ) {
            $statement->execute($queryParams);
        } else {
            $statement->execute();
        } // end if queryParams

        if ($className !== null) {
            $resultset = $statement->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $className);
        } else {

            if ($this->returnClassName !== null ) {
                $resultset
                    = $statement->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->returnClassName);
            } else {
                //	The result set to return
                $resultset = array();

                //	Loop throw rows and create an array
                //	assign the array to the resultset
                foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $v) {
                    $result = array();
                    foreach ( array_keys($v) as $colName ) {
                        if ($this->utf8) {
                            $result[$colName] =
                                mb_convert_encoding(
                                    $v[$colName], 
                                    "UTF-8", 
                                    mb_detect_encoding($v[$colName])
                                );
                        } else {
                            $result[$colName] = $v[$colName];
                        } // end if utf8
                    }
                    $resultset[] = $result;
                }
            }
        }

        //	Disconnect
        $this->disconnect();

        //	Return the resultset
        return $resultset;
    } // end query

    /**
     * Query the database, return scalar value
     *
     * @param string $sql         The query, with named params format ":paramName"
     * @param array  $queryParams The actual key value pair collection of params
     *
     * @return object
     */
    function scalar($sql, $queryParams)
    {
        //	Connect
        $this->connect();

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Prepare
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $statement = $this->conn->prepare($sql);

        //	Desicion if params
        if ($queryParams != null ) {
            $statement->execute($queryParams);
        } else {
            $statement->execute();
        }

        $result = $statement->fetchColumn();

        //	Disconnect
        $this->disconnect();

        return $result;
    } // end query

    /**
     * Query the database, execute DML statements
     *
     * @param String $sql         The query, with named params format ":paramName"
     * @param Array  $queryParams The actual key value pair collection of params
     *
     * @return Assoc Array
     */
    function nonQuery($sql, $queryParams = null)
    {
        //	Connect
        $this->connect();

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Prepare
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $statement = $this->conn->prepare($sql);

        //	Desicion if params
        if ($queryParams != null ) {
            $statement->execute($queryParams);
        } else {
            $statement->execute();
        }

        $this->disconnect();
        return true;
    } // end query

    /**
     * Returns the las id inserted to a table
     *
     * @param String $tableName The table's name
     * @param String $idColName The identity column name
     * @param Array  $where     The where key-value pair collection
     *
     * @return Object
     */
    function getLastId($tableName, $idColName, $where)
    {
        $this->where($where);
        return $this->max($idColName, $tableName);
    } // end function getLastId

    /**
     * Same as where
     *
     * @param String|Array $columnName The column's name
     * @param String       $value      The column's value
     *
     * @return Db
     */
    function andWhere($columnName, $value = null)
    {
        return $this->where($columnName, $value);
    } // end function andWhere

    /**
     * Sets an WHERE statement
     *
     * @param String      $columnName The column name, alternatively, an assoc array
     * @param String|null $value      The column's value
     *
     * @return Db
     */
    function where($columnName, $value = null)
    {

        if ($value == null && is_array($columnName) ) {

            foreach ( array_keys($columnName) as $colName ) {

                if (is_null($columnName[$colName])
                    && ! is_numeric($columnName[$colName])
                ) {
                    unset($columnName[$colName]);
                } else {
                    $cont = ($this->whereParams) ? count($this->whereParams) + 1 : 1;
                    $paramName = str_replace(".", "_", $colName) . $cont;
                    $this->whereParams[$paramName] = $columnName[$colName];

                    if (empty($this->whereStatement) ) {
                        $this->whereStatement = "$colName = :$paramName";
                    } else {
                        $this->whereStatement .= " AND $colName = :$paramName";
                    }
                }
            }

            return $this;
        }

        if ($value == null && $columnName ) {
            if (empty($this->whereStatement) ) {
                $this->whereStatement = " $columnName ";
            } else {
                $this->whereStatement .= " AND $columnName ";
            }
        } else {
            if ($this->whereParams) {
                $cont = count($this->whereParams) + 1;
            } else {
                $cont = 1;
            }
            $paramName = str_replace(".", "_", $columnName) . $cont;
            $this->whereParams[$paramName] = $value;

            if (empty($this->whereStatement) ) {
                $this->whereStatement = "$columnName = :$paramName";
            } else {
                $this->whereStatement .= " AND $columnName = :$paramName";
            }
        } // end if value == null & columnName

        //	Returns this same instance
        return $this;
    }

    /**
     * Sets and OR statement
     *
     * @param String  $columnName The column's name
     * @param mixed[] $value      The column's value
     *
     * @return Db
     */
    function orWhere($columnName, $value)
    {

        if ($value == null && $columnName ) {
            if (empty($this->whereStatement) ) {
                $this->whereStatement = " $columnName ";
            } else {
                $this->whereStatement .= " OR $columnName ";
            }
        } else {
            $cont = count($this->whereParams) + 1;
            $paramName = str_replace(".", "_", $columnName) . $cont;
            $this->whereParams[$paramName] = $value;

            if (empty($this->whereStatement) ) {
                $this->whereStatement = "$columnName = :$paramName";
            } else {
                $this->whereStatement .= " OR $columnName = :$paramName";
            }
        } // end if value == null & columnName

        //	Returns this same instance
        return $this;
    } // end function orWhere

    /**
     * Sets an WHERE BETWEEN statement
     *
     * @param String    $columnName The column name, alternatively, an assoc array
     * @param Mixed     $value1 The first value in the between clause
     * @param Mixed     $value2 The second value in the between clause
     *
     * @return Db
     */
    function whereBetween($columnName, $value1, $value2, $isOr = false)
    {
        $cont = count($this->whereParams) + 1;
        $paramName1 = str_replace(".", "_", $columnName) . $cont;
        $paramName2 = str_replace(".", "_", $columnName) . ($cont + 1);
        $this->whereParams[$paramName1] = $value1;
        $this->whereParams[$paramName2] = $value2;

        $whereType = ($isOr) ? "OR" : "AND";

        if (empty($this->whereStatement) ) {
            $this->whereStatement = "$columnName BETWEEN :$paramName1 AND :$paramName2";
        } else {
            $this->whereStatement .= " $whereType $columnName BETWEEN :$paramName1 AND :$paramName2";
        }

        //	Returns this same instance
        return $this;
    } // end whereBetween

    /**
     * Sets an OR WHERE BETWEEN statement
     *
     * @param String    $columnName The column name, alternatively, an assoc array
     * @param Mixed     $value1 The first value in the between clause
     * @param Mixed     $value2 The second value in the between clause
     *
     * @return Db
     */
    function orWhereBetween($columnName, $value1, $value2)
    {
        return $this->whereBetween($columnName, $value1, $value2, true);
    } // end whereBetween

    /**
     * Sets an LIKE statement on an AND clause
     *
     * @param String $columnName The column's name
     * @param String $value      The value to be compared in the like clause
     *
     * @return Db
     */
    function like($columnName, $value)
    {

        $cont = count($this->whereParams) + 1;
        $paramName = str_replace(".", "_", $columnName) . $cont;
        $this->whereParams[$paramName] = '%' . strtoupper($value) . '%';;

        if (empty($this->whereStatement) ) {
            $this->whereStatement = "UPPER($columnName) LIKE :$paramName";
        } else {
            $this->whereStatement .= " AND UPPER($columnName) LIKE :$paramName";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Sets a like statement on an OR clause
     *
     * @param String $columnName The column's name
     * @param Object $value      The value to be compared in the like clause
     *
     * @return Db
     */
    function orLike($columnName, $value)
    {
        $cont = count($this->whereParams) + 1;
        $paramName = str_replace(".", "_", $columnName) . $cont;
        $this->whereParams[$paramName] = '%' . $value . '%';;

        if (empty($this->whereStatement) ) {
            $this->whereStatement = "$columnName LIKE :$paramName";
        } else {
            $this->whereStatement .= " OR $columnName LIKE :$paramName";
        }

        //	Returns this same instance
        return $this;

        //	Returns this same instance
        return $this;
    }

    /**
     * Set the table to query from
     *
     * @param string $tableName The name of the table
     *
     * @return Db
     */
    function from($tableName)
    {
        $this->from = $tableName;

        //	Returns this same instance
        return $this;
    }

    /**
     * Adds columns to the select statement
     *
     * @param string $column The column, or list of columns comma separated
     *
     * @return Db
     */
    function select($column)
    {

        if (empty($this->selectStatement) ) {
            $this->selectStatement = $column;
        } else {
            $this->selectStatement .= ", $column";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Adds an order by statement to the current select statement
     *
     * @param String $column  The column name
     * @param String $ascDesc Indicates the sort type ( 'ASC', 'DESC' )
     *
     * @return Db
     */
    function orderBy($column, $ascDesc = null)
    {
        if (empty($this->orderByStatement) ) {
            $this->orderByStatement
                = $column . ($ascDesc != null ? " $ascDesc" : "");
        } else {
            $this->orderByStatement
                .= ", $column" . ($ascDesc != null ? " $ascDesc" : "");
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Returns the first row in the resultset
     *
     * @param string|null $tableName The table's name
     * @param string|null $className The name of the class to return
     *
     * @return array Array
     */
    function first($tableName = null, $className = null)
    {

        $result = $this->get($tableName, $className);

        if (count($result) > 0 ) {
            return $result[0];
        } else {
            return null;
        }
    } // end function first

    /**
     * Returns the Sql Statement
     *
     * @param String|null $tableName The name of the table to query
     *
     * @return String
     */
    function getSql( $tableName = null )
    {
        $sql = "";

        if ($tableName != null) {
            $this->from = $tableName;
        } else {
            if (empty($this->from) ) {
                throw new Exception("From table not specified!");
            }
        }

        if (empty($this->selectStatement) ) {
            $this->selectStatement = " * ";
        }

        $sql = "SELECT ";
        if ($this->limitType == 2 ) {
            $sql .= $this->limitStatement;
        }

        $sql .= $this->selectStatement;
        $sql .= " FROM " . $this->from;

        if (!empty($this->joinStatement) ) {
            $sql .= $this->joinStatement;
        }

        if (!empty($this->whereStatement) ) {
            $sql .= " WHERE " . $this->whereStatement;
        }

        if (!empty($this->orderByStatement) ) {
            $sql .= " ORDER BY " . $this->orderByStatement;
        }

        if ($this->limitType == 1 ) {
            $sql .= $this->limitStatement;
        }

        if ($this->limitType == 3 ) {
            $sql = str_replace('SELECT', '', $sql);
            $sql = str_replace('@selectStatement', $sql, $this->limitStatement);
            if ($this->orderByStatement ) {
                $sql
                    = str_replace(
                        '@orderByStatement',
                        $this->orderByStatement,
                        $sql
                    );
            } else {
                if ($this->from ) {
                    $orderBy = $this->getIdentityColumn($this->from);
                    if ($orderBy ) {

                    } else {
                        $primaryKeys = $this->getPrimaryKeys($this->from);
                        $orderBy = $primaryKeys[0];
                    }
                } else {
                    throw new Exception("Statement has no from");
                } // end if from
                $sql = str_replace('@orderByStatement', " ORDER BY $orderBy ", $sql);
            } // end if orderByStatement
        } // end if limitType 3

        return $sql;
    } // end function getSql

    /**
     * Performs a query to the database, constructed by the class
     *
     * @param string|null $tableName The name of the table to query
     * @param string|null $className The name of the class to return array of
     *
     * @return array Array
     *
     * @throws Exception
     */
    function get($tableName = null, $className = null)
    {

        $sql = $this->getSql($tableName);

        $queryParams = null;
        if (!empty($this->whereStatement) ) {
            $queryParams = $this->whereParams;
        }

        //	Returns a resultset
        return $this->query($sql, $queryParams, $className);
    }

    /**
     * Performs a query to the database, constructed by the class
     * and returns a scalar value
     *
     * @param string $tableName The name of the table to query
     *
     * @return Assoc Array
     *
     * @throws Exception
     */
    function getScalar($tableName = null)
    {

        $sql = "";

        if ($tableName != null) {
            $this->from = $tableName;
        } else {
            if (empty($this->from) ) {
                throw new Exception("From table not specified!");
            }
        }

        if (empty($this->selectStatement) ) {
            $this->selectStatement = " * ";
        }

        $sql = "SELECT " . $this->selectStatement;
        $sql .= " FROM " . $this->from;

        if (!empty($this->joinStatement) ) {
            $sql .= $this->joinStatement;
        }

        if (!empty($this->whereStatement) ) {
            $sql .= " WHERE " . $this->whereStatement;
        }

        if (!empty($this->orderByStatement) ) {
            $sql .= " ORDER BY " . $this->orderByStatement;
        }

        $queryParams = null;
        if (!empty($this->whereStatement) ) {
            $queryParams = $this->whereParams;
        }

        //	Returns a resultset
        return $this->scalar($sql, $queryParams);
    }

    /**
     * Performs a query count to the database, constructed by the class
     *
     * @param string $tableName The name of the table to query
     *
     * @return Int
     *
     * @throws Exception
     */
    function count($tableName = null)
    {

        $sql = "";

        if ($tableName != null) {
            $this->from = $tableName;
        } else {
            if (empty($this->from) ) {
                throw new Exception("From table not specified!");
            }
        }

        $sql = "SELECT COUNT(*) ";
        $sql .= " FROM " . $this->from;

        if (!empty($this->joinStatement) ) {
            $sql .= $this->joinStatement;
        }

        if (!empty($this->whereStatement) ) {
            $sql .= " WHERE " . $this->whereStatement;
        }

        $queryParams = null;
        if (!empty($this->whereStatement) ) {
            $queryParams = $this->whereParams;
        }

        //	Returns a resultset
        return $this->scalar($sql, $queryParams);
    } // end function count

    /**
     * Performs a query count to the database, constructed by the class
     *
     * @param string $colName   Name The name of the field to count
     * @param string $tableName The name of the table to query
     *
     * @return Int
     *
     * @throws Exception
     */
    function max($colName, $tableName = null)
    {

        $sql = "";

        if ($tableName != null) {
            $this->from = $tableName;
        } else {
            if (empty($this->from) ) {
                throw new Exception("From table not specified!");
            }
        }

        $sql = "SELECT MAX($colName) ";
        $sql .= " FROM " . $this->from;

        if (!empty($this->joinStatement) ) {
            $sql .= $this->joinStatement;
        }

        if (!empty($this->whereStatement) ) {
            $sql .= " WHERE " . $this->whereStatement;
        }

        $queryParams = null;
        if (!empty($this->whereStatement) ) {
            $queryParams = $this->whereParams;
        }

        //	Returns a resultset
        return $this->scalar($sql, $queryParams);
    } // end function max

    /**
     * Returns true if any record exists
     *
     * @param String|null $tableName The table's name
     *
     * @return Boolean
     */
    function exists($tableName = null)
    {

        $count = $this->count($tableName);

        if ($count > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds a condition for joining tables
     *
     * @param String $leftField  The field for the left table to join
     * @param String $operator   The joining operator ( '=', '>=', '<=', etc )
     * @param String $rightField The field for the right table to join
     *
     * @return Db
     */
    function andOn($leftField, $operator, $rightField)
    {
        if (!empty($this->joinStatement) ) {
            $this->joinStatement .= " AND $leftField $operator $rightField ";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Creates the statement to join a table
     *
     * @param String $tableName  The name of the table to join
     * @param String $leftField  The field for the left table to join
     * @param String $operator   The joining operator ( '=', '>=', '<=', etc )
     * @param String $rightField The field for the right table to join
     *
     * @return Db
     */
    function join($tableName, $leftField, $operator, $rightField)
    {

        if (empty($this->joinStatement) ) {
            $this->joinStatement
                = " JOIN $tableName ON $leftField $operator $rightField ";
        } else {
            $this->joinStatement
                .= " JOIN $tableName ON $leftField $operator $rightField ";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Creates the statement to left join a table
     *
     * @param String $tableName  The name of the table to join
     * @param String $leftField  The field for the left table to join
     * @param String $operator   The joining operator ( '=', '>=', '<=', etc )
     * @param String $rightField The field for the right table to join
     *
     * @return Db
     */
    function leftJoin($tableName, $leftField, $operator, $rightField)
    {

        if (empty($this->joinStatement) ) {
            $this->joinStatement
                = " LEFT JOIN $tableName ON $leftField $operator $rightField ";
        } else {
            $this->joinStatement
                .= " LEFT JOIN $tableName ON $leftField $operator $rightField ";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Creates the statement to right join a table
     *
     * @param String $tableName  The name of the table to join
     * @param String $leftField  The field for the left table to join
     * @param String $operator   The joining operator ( '=', '>=', '<=', etc )
     * @param String $rightField The field for the right table to join
     *
     * @return Db
     */
    function rightJoin($tableName, $leftField, $operator, $rightField)
    {

        if (empty($this->joinStatement) ) {
            $this->joinStatement
                = " RIGHT JOIN $tableName ON $leftField $operator $rightField ";
        } else {
            $this->joinStatement
                .= " RIGHT JOIN $tableName ON $leftField $operator $rightField ";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Creates the statement to cross join a table
     *
     * @param String $tableName The name of the table to join
     *
     * @return Db
     */
    function crossJoin($tableName)
    {

        if (empty($this->joinStatement) ) {
            $this->joinStatement = " LEFT JOIN $tableName ";
        } else {
            $this->joinStatement .= " LEFT JOIN $tableName ";
        }

        //	Returns this same instance
        return $this;
    }

    /**
     * Sets an "TOP" or "LIMIT" clause
     *
     * @param int $limit   Number of row to return
     * @param int $startAt Indicates the row number to start to return
     *
     * @return Db
     */
    function top($limit, $startAt = null)
    {
        switch($this->dbConn->driver) {
        case "mysql":
            $this->limitType = 1;
            if ($startAt ) {
                $this->limitStatement = " LIMIT $startAt, $limit ";
            } else {
                $this->limitStatement = " LIMIT $limit ";
            }
            break;
        case "pgsql":
            $this->limitType = 1;
            $this->limitStatement = " LIMIT $limit ";
            if ($startAt != null ) {
                $this->limitStatement .= " offset $startAt";
            }
            break;
        case "sqlsrv":
            $this->limitType = 2;
            if ($startAt ) {
                $this->limitType = 3;
                $this->limitStatement = "SELECT
							TOP $limit
							*
						FROM
							(
								SELECT
									ROW_NUMBER() OVER ( @orderByStatement ) AS ROW,
									@selectStatement
							) AS A
						WHERE
							A.ROW > $startAt";
            } else {
                $this->limitStatement = " TOP ( $limit ) ";
            }
            break;
        case "dblib":
            $this->limitType = 2;
            $this->limitStatement = " TOP ($limit) ";
            break;
        }

        return $this;
    } // end function top


    /**
     * Run the querys to provide pagination
     *
     * @param Int         $pageItems   The number of rows to show
     * @param Int         $page        The number of the page to show
     * @param String      $query       The query to run
     * @param Assoc Array $queryParams The query params
     *
     * @return Assoc Array
     */
    function paginateQuery($pageItems, $page, $query, $queryParams)
    {

        $sql = "SELECT COUNT(*) FROM ($query) AS A;";
        $count = $this->scalar($sql, $queryParams);

        //	Then, get the pages
        $pagesCount = ceil((int)$count / $pageItems);

        //	Then the start at
        $startAt = ($page - 1) * $pageItems;

        $sql = $this
            ->select("A.*")
            ->from("($query) AS A")
            ->top($pageItems, $startAt)
            ->getSql();

        $results = $this->query($sql, $queryParams);

        $result['results'] = $results;

        //	Get the pages
        for ( $i=1; $i<=$pagesCount; $i++ ) {
            $pages[] = $i;
        }

        $result['pages'] = $pages;
        $result['currentPage'] = $page;

        return $result;
    } // end function paginateQuery

    /**
     * Paginates a result set, use instead of get
     *
     * @param Int         $pageItems Number of items per page
     * @param Int         $page      The page number, from 1 to N
     * @param String|null $tableName The table's name
     * @param String|null $className The class's name to convert the rows
     *
     * @return Assoc Array
     */
    function paginate($pageItems, $page, $tableName = null, $className = null)
    {
        //	First, get the count
        $clone = clone $this;
        $count = $clone->count();

        //	Then, get the pages
        $pagesCount = ceil($count / $pageItems);

        //	Then the start at
        $startAt = ($page - 1) * $pageItems;

        //	Then set the top
        $this->top($pageItems, $startAt);

        //	Get the results
        $results = $this->get($tableName, $className);
        $result['results'] = $results;

        //	Get the pages
        for ( $i=1; $i<=$pagesCount; $i++ ) {
            $pages[] = $i;
        }

        $result['pages'] = $pages;
        $result['currentPage'] = $page;

        return $result;

    } // en function paginate

    /**
     * Get the tables in the database
     */
    function getTables()
    {
        $sql = "
            SELECT
                table_catalog,
                table_name,
                table_type
            FROM information_schema.tables
            WHERE table_catalog = :databaseName
            AND table_schema = 'public'
            AND table_type = 'BASE TABLE'
";

        if ($this->dbConn->driver == 'mysql') {
            $sql = "SELECT
                table_schema as table_catalog,
                table_name,
                table_type
            FROM information_schema.tables
            WHERE table_schema = :databaseName
            AND table_type = 'BASE TABLE'";
        } // end if mysql

        $queryParams['databaseName'] = $this->dbConn->databaseName;
        $result = $this->query($sql, $queryParams, 'stdClass');
        return $result;
    } // end function getTables

    /**
     * Returns an array with the columns information schema
     *
     * @param String $tableName The name of the table to query the schema
     *
     * @return Array
     */
    function getColumns( $tableName )
    {
        $sql = "";

        switch ($this->dbConn->driver) {
        case 'pgsql':
            $sql = "SELECT
								  ORDINAL_POSITION,
								  COLUMN_NAME,
								  DATA_TYPE,
								  CHARACTER_MAXIMUM_LENGTH,
								  IS_NULLABLE
								FROM
								  INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_CATALOG = :databaseName
									AND
                                        TABLE_NAME = :tableName
                                ORDER BY
                                    ORDINAL_POSITION";
            break;

        case 'mysql':
            $sql = "SELECT
								  ORDINAL_POSITION,
								  COLUMN_NAME,
								  DATA_TYPE,
								  CHARACTER_MAXIMUM_LENGTH,
								  IS_NULLABLE
								FROM
								  INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_SCHEMA = :databaseName
									AND
										TABLE_NAME = :tableName
                                ORDER BY
                                    ORDINAL_POSITION";
            break;

        case 'sqlsrv':
            $sql = "SELECT
								  ORDINAL_POSITION,
								  COLUMN_NAME,
								  DATA_TYPE,
								  CHARACTER_MAXIMUM_LENGTH,
								  IS_NULLABLE
								FROM
								  INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_CATALOG = :databaseName
									AND
										TABLE_NAME = :tableName
                                ORDER BY
                                    ORDINAL_POSITION";
            break;

        case 'dblib':
            $sql = "SELECT
								  ORDINAL_POSITION,
								  COLUMN_NAME,
								  DATA_TYPE,
								  CHARACTER_MAXIMUM_LENGTH,
								  IS_NULLABLE
								FROM
								  INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_CATALOG = :databaseName
									AND
										TABLE_NAME = :tableName
                                ORDER BY
                                    ORDINAL_POSITION";
            break;

        default:
            // code...
            break;
        } // end switch

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;
        $result = $this->query($sql, $queryParams);
        return $result;

    } // end function getProperties

    /**
     * Returns un array with the public properties for a model
     * obtained from the information schema
     *
     * @param String $tableName The name of the table to query the schema
     *
     * @return Array
     */
    function getPublicProperties( $tableName )
    {
        $sql = "";

        switch ($this->dbConn->driver) {
        case 'pgsql':
            $sql = "SELECT 'public $' || column_name || ';' AS property
								FROM information_schema.columns
								WHERE table_catalog = :databaseName
								  AND table_name   = :tableName";
            break;

        case 'mysql':
            $sql = "SELECT
									CONCAT('public $',COLUMN_NAME,';') AS property
								FROM
									INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_SCHEMA = :databaseName
									AND
										TABLE_NAME = :tableName";
            break;

        case 'sqlsrv':
            $sql = "SELECT
									'public $' + COLUMN_NAME + ';' AS property
								FROM
									INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_CATALOG = :databaseName
									AND
										TABLE_NAME = :tableName";
            break;

        case 'dblib':
            $sql = "SELECT
									'public $' + COLUMN_NAME + ';' AS property
								FROM
									INFORMATION_SCHEMA.COLUMNS
								WHERE
									TABLE_CATALOG = :databaseName
									AND
										TABLE_NAME = :tableName";
            break;

        default:
            // code...
            break;
        } // end switch

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;
        $result = $this->query($sql, $queryParams);

        $publicProperties = '';
        foreach ( $result as $row ) {
            $publicProperties .= ($publicProperties) ? "\t".$row['property']."\n" : $row["property"]."\n";
        } // end foreach

        return $publicProperties;

    } // end function getProperties

    /**
     * Return the identity column of the table if any
     *
     * @param String $tableName The name of the table to query the schema
     *
     * @return Assoc Array
     */
    function getIdentityColumn( $tableName )
    {
        $sql = "";
        switch ($this->dbConn->driver) {
        case 'pgsql':
            $sql = "SELECT column_name as property
			FROM information_schema.columns
			WHERE table_catalog = :databaseName
			AND table_name   = :tableName
			AND column_default LIKE '%nextval%'";
            break;

        case 'mysql':
            $sql = "SELECT
									COLUMN_NAME
								FROM
									INFORMATION_SCHEMA.`COLUMNS`
								WHERE
									TABLE_NAME = :tableName
									AND
										TABLE_SCHEMA = :databaseName
									AND
										EXTRA = 'auto_increment';";
            break;

        case 'sqlsrv':
            $sql = "SELECT
									COLUMN_NAME
								FROM
									INFORMATION_SCHEMA.COLUMNS
								WHERE
									COLUMNPROPERTY(object_id(TABLE_NAME), COLUMN_NAME, 'IsIdentity') = 1
									AND TABLE_NAME = :tableName
									AND TABLE_CATALOG = :datebaseName
								ORDER BY
									TABLE_NAME";
            break;

        case 'sqlsrv':
            $sql = "SELECT
									COLUMN_NAME
								FROM
									INFORMATION_SCHEMA.COLUMNS
								WHERE
									COLUMNPROPERTY(object_id(TABLE_NAME), COLUMN_NAME, 'IsIdentity') = 1
									AND TABLE_NAME = :tableName
									AND TABLE_CATALOG = :datebaseName
								ORDER BY
									TABLE_NAME";
            break;

        default:
            // code...
            break;
        } // end switch

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;
         $result = $this->scalar($sql, $queryParams);

        return $result;
    } // end function getIdentityColumn

    /**
     * Retrives the foreign keys from the database
     *
     * @param string $tableName The table name
     *
     * @return array
     */
    function getForeignKeys($tableName)
    {
        $sql = "
SELECT
   tc.constraint_name,
   tc.table_name,
   kcu.column_name,
   ccu.table_name AS foreign_table_name,
   ccu.column_name AS foreign_column_name
FROM
   information_schema.table_constraints AS tc
   JOIN
      information_schema.key_column_usage AS kcu
      ON tc.constraint_name = kcu.constraint_name
   JOIN
      information_schema.constraint_column_usage AS ccu
      ON ccu.constraint_name = tc.constraint_name
WHERE
   constraint_type = 'FOREIGN KEY'
   AND tc.table_name = :tableName
   AND tc.constraint_catalog = :databaseName
ORDER BY
   constraint_name ASC
";

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;
        $result = $this->query($sql, $queryParams, 'stdClass');
        return $result;
    } // end function getForeignKeys

    /**
     * Returns the columns for a table constraint, or all if $constraintName not present
     *
     * @param string $tableName The table name
     * @param string $constraintName The constraint name
     *
     * @return array
     */
    function getTableConstraintsColumns($tableName, $constraintName = '')
    {
        $sql = "
SELECT
   t.table_name,
   tc.constraint_name,
   tc.constraint_type,
   kcu.column_name,
   ccu.table_name AS foreign_table_name,
   ccu.column_name AS foreign_column_name
FROM
   INFORMATION_SCHEMA.TABLES t
   LEFT JOIN
      INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
      ON tc.table_catalog = t.table_catalog
      AND tc.table_schema = t.table_schema
      AND tc.table_name = t.table_name
   LEFT JOIN
      INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
      ON kcu.table_catalog = tc.table_catalog
      AND kcu.table_schema = tc.table_schema
      AND kcu.table_name = tc.table_name
      AND kcu.constraint_name = tc.constraint_name
   LEFT JOIN
      information_schema.constraint_column_usage AS ccu
      ON ccu.constraint_name = tc.constraint_name
      AND tc.constraint_type = 'FOREIGN KEY'
WHERE
   t.table_catalog = :databaseName
   AND t.table_name = :tableName
   AND (:getAll = 1 OR tc.constraint_name = :constraintName)
   AND kcu.column_name IS NOT NULL
ORDER BY
   t.table_catalog,
   t.table_schema,
   t.table_name,
   kcu.constraint_name,
   kcu.ordinal_position
";

        $getAll = ($constraintName) ? 0 : 1;
        $constraintName = $getAll ? "" : $constraintName;
        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;
        $queryParams['getAll'] = $getAll;
        $queryParams['constraintName'] = $constraintName;
        $result = $this->query($sql, $queryParams, 'stdClass');
        return $result;

    } // end function getTableConstraintColumns

    /**
     * Gets the table constraints
     *
     * @param string $tableName The table name
     *
     * @return array
     */
    function getTableConstraints($tableName)
    {
        $sql = "
SELECT
    table_name,
    constraint_name,
    constraint_type
FROM
    information_schema.table_constraints
WHERE
    constraint_catalog = :databaseName
    AND table_name = :tableName
";

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;
        $result = $this->query($sql, $queryParams, 'stdClass');
        return $result;
    } // end function getTableConstraints

    /**
     * Returns a resultset with the primary keys
     *
     * @param String $tableName The name of the table to query the schema
     *
     * @return Array
     */
    function getPrimaryKeys( $tableName )
    {

        $sql = "";
        switch ($this->dbConn->driver) {
        case 'pgsql':
            $sql = "
SELECT
  kcu.column_name
FROM
  INFORMATION_SCHEMA.TABLES t
  INNER JOIN
    INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
    ON tc.table_catalog = t.table_catalog
    AND tc.table_schema = t.table_schema
    AND tc.table_name = t.table_name
    AND tc.constraint_type = 'PRIMARY KEY'
  LEFT JOIN
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
    ON kcu.table_catalog = tc.table_catalog
    AND kcu.table_schema = tc.table_schema
    AND kcu.table_name = tc.table_name
    AND kcu.constraint_name = tc.constraint_name
WHERE
  t.table_catalog = :databaseName
  AND t.table_name = :tableName
ORDER BY
  t.table_catalog,
  t.table_schema,
  t.table_name,
  kcu.constraint_name,
  kcu.ordinal_position;
";
            break;

        case 'mysql':
            $sql = "SELECT
									KCU.COLUMN_NAME
								FROM
									INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
								INNER JOIN
									INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU
								ON
									KCU.TABLE_NAME = TC.TABLE_NAME
									AND
										KCU.TABLE_SCHEMA = TC.TABLE_SCHEMA
									AND
										KCU.CONSTRAINT_NAME = TC.CONSTRAINT_NAME
								WHERE
									TC.TABLE_NAME = :tableName
									AND
										TC.CONSTRAINT_TYPE = 'PRIMARY KEY'
									AND
										TC.TABLE_SCHEMA = :databaseName
								ORDER BY
									KCU.ORDINAL_POSITION";
            break;

        case 'sqlsrv':
            $sql = "SELECT
									KCU.COLUMN_NAME
								FROM
									INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
								INNER JOIN
									INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU
								ON
									KCU.TABLE_NAME = TC.TABLE_NAME
									AND
										KCU.TABLE_CATALOG = TC.TABLE_CATALOG
									AND
										KCU.CONSTRAINT_NAME = TC.CONSTRAINT_NAME
								WHERE
									TC.TABLE_NAME = :tableName
									AND
										TC.CONSTRAINT_TYPE = 'PRIMARY KEY'
									AND
										TC.TABLE_CATALOG = :databaseName
								ORDER BY
									KCU.ORDINAL_POSITION";
            break;

        case 'dblib':
            $sql = "SELECT
									KCU.COLUMN_NAME
								FROM
									INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
								INNER JOIN
									INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU
								ON
									KCU.TABLE_NAME = TC.TABLE_NAME
									AND
										KCU.TABLE_CATALOG = TC.TABLE_CATALOG
									AND
										KCU.CONSTRAINT_NAME = TC.CONSTRAINT_NAME
								WHERE
									TC.TABLE_NAME = :tableName
									AND
										TC.CONSTRAINT_TYPE = 'PRIMARY KEY'
									AND
										TC.TABLE_CATALOG = :databaseName
								ORDER BY
									KCU.ORDINAL_POSITION";
            break;

        default:
            //	The default is pgsql
            break;
        } // end switch

        $queryParams['tableName'] = $tableName;
        $queryParams['databaseName'] = $this->dbConn->databaseName;

        $results = $this->query($sql, $queryParams);
        $result = array();
        if ( $results ) {
            foreach( $results as $row ) {
                $result[] = reset($row);
            } // end foreach
        } // end if $result

        return $result;
    } // end function getPrimaryKeys

    /**
     * Returns only some columns from a resultset
     *
     * @param assoc_array $resultSet An array of assoc_arrays with the original info
     * @param array $itemKeys An array of string, containing the columns name to select
     *
     * @return Array
     */
    static function resultSetSelect($resultSet, $itemKeys) {
        if (!is_array($itemKeys)) {
            throw new Exception("Item keys must be an array of string");
        } // end if not is array itemm keys

        $result = array();
        if ($resultSet) {
            foreach ($resultSet as $row) {
                $rw = array();
                foreach ($itemKeys as $key) {
                    $rw[$key] = $row[$key];
                } // end foreach key
                $result[] = $rw;
            } // end foreach
        } // end if resultSet

        return $result;
    } // end function resultSetSelect

    /**
     * Returns an filtered array
     *
     * @param assoc_array $resultSet An array of assoc arrays with the original info
     * @param string      $itemKey   The name of the key to filter
     * @param variable    $itemValue The actual value to find
     *
     * @return Array
     */
    static function resultSetFilter( $resultSet, $itemKey, $itemValue )
    {
        $result = array();
        if ($resultSet ) {
            foreach ( $resultSet as $row ) {
                if (isset($row[$itemKey]) ) {
                    if ($row[$itemKey] === $itemValue ) {
                        $result[] = $row;
                    } // end if = value
                } // end if isset
            } // end foreach
        }

        return $result;
    } // end function resultSetFilter

    /**
     * Updates a resultset
     *
     * @param Array       $resultSet The array with the result set as reference
     * @param Assoc Array $row       The array with the collection to update
     * @param Assoc Array $keys      An array with the key columns
     *
     * @return void
     */
    static function updateResultSet( &$resultSet, $row, $keys )
    {

        $idx = null;
        foreach ( $resultSet as $index => $rs ) {
            $cond = true;
            foreach ( $keys as $key ) {
                if ($rs[$key] !== $row[$key] ) {
                    $cond = false;
                }
            } // end foreach $key
            if ($cond ) {
                $idx = $index;
                break;
            }
        } // end foreach resultset

        if ($idx ) {
            unset($resultSet[$idx]);
            $resultSet[] = $row;
            return true;
        } // end if $idx

        return false;
    } // end function updateResultSet

    /**
     * Returns the index for a key value pair
     *
     * @param Array  $resultSet The array of assoc arrays to query
     * @param String $itemKey   The key or column name to search for
     * @param mixed  $itemValue The column's value
     *
     * @return mixed[]
     */
    static function resultSetIndex( $resultSet, $itemKey, $itemValue )
    {
        if ($resultSet ) {
            foreach ( $resultSet as $index => $row ) {
                if ($row[$itemKey] === $itemValue ) {
                    return $index;
                } // end if = value
            } // end foreach
        } // end if resultSet

        return null;
    } // end function resultSetIndex

    /**
     * Deletes a row from a result set
     *
     * @param Array       $resultSet The array of array to update
     * @param Assoc Array $params    The key-value pair array of values to search for
     *
     * @return void
     */
    static function deleteFromResultSet( $resultSet, $params )
    {
        $indexes = null;
        foreach ( $resultSet as $index => $rs ) {

            $paramCount = count($params);
            $count = 0;
            foreach ( array_keys($params) as $key ) {
                if ($rs[$key] == $params[$key] ) {
                    $count++;
                }
            } // end foreach $key
            if ($paramCount === $count ) {
                $indexes[] = $index;
                break;
            }
        } // end foreach resultset

        if ($indexes ) {
            foreach ( $indexes as $index ) {
                unset($resultSet[ $index ]);
            } // end foreach
        } // end if $idx

        return $resultSet;
    } // deleteFromResultSet

    /**
     * Returns true if $tableName exists in the database
     *
     * @param $tableName The table name
     *
     * @return Boolean
     */
    public function tableExists($tableName)
    {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = :tableName";
        $count = $this->scalar(
            $sql,
            array(
                "tableName" => $tableName
            )
        );

        return ($count);
    } // end function table exists

} // end class Db
