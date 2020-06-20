<?php

namespace RSPhp\Framework;

/**
 * Gets and sets environment variable
 */
class Env {

    /**
     * Gets an environment variable
     *
     * @param $varName The name of the environment variable
     *
     * @return Object
     */
    static function get($varName)
    {
        if (!$varName) {
            return getenv();
        } // end if not varname

        $result = getenv($varName, true) ?: getenv($varname);

        $result = unserialize($result);

        return $result;
    } // end function getVar

    /**
     * Sets an environment variable
     *
     * @param $varName The name of the environment variable
     * @param $value The value of the environment variable
     *
     * @return null
     */
    static function set($varName, $value)
    {
        $value = serialize($value);
        $put = "$varName=$value";
        putenv($put);
    } // end function setVar

    /**
     * Removes an environment variable
     *
     * @param $varName The name of the environment variable to remove
     */
    public static function remove($varName)
    {
        putenv($varName);
    } // end function remove
} // end class Env
