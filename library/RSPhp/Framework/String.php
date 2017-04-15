<?php
/**
 * String.php
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
 * Helper for string manipulation
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
class String
{
    /**
     * Determines is the string is upper
     *
     * @param String $string The string to verify
     *
     * @return String
     */
    static function isUppercase( $string )
    {
        return ctype_upper($string);
    } // end function isUppercase

    /**
     * Returns true if $str contains $val
     *
     * @param String $str The string container
     * @param String $val The string to search in the container
     *
     * @return Boolean
     */
    static function contains( $str, $val )
    {
        return ( strpos($str, $val) !== false );
    } // end function contains

    /**
     * Determines if a string starts with another
     *
     * @param String $haystack The string to search in
     * @param String $needle   The string to search for
     *
     * @return Boolean
     */
    static function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return
            $needle === "" ||
            strrpos($haystack, $needle, -strlen($haystack)) !== false;
    } // end function startsWith

    /**
     * Determines if a string ends with another
     *
     * @param String $haystack The string to search in
     * @param String $needle   The string to search for
     *
     * @return Boolean
     */
    static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" ||
            (($temp = strlen($haystack) - strlen($needle)) >= 0 &&
            strpos($haystack, $needle, $temp) !== false);
    } // end function endsWith

    /**
     * Returns the left part of an string
     *
     * @param String $str    The string to manipulate
     * @param Int    $length The number of characters to return
     *
     * @return String
     */
    static function left($str, $length)
    {
        return substr($str, 0, $length);
    } // end function left

    /**
     * Returns the right part of an string
     *
     * @param String $str    The string to manipulate
     * @param Int    $length The number of characters to return
     *
     * @return String
     */
    static function right($str, $length)
    {
        return substr($str, -$length);
    } // end function right

    /**
     * Remove the strip accents from a string
     *
     * @param String $string The string to modify
     *
     * @return String The string modified
     */
    static function stripAccents( $string )
    {
        $string = str_replace("�", "a", $string);
        $string = str_replace("�", "e", $string);
        $string = str_replace("�", "i", $string);
        $string = str_replace("�", "o", $string);
        $string = str_replace("�", "u", $string);
        $string = str_replace("�", "A", $string);
        $string = str_replace("�", "E", $string);
        $string = str_replace("�", "I", $string);
        $string = str_replace("�", "O", $string);
        $string = str_replace("�", "U", $string);
        $string = str_replace("�", "n", $string);
        $string = str_replace("�", "N", $string);
        return $string;
    } // end function stripAccents

    /**
     * Replaces the key with the value of $dictionary in $string
     *
     * @param Array  $dictionary Dictionary with key and value
     * @param String $string     The string in which the replacements
     * are gonna be made
     *                  are gonna be made
     *
     * @return String
     */
    static function stringReplace( $dictionary, $string )
    {
        foreach ($dictionary as $key => $value) {
            $string = str_replace($key, $value, $string);
        } // end foreach $dictionary
        return $string;
    } // end function stringReplace

    /**
     * Converts special chars to HTML equivalents
     *
     * @param String $string The string to converts
     *
     * @return String
     */
    static function specialCharsToHTML( $string )
    {
        $search = array(
          'á', 'é', 'í', 'ó', 'ú',
          'Á', 'É', 'Í', 'Ó', 'Ú',
          'ñ', 'Ñ', '¿', '¡'
        );
        $replace = array(
          '&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;',
          '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute;',
          '&ntilde;', '&Ntilde;', '&iquest;', '&iexcl;'
        );

        $string = str_replace($search, $replace, $string);
        return $string;
    } // end function specialCharsToHTML

    /**
     * Converts to upper including accents
     *
     * @param String $string The string to modify
     *
     * @return String
     */
    static function toUpper( $string )
    {

        $string = strtoupper($string);

        $search = array(
          'á', 'é', 'í', 'ó', 'ú', 'ñ'
        );
        $replace = array(
          'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'
        );

        $string = str_replace($search, $replace, $string);
        return $string;
    } // end function to Upper

    /**
     * Converts to lower including accents
     *
     * @param String $string The string to modify
     *
     * @return String
     */
    static function toLower( $string )
    {

        $string = strtoupper($string);

        $search = array(
          'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'
        );
        $replace = array(
          'á', 'é', 'í', 'ó', 'ú', 'ñ'
        );

        $string = str_replace($search, $replace, $string);
        return $string;
    } // end function toLower

    /**
     * Return a chunked string in unicode
     *
     * @param String $str The string to modify
     * @param Int    $l   The number of chunks
     * @param String $e   The separator
     *
     * @return String
     */
    static function chunkSplitUnicode($str, $l = 76, $e = "\r\n")
    {
        $tmp = array_chunk(
            preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $l
        );
        $str = "";
        foreach ($tmp as $t) {
            $str .= join("", $t) . $e;
        }
        return $str;
    } // end function chunkSplitUnicode

    /**
     * Returns a GUID String
     *
     * @return String
     */
    static function GUID()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            //$uuid = chr(123)// "{"
            $uuid = ""
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid, 12, 4).$hyphen
            .substr($charid, 16, 4).$hyphen
            .substr($charid, 20, 12);
            //.chr(125);// "}"
            return $uuid;
        }
    } // end function GUID

    /**
     * Replaces a string within another
     *
     * @param String $search  The string to search
     * @param String $replace The string to replace
     * @param String $string  The string to search in
     *
     * @return String
     */
    static function replace( $search, $replace, $string )
    {
        $string = str_replace($search, $replace, $string);
        return $string;
    } // end function  replace

    /**
     * Returns a random string of $len characters
     *
     * @param Int     $len        The length of the desired string
     * @param Boolean $useSymbols Indicates if the return string will have symbols
     *
     * @return String
     */
    static function random( $len, $useSymbols = false )
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($useSymbols ) {
            $chars .= "°!#$%&/()=?¡*][{}-.,;:_";
        } // end if use symbols

        $charsLen = strlen($chars);
        $charsLen--;

        $random = '';
        $count = 0;
        while ( $count < $len ) {
            $rand = rand(0, $charsLen);
            $random .= $chars[$rand];
            $count++;
        } // end while

        return $random;
    } // end function random

    /**
     * Determines if is a string is base 64
     *
     * @param String $str The string to verify
     *
     * @return Boolean
     */
    static function isBase64( $str )
    {
        if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $str)) {
            return true;
        } else {
            return false;
        }
    } // end function isBase64

    /**
     * Trims a string
     *
     * @param String $string The string to trim
     *
     * @return String
     */
    public static function trim( $str, $charMask = " \t\n\r\0\x0B" )
    {
        return trim( $str, $charMask );
    } // end function trim
} // end class
