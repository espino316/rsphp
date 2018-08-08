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
date_default_timezone_set('America/Mexico_City');
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
    static $folder = ROOT.DS.'logs'.DS;

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
     * Writes to the specific log
     *
     * @param string $log The log to write
     * @param mixed[] $text The text or array to log
     *
     * @return void
     */
    private static function writeLog( $log, $text)
    {
        try {
            $filename = self::$folder . $log . '_log';
            if (is_writable( self::$folder ) ) {
                if (is_array($text) ) {
                    $text = print_r($text, true);
                }
                $text = date('Y-m-d H:i:s') . "\t" . $text . "\n";
                file_put_contents($filename, $text, FILE_APPEND | LOCK_EX);
            } else {
                error_log( print_r( $text, true ) );
            } // end if then else is writable
        } catch (Exception $ex) {
            error_log( print_r( $text, true ) );
            error_log($ex->getMessage());
        } // end try catch
    } // end function debugSql

    /**
     * Writes to the debug log
     *
     * @param mixed[] $text The text|array to log
     *
     * @return void
     */
    static function debug( $text )
    {
        self::writeLog( "debug", $text );
    } // end function debug

    /**
     * Writes to the info log
     *
     * @param mixed[] $text The text|array to log
     *
     * @return void
     */
    static function info( $text )
    {
        self::writeLog( "info", $text );
    } // end function info

    /**
     * Writes to the error log
     *
     * @param mixed[] $text The text|array to log
     *
     * @return void
     */
    static function error( $text )
    {
        self::writeLog( "error", $text );
    } // end function debug

    /**
     * Writes to the sql log
     *
     * @param mixed[] $text The text|array to log
     *
     * @return void
     */
    static function sql( $text )
    {
        self::writeLog( "sql", $text );
    } // end function debug

    /**
     * Prints the log content
     *
     * @return void
     */
    static function show( $log )
    {
        $filename = $folder.$log."_log";
        echo file_get_contents($filename);
    } // end function showDebug

} // end class Logger
