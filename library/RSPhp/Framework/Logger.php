<?php
/**
 * Logger.php
 *
 * PHP Version 5
 *
 * Logger File Doc Comment
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
 * Logs helper
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
class Logger
{
    /**
     * Clear the log files
     *
     * @return void
     */
    static function clearLogs()
    {
        try {
            $filename = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'debugSql.txt';
            if (is_writable($filename) ) {
                file_put_contents($filename, "");
            } // end if is writable

            $filename = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'debug.txt';
            if (is_writable($filename) ) {
                file_put_contents($filename, "");
            } // end if is writable
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        } // end try catch
    } // end function clearLogs

    /**
     * Writes to the sql log
     *
     * @param mixed[] $text The text or array to log
     *
     * @return void
     */
    static function debugSql($text)
    {
        try {
            $filename = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'debugSql.txt';
            if (is_writable($filename) ) {
                if (is_array($text) ) {
                    $text = print_r($text, true);
                }
                $text = date('Y-m-d H:i:s') . '		' . $text . '
	';
                file_put_contents($filename, $text, FILE_APPEND | LOCK_EX);
            } else {
                error_log($text);
            } // end if then else is writable
        } catch (Exception $ex) {
            error_log($text);
            error_log($ex->getMessage());
        } // end try catch
    } // end function debugSql

    /**
     * Writes to the log
     *
     * @param mixed[] $text The text|array to log
     *
     * @return void
     */
    static function debug($text)
    {
        try {
            $filename = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'debug.txt';

            if (is_writable($filename) ) {
                if (is_array($text) || is_object($text) ) {
                    $text = print_r($text, true);
                }
                $text = date('Y-m-d H:i:s') . '		' . $text . '
	';
                file_put_contents($filename, $text, FILE_APPEND | LOCK_EX);
            } else {
                error_log($text);
            } // end if then else is writable
        } catch (Exception $ex) {
            error_log($text);
            error_log($ex->getMessage());
        } // end try catch
    } // end function debug

    /**
     * Prints the log content
     *
     * @return void
     */
    static function showDebug()
    {
        $filename = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'debug.txt';
        echo file_get_contents($filename);
    } // end function showDebug

    /**
     * Prints the sql log content
     *
     * @return void
     */
    static function showDebugSql()
    {
        $filename = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'debugSql.txt';
        echo file_get_contents($filename);
    } // end function showDebugSql
} // end class Logger
