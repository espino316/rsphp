<?php
/**
 * Export.php
 *
 * PHP Version 5
 *
 * Db File Doc Comment
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
use InvalidArgumentException;
use stdClass;

/**
 * Helper for exporting functions
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
class Export
{
    /**
     * Return or prints HTML output for a resultset (array or assoc_arrays)
     *
     * @param $resultSet Array The Array of assoc arrays
     * @param $return Boolean Optional Indicates if must return the HTML string
     *
     * @return String|null
     *
     */
    static function toHTML( $resultSet, $return = false )
    {
        if ( ! $resultSet ) {
            throw new Exception( "No data to export" );
        } // end if not resultSet

        if ( ! is_array( $resultSet ) ) {
            throw new Exception( "Data must be an array" );
        } // end if no array

        $header = $resultSet[0];

        if ( ! is_array( $header ) ) {
            throw new Exception( "Data must be an array of assoc arrays" );
        } // end if no array

        $html = Html::dataTable( $resultSet, null, true );

        if ( $return ) {
            return $html;
        } // end if return

        echo $html;
    } // end function

    static function toExcel( $resultSet, $fileName )
    {
        $html = self::toHTML( $resultSet, true );
        ob_end_clean();
        header( "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" );
        header( 'Content-Disposition: attachment; filename="'.$fileName.'.xlsx"' );
        echo $html;
    } // end function toExcel
} // end class Db
