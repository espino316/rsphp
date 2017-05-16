<?php
/**
 * Directory.php
 *
 * PHP Version 5
 *
 * Directory File Doc Comment
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
 * Helper for directory management
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
class Directory
{

    /**
     * Verify a file exits
     *
     * @param String $file The file path to verify existence
     *
     * @return Boolean
     */
    public static function exists( $file )
    {
        return file_exists($file);
    } // end function exists

    /**
     * Return full path files (only files) from directory
     *
     * @param String     $dir        The directory to search for files
     * @param Array|null $extensions An array with extension to filter the search
     *
     * @return Array
     */
    public static function getFiles( $dir, $extensions = null )
    {

        if (self::exists($dir) ) {
            //  Array of string representing files
            $result = array();

            //  Get the files with scandir
            $files = scandir($dir);

            //  Adds the directory
            //  Filter directories, return only files
            $continue = 0;
            foreach ( $files as $file ) {

                if ($file == '.' || $file == '..' ) {
                    continue;
                } // end if $file not . nor ..

                if ($extensions ) {
                    $continue = 1;
                    foreach ( $extensions as $ext ) {
                        if (Str::endsWith($file, $ext) ) {
                            $continue = 0;
                            continue;
                        } // end if
                    } // end foreach
                } // end if extensions

                if ($continue ) {
                    continue;
                } // end if continue

                //  Adds the directory
                $file = $dir.DS.$file;

                if (is_dir($file) ) {
                    continue;
                } // end if is_idr

                //  Add the file to the array
                $result[] = $file;
            } // end foreach $file

            return $result;
        } else {
            echo "file not exists $dir\n";
            return null;
        } // end if directory exists
    } // end function getFiles

    /**
     * Delete a directory
     *
     * @param String  $dir       The directory (full path)
     * @param Boolean $recursive (default false) Indicates if remove inner files and
     * directories too.
     *
     * @return void
     */
    public static function delete( $dir, $recursive = false )
    {

        if ($recursive ) {
            if (!is_dir($dir) ) {
                return false;
            }

            $files = scandir($dir);

            foreach ( $files as $file ) {
                if ($file == '.' || $file == '..' ) {
                    continue;
                }
                $file = $dir.DS.$file;

                if (is_dir($file) ) {
                    self::delete($file, true);
                } else {
                    unlink($file);
                } // end if is dir
            } // end foreach
        }

        //  remove directory
        rmdir($dir);
    } // end function delete

    /**
     * Creates a directory
     *
     * @param String $dir The directory path to create
     *
     * @return void
     */
    public static function create( $dir )
    {
        if (file_exists($dir) ) {
            throw new Exception("Directory already exists", 1);
            return;
        } // end if file exists
        mkdir($dir);
    } // end function create

    /**
     * Return full path directories (only directories) from directory
     *
     * @param String $dir The directory to search for subdirectories
     *
     * @return Array
     */
    public static function getDirectories( $dir )
    {

        //  Array of string representing files
        $result = array();

        //  Get the files with scandir
        $files = scandir($dir);

        //  Adds the directory
        //  Filter directories, return only files
        foreach ( $files as $file ) {

            if ($file == '.' || $file == '..' ) {
                continue;
            } // end if $file not . nor ..

            //  Adds the directory
            $file = $dir.DS.$file;

            if (!is_dir($file) ) {
                continue;
            } // end if is_idr

            //  Add the file to the array
            $result[] = $file;
        } // end foreach $file

        return $result;
    } // end function getDirectories
} // end class Directory
