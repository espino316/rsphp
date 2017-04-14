<?php
/**
 * Session.php
 *
 * PHP Version 5
 *
 * Session File Doc Comment
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
 * Accesses session variables
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
class Session
{

    private static $_data;

    public static $cookieName = "sesdat";

    /**
     * Load the cookie into $_data
     *
     * @return void
     */
    private static function _load()
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        //	If no session
        if (!isset($_SESSION[self::$cookieName])) {

            //	but there is cookie
            if (isset($_COOKIE[self::$cookieName]) ) {

                //	Decrypt the cookie
                //	and set the array
                $crypt = new Crypt();
                self::$_data
                    = json_decode(
                        $crypt->tripleDesDecrypt(
                            $_COOKIE[self::$cookieName]
                        ),
                        true
                    );
                //	Pass the cookie value to the session
                $_SESSION[self::$cookieName] = $_COOKIE[self::$cookieName];
            } else {
                //	No session, no cookie, start session
                self::$_data = array();
            } // end else
        } else {
            //	There is session, then decrypt session and pass to array
            $crypt = new Crypt();
            self::$_data
                = json_decode(
                    $crypt->tripleDesDecrypt(
                        $_SESSION[self::$cookieName]
                    ),
                    true
                );
            //	Set cookie
            setcookie(
                self::$cookieName,
                $_SESSION[self::$cookieName],
                time() + ( 3600 * 24 )
            );
        } // end else

    } // end _load

    /**
     * Write a key value pair to $_data and set the cookie
     *
     * @param String $itemKey   The item's key
     * @param Object $itemValue The item's value
     *
     * @return void
     */
    static function set($itemKey, $itemValue)
    {

        //	Load the array
        self::_load();
        //	Set the item
        self::$_data[$itemKey] = $itemValue;
        self::$_data['clear'] = false;
        //	Crypt
        $crypt = new Crypt();
        $str = json_encode(self::$_data);
        $val = $crypt->tripleDesEncrypt(
            json_encode(self::$_data)
        );
        //	Set session
        $_SESSION[self::$cookieName] = $val;
        //	Set cookie
        setcookie(
            self::$cookieName,
            $_SESSION[self::$cookieName],
            time() + 3600
        );

    }

    /**
     * Retrives a value from $_data
     *
     * @param String $itemKey The item's key to return
     *
     * @return mixed[]
     */
    static function get($itemKey = null)
    {

        //	Load the array
        self::_load();
        //	If no request specific key
        if ($itemKey == null ) {
            //	Return the whole array
            return self::$_data;
        } else {
            //	Else, return value, if exists
            if (empty(self::$_data) ) {
                return null;
            } else {
                if (array_key_exists($itemKey, self::$_data)) {
                    return self::$_data[$itemKey];
                } else {
                    return null;
                }
            } // end if then else empty data
        } // end if then else itemkey null
    } // end function

    /**
     * Remove the cookie data, destroy the session
     *
     * @return void
     */
    static function clear()
    {

        //	Load the array
        self::_load();
        self::$_data = null;
        //	Set the item
        self::$_data['clear'] = true;
        //	Crypt
        $crypt = new Crypt();
        $str = json_encode(self::$_data);
        $val = $crypt->tripleDesEncrypt(
            json_encode(self::$_data)
        );
        //	Set session
        $_SESSION[self::$cookieName] = $val;
        //	Set cookie
        setcookie(
            self::$cookieName,
            $_SESSION[self::$cookieName],
            time() + 3600
        );
    } // end clear

    /**
     * Gets the raw data from the cookie
     *
     * @return String
     */
    static function getRaw()
    {
        //	Crypt
        $crypt = new Crypt();
        $row['name'] = 'PHPSESSID';
        $row['value'] = session_id();
        $result[] = $row;
        $row = array();
        $row['name'] = self::$cookieName;
        $row['value'] = $crypt->tripleDesEncrypt(
            json_encode(self::$_data)
        );
        $result[] = $row;
        return $result;
    } // end function getRaw

    /**
     * Sets raw data to the cookie
     *
     * @param String $rawData The raw data
     *
     * @return void
     */
    static function setRaw( $rawData )
    {
        $_SESSION[self::$cookieName] = $rawData;
        $crypt = new Crypt();
        self::$_data
            = json_decode(
                $crypt->tripleDesDecrypt(
                    $_SESSION[self::$cookieName]
                ),
                true
            );
    } // end function setRaw

} // end class
