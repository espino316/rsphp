<?php
/**
 * Xml.php
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

use SimpleXmlElement;

/**
 * Helper for XML manipulation
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
class Xml
{
    private static $_baseElement = "<?xml version=\"1.0\"?><data></data>";

    /**
     * Returns Xml String from an array
     *
     * @param Array $data The array to convert
     *
     * @return String
     */
    public static function getString( $data )
    {
        $xmlData = new SimpleXMLElement( self::$_baseElement );
        self::_arrayToXml($data, $xmlData);
        return $xmlData->asXML();
    } // end function getString

    /**
     * Convert an array to xml
     *
     * @param Array  $data    The data to convert to xml
     * @param String $xmlData The xml data converted ( Referenced )
     *
     * @return void
     */
    private static function _arrayToXml( $data, &$xmlData )
    {
        foreach ( $data as $key => $value ) {
            if (is_object($value) ) {
                $value = (array) $value;
            }

            if (is_array($value) ) {
                if (is_numeric($key) ) {
                    $key = 'item'.$key;
                }
                $subnode = $xmlData->addChild($key);
                self::arrayToXml($value, $subnode);
            } else {
                if (strpos($key, '*') === false) {
                    $xmlData->addChild("$key", htmlspecialchars("$value"));
                } // end if *
            } // end if then else is numeric
        } // end if then else is array
    } // end function arrayToXml
} // end class Xml
