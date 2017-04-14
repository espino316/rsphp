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

    /**
     * Writes data as xml to the response
     *
     * @param Array $data The data to writo to response in xml format
     *
     * @return void
     */
    static function xmlResponse( $data )
    {
        $xmlData = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
        self::arrayToXml($data, $xmlData);
        $result = $xmlData->asXML();
        ob_end_clean();
        header('Content-Type: application/xml');
        if (App::get('allowCORS') ) {
            $this->setCORSHeaders();
        }
        echo $result;
    } // end function xmlResponse

    /**
     * Convert an array to xml
     *
     * @param Array  $data    The data to convert to xml
     * @param String $xmlData The xml data converted ( Referenced )
     *
     * @return void
     */
    static function arrayToXml( $data, &$xmlData )
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
