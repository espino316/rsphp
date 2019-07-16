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
     * @return String
     */
    static function get($varName)
    {
        print_r($varName, getenv($varName, true));
        return getenv($varName, true) ?: getenv($varName);
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
        $put = "$varName=$value";
        putenv($put);
    } // end function setVar
} // end class Env
