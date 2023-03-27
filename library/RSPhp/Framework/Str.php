<?php
/**
 * Str.php
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
class Str
{
    /**
     * Determines is the string is upper
     *
     * @param string $str The string to verify
     *
     * @return string
     */
    static function isUppercase( $str )
    {
        return ctype_upper($str);
    } // end function isUppercase

    /**
     * Returns true if $str contains $val
     *
     * @param string $str The string container
     * @param string $val The string to search in the container
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
     * @param string $haystack The string to search in
     * @param string $needle   The string to search for
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
     * @param string $haystack The string to search in
     * @param string $needle   The string to search for
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
     * @param string $str    The string to manipulate
     * @param int    $length The number of characters to return
     *
     * @return string
     */
    static function left($str, $length)
    {
        return substr($str, 0, $length);
    } // end function left

    /**
     * Returns the right part of an string
     *
     * @param string $str    The string to manipulate
     * @param int    $length The number of characters to return
     *
     * @return string
     */
    static function right($str, $length)
    {
        return substr($str, -$length);
    } // end function right

    /**
     * Remove the strip accents from a string
     *
     * @param string $str The string to modify
     *
     * @return string The string modified
     */
    static function stripAccents( $str )
    {
        $str = str_replace("�", "a", $str);
        $str = str_replace("�", "e", $str);
        $str = str_replace("�", "i", $str);
        $str = str_replace("�", "o", $str);
        $str = str_replace("�", "u", $str);
        $str = str_replace("�", "A", $str);
        $str = str_replace("�", "E", $str);
        $str = str_replace("�", "I", $str);
        $str = str_replace("�", "O", $str);
        $str = str_replace("�", "U", $str);
        $str = str_replace("�", "n", $str);
        $str = str_replace("�", "N", $str);
        return $str;
    } // end function stripAccents

    /**
     * Replaces a string within another
     *
     * @param string $search  The string to search
     * @param string $replace The string to replace
     * @param string $str  The string to search in
     *
     * @return string
     */
    static function replace($search, $replace, $str = null)
    {
        if ( is_array( $search ) && ! $str ) {
            return self::dictReplace( $search, $replace );
        } // end if is array and no str
        $str = str_replace($search, $replace, $str);
        return $str;
    } // end function  replace

    /**
     * Replaces the key with the value of $dictionary in $str
     *
     * @param Array  $dictionary Dictionary with key and value
     * @param string $str     The string in which the replacements
     * are gonna be made
     *
     * @return string
     */
    private static function dictReplace( $dictionary, $str )
    {
        foreach ($dictionary as $key => $value) {
            $str = str_replace($key, $value, $str);
        } // end foreach $dictionary
        return $str;
    } // end function stringReplace

    /**
     * Converts special chars to HTML equivalents
     *
     * @param string $str The string to converts
     *
     * @return string
     */
    static function specialCharsToHTML( $str )
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

        $str = str_replace($search, $replace, $str);
        return $str;
    } // end function specialCharsToHTML

    /**
     * Converts to upper including accents
     *
     * @param string $str The string to modify
     *
     * @return string
     */
    static function toUpper( $str )
    {

        $str = strtoupper($str);

        $search = array(
          'á', 'é', 'í', 'ó', 'ú', 'ñ'
        );
        $replace = array(
          'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'
        );

        $str = str_replace($search, $replace, $str);
        return $str;
    } // end function to Upper

    /**
     * Converts to lower including accents
     *
     * @param string $str The string to modify
     *
     * @return string
     */
    static function toLower( $str )
    {

        $str = strtolower($str);

        $search = array(
          'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'
        );
        $replace = array(
          'á', 'é', 'í', 'ó', 'ú', 'ñ'
        );

        $str = str_replace($search, $replace, $str);
        return $str;
    } // end function toLower

    /**
     * Return a chunked string in unicode
     *
     * @param string $str The string to modify
     * @param int    $l   The number of chunks
     * @param string $e   The separator
     *
     * @return string
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
     * Returns a GUID Str
     *
     * @return string
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
     * Returns a random string of $len characters
     *
     * @param int     $len        The length of the desired string
     * @param boolean $useSymbols Indicates if the return string will have symbols
     *
     * @return string
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
     * @param string $str The string to verify
     *
     * @return boolean
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
     * @param string $str The string to trim
     *
     * @return string
     */
    public static function trim($str, $charMask = " \t\n\r\0\x0B")
    {
        return trim( $str, $charMask );
    } // end function trim

    /**
     * Return regex matches
     *
     * @param string $pattern The regex pattern
     * @param string $str String The string to parse
     *
     * @return Array
     */
    public static function pregMatchAll($pattern, $str)
    {
        preg_match_all($pattern, $str, $matches);
        return $matches[0];
    } // end function pregMatchAll

    /**
     * Converts a string in camelCase
     * @param string $delimiter The delimiter to use, "-", "_", " ", etc
     * @param string $str The string to convert, e.g. "camel_case"
     *
     * @return string
     */
    public static function toCamelCase($delimiter, $str)
    {
        $str = str_replace($delimiter, '', ucwords($str, $delimiter));
        return lcfirst($str);
    } // end function toCamel

    /**
     * Converts a string in PascalCase
     * @param string $delimiter The delimiter to use, "-", "_", " ", etc
     * @param string $str The string to convert, e.g. "pascal_case"
     *
     * @return string
     */
    public static function toPascalCase($delimiter, $str)
    {
        $str = str_replace($delimiter, '', ucwords($str, $delimiter));
    } // end function toPascalCase

    /**
     * Return the lenght of a string
     *
     * @param string $str The string to calculate its length
     *
     * @return int
     */
    public static function len($str)
    {
        return strlen($str);
    } // end function len
} // end class
