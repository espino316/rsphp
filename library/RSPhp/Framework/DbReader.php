<?php

namespace RSPhp\Framework;

use PDO;
/**
 * Reads data one row at the time
 */
class DbReader
{
    //  The PDO statement
    private $_statement;
    //  The class name
    private $_className;
    //  The db conn
    private $_db;

    //  Indicates is db is utf8
    public $utf8 = false;
    /**
     * Reads one record
     *
     * @return mixed|null
     */
    public function read()
    {
        if ($this->_className !== null) {
            $this->_statement->setFetchMode( PDO::FETCH_CLASS, $this->_className );
            $result = $this->_statement->fetch();
            if ( !$result ) {
                $this->_disconnect();
                return null;
            } // end if not result
            return $result;
        } else {

            if ($this->_db->returnClassName !== null ) {
                $this->_statement->setFetchMode(
                    PDO::FETCH_CLASS,
                    $this->_db->returnClassName
                );
                $result = $this->_statement->fetch();
                if ( !$result ) {
                    $this->_disconnect();
                    return null;
                } // end if not result
                return $result;
            } else {
                $result = $this->_statement->fetch(PDO::FETCH_ASSOC);
                if ( !$result ) {
                    $this->_disconnect();
                    return null;
                } // end if not result

                $v = $result;
                $result = array();
                foreach ( array_keys($v) as $colName ) {
                    $result[$colName] = $v[$colName];
                }
                return $result;
            } // end if returnClassName
        } // end if className
    } // end function read

    /**
     * Disconnects
     *
     * @return null
     */
    private function _disconnect()
    {
        //	Disconnect
        $this->_db->conn = null;
        $this->_db->clear();
    } // end private function _disconnect

    /**
     * Creates a new instance of DbReader
     */
    public function __construct( $db, $sql, $queryParams = null, $className = null)
    {
        $this->_className = $className;
        $this->_db = $db;

        //	remove nulls
        if ($queryParams != null ) {
            foreach ( array_keys($queryParams) as $key ) {
                if ($queryParams[$key] == null
                    && ! is_numeric($queryParams[$key])
                    && $queryParams[$key] !== 0
                ) {
                    unset($queryParams[$key]);
                }
            } // end foreach key
        } // end if $queryParams is null

        //	Connect
        $db->connect();

        //	Prepare
        $this->_statement
            = $db->conn->prepare(
                $sql,
                array( PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)
            );

        //	If logSql, then log Sql
        if (App::get('logSql') ) {
            Logger::sql($sql);
            Logger::sql($queryParams);
        } // end if App::get('logSql')

        //	Desicion if params
        if ($queryParams != null ) {
            $this->_statement->execute($queryParams);
        } else {
            $this->_statement->execute();
        } // end if queryParams
    } // end function __construct
} // end class DbReader
