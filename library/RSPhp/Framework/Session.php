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

    private static $data;

    public static $cookieName = "sesdat";

    /**
     * Load the cookie into $data
     *
     * @return void
     */
    private static function load()
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
                self::$data
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
                self::$data = array();
            } // end else
        } else {
            //	There is session, then decrypt session and pass to array
            $crypt = new Crypt();
            self::$data
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

    } // end load

    /**
     * Write a key value pair to $data and set the cookie
     *
     * @param String $itemKey   The item's key
     * @param Object $itemValue The item's value
     *
     * @return void
     */
    static function set($itemKey, $itemValue)
    {
        //	Load the array
        self::load();
        //	Set the item
        self::$data[$itemKey] = $itemValue;
        self::$data['clear'] = false;
        //	Crypt
        $crypt = new Crypt();
        $str = json_encode(self::$data);
        $val = $crypt->tripleDesEncrypt(
            json_encode(self::$data)
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
     * Retrives a value from $data
     *
     * @param String $itemKey The item's key to return
     *
     * @return mixed[]
     */
    static function get($itemKey = null)
    {

        //	Load the array
        self::load();
        //	If no request specific key
        if ($itemKey == null ) {
            //	Return the whole array
            return self::$data;
        } else {
            //	Else, return value, if exists
            if (empty(self::$data) ) {
                return null;
            } else {
                if (array_key_exists($itemKey, self::$data)) {
                    return self::$data[$itemKey];
                } else {
                    return null;
                }
            } // end if then else empty data
        } // end if then else itemkey null
    } // end function

    /**
     * Remove a cookie
     *
     * @param String $name
     *
     * @return Null
     */
    static function remove($name)
    {
        //	Load the array
        self::load();

        //	remove the item
        unset(self::$data[$itemKey]);
        self::$data['clear'] = false;

        //	Crypt
        $crypt = new Crypt();
        $str = json_encode(self::$data);
        $val = $crypt->tripleDesEncrypt(
            json_encode(self::$data)
        );

        //	Set session
        $_SESSION[self::$cookieName] = $val;

        //	Set cookie
        setcookie(
            self::$cookieName,
            $_SESSION[self::$cookieName],
            time() + 3600
        );
    } // end function function remove

    /**
     * Remove the cookie data, destroy the session
     *
     * @return void
     */
    static function clear()
    {

        //	Load the array
        self::load();
        self::$data = null;
        //	Set the item
        self::$data['clear'] = true;
        //	Crypt
        $crypt = new Crypt();
        $str = json_encode(self::$data);
        $val = $crypt->tripleDesEncrypt(
            json_encode(self::$data)
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
            json_encode(self::$data)
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
        self::$data
            = json_decode(
                $crypt->tripleDesDecrypt(
                    $_SESSION[self::$cookieName]
                ),
                true
            );
    } // end function setRaw

    public static function auth()
    {
        if (!self::get(self::get("__rs_keyAuth__"))) {
            Uri::redirect(self::get("__rs_redirectAuthUrl__"));
        } // end if not $key
    } // end function validate session

    public static function setAuth($key, $redirectUrl)
    {
        self::set("__rs_keyAuth", $key);
        self::set("__rs_redirectAuthUrl__", $redirectUrl);
    } // end function setValidation

    /**
     * Validates the input values
     *
     * @return Boolean
     */
    public static function validate( $rules ) {
        $val = new Validation();
        foreach( $rules as $key => $value ) {
            $val->addRule( $key, $value );
        } // end foreach

        if ( ! $val->validate( self::get() ) ) {
            throw new Exception(
                $val->getErrors()
            );
        } // end if validate

        return true;
    } // end function validates
} // end class
