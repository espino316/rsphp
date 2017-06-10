<?php
/**
 * File.php
 *
 * PHP Version 5
 *
 * File File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use Exception;

/**
 * Helper for file management
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
class File
{

    /**
     * Verify a file exits
     *
     * @param String $file The file name
     *
     * @return Boolean
     */
    public static function exists( $file )
    {
        return file_exists($file);
    } // end function exists

    /**
     * Reads a file and return its content
     *
     * @param String $file The file path
     *
     * @return String
     */
    public static function read( $file )
    {
        if ( ! file_exists( $file ) ) {
            throw new Exception( "File $file do not exists" );
        } // end if not exists
        return file_get_contents($file);
    } // end function read

    /**
     * Write a file
     *
     * @param String $file    The file path
     * @param String $content The content of the file
     * @param bool   $append  Indicates if append to file
     *
     * @return String
     */
    public static function write( $file, $content = "", $append = false )
    {
        if ($append ) {
            file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents($file, $content, LOCK_EX);
        }
    } // end function write

    /**
     * Deletes a file
     *
     * @param String $file The file to delete
     *
     * @return void
     */
    public static function delete( $file )
    {
        if (file_exists($file) ) {
            unlink($file);
        }
    } // end function delete

    /**
     * Copy a file
     *
     * @param String $fileSrc  The file to copy
     * @param String $fileDest The destination path
     *
     * @return void
     */
    public static function copy( $fileSrc, $fileDest )
    {
        if (!file_exists($fileSrc) ) {
            throw new Exception("File source do not exists ($fileSrc)", 1);
        } // end if !file exists
        copy($fileSrc, $fileDest);
    } // end function copy

    /**
     * Moves or renames a file
     *
     * @param String $fileSrc  The file to move
     * @param String $fileDest The destination path
     *
     * @return void
     */
    public static function move( $fileSrc, $fileDest )
    {
        if (!file_exists($fileSrc) ) {
            throw new Exception("File source do not exists ($fileSrc)", 1);
        } // end if !file exists
        rename($fileSrc, $fileDest);
    } // end function move

    /**
     * Returns the extension from a file
     *
     * @param String $fileName The full file name
     *
     * @return String
     */
    public static function getExtension( $fileName )
    {
        return end(( explode(".", $fileName) ));
    } // end function getExtension

    /**
     * Write a file to the response
     *
     * @param String $file The file name
     *
     * @return void
     */
    public static function writeToResponse( $file )
    {
        $type = mime_content_type($file);
        $name = basename($file);
        header("Content-type:application/$type");
        header("Content-Disposition:attachment;filename=$name");
        ob_end_clean();
        readfile($file);
    } // end function writeToResponse

} // end class File
