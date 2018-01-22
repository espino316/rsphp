<?php
/**
 * Model.php
 *
 * PHP Version 5
 *
 * Model File Doc Comment
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
 * Represent a Model in the MVC pattern
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
class Model
{

    /**
     *
     * @var Db
     */
    protected static $db;

    /**
     * Creates a new instance of Model
     *
     * @param mixed[] $dbConnName The database connection information
     *
     * @return void
     */
    function __construct($dbConnName = null)
    {
        self::$db = new Db($dbConnName);
    } // end function __construct

    /**
     * Return a list of its properties
     *
     * @return Arrray
     */
    public function getProperties() {
        return get_object_vars($this);
    } // end function getProperties

    /**
     * Takes the value of its properties from the input
     *
     * @return void
     */
    public function fromInput()
    {
        //  Get the properties
        $properties = $this->getProperties();

        //  Get the input
        $inputs = Input::get();

        //  Setup undefined properties
        foreach( array_keys( $properties ) as $key ) {
            if ( $key == "tableName" ) {
                continue; // next
            } // end if
            $this->$key = Undefined::instance();
        } // end foreach

        //  Loop inputs, search for property, set property
        foreach ( array_keys($inputs) as $inputKey ) {
            foreach ( array_keys($properties) as $propertyKey ) {
                if ($inputKey == $propertyKey ) {
                    $this->$propertyKey = $inputs[$inputKey];
                } // end if inputkey = propertyKey
            } // end foreach properties
        } // end foreach input
    } // end function getPropertiesFromInput

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     * Sets a table name for the model
     *
     * @param String $tableName The table name
     *
     * @return void
     */
    function setTableName( $tableName )
    {
        $this->tableName = $tableName;
    } // end function setTableName

    /**
     * Returns the table name of the class,
     * assumes stripping Model from class name by default
     *
     * @return String
     */
    function getTableName()
    {
        if ($this->tableName == null) {
            $tableName = substr_replace(get_class($this), "", -5);
            $tableName = str_replace( "Application\\Models\\", "", $tableName );
            return $tableName;
        } else {
            return $this->tableName;
        }
    } // end getTableName

    /**
     * Returns the table name
     *
     * @return void
     */
    static function tableName()
    {
        $tableName = substr_replace(get_called_class(), "", -5);
        $tableName = str_replace( "Application\\Models\\", "", $tableName );
        return $tableName;
    } // end function

    /**
     * Gets the first record as an instance of this class
     *
     * @return self
     */
    static function first()
    {
        self::setDB();
        return self::$db->first(
            self::tableName(),
            get_called_class()
        );
    } // end function first

    /**
     * Gets an instance of a Model
     *
     * @return Model
     */
    static function get()
    {
        self::setDB();
        return self::$db->get(
            self::tableName(),
            get_called_class()
        );
    } // end function get

    /**
     * Sets a where clause
     *
     * @param String or array $columnName The column name
     * @param String          $value      The column value
     *
     * @return Db
     */
    static function where($columnName, $value = null)
    {
        self::setDB();
        return self::$db->where($columnName, $value);
    } // end static function where

    /**
     * Sets and OR statement
     *
     * @param String $columnName The column name
     * @param Object $value      The column value
     *
     * @return Db
     */
    static function orWhere($columnName, $value)
    {
        self::setDB();
        return self::$db->orWhere($columnName, $value);
    } // end static function orWhere

    /**
     * Adds an order by statement to the current select statement
     *
     * @param String      $column  The column name
     * @param String|null $ascDesc ASC|DESC|null
     *
     * @return Db
     */
    static function orderBy($column, $ascDesc = null)
    {
        self::setDB();
        return self::$db->orderBy($column, $ascDesc);
    } // end static function orderBy

    /**
     * Sets an "TOP" or "LIMIT" clause
     *
     * @param Int      $limit   The number of rows to return
     * @param Int|null $startAt The initial row
     *
     * @return Db
     */
    static function top($limit, $startAt = null)
    {
        self::setDB();
        return self::$db->top($limit, $startAt);
    } // end static function top

    /**
     * Set the static properti DB to a default connection
     *
     * @return void
     */
    protected static function setDB()
    {
        if (self::$db === null ) {
            self::$db = new Db();
        }
        self::$db->from(self::tableName());
        self::$db->setReturnClass(get_called_class());
    } // end protected static function setDB

    /**
     * Removes the db undefined
     *
     * @param Array $queryParams The parameters to remove the nulls
     *
     * @return null
     */
    protected static function removeUndefined( $queryParams )
    {
        foreach( $queryParams as $key => $value ) {
            if (
                gettype( $value ) == "object" &&
                get_class( $value ) == "RSPhp\Framework\Undefined"
            ) {
                unset( $queryParams[$key] );
            } // end if object
        }

        return $queryParams;
    } // end protected static function removeUndefined

} // end class Model
