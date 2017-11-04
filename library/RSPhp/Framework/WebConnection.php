<?php
/**
 * WebConnection.php
 *
 * PHP Version 5
 *
 * WebConnection File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

use RSPhp\Framework\HttpContentTypes;

namespace RSPhp\Framework;

/**
 * Represents a connection to a web service
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
class WebConnection
{

    public $endPoint;
    public $method;
    public $headers;
    public $parameters;
    public $contentType;

    /**
     * Creates an instance of WebConnection
     *
     * @param Array $options An array with the options parameters
     * to construct the connection.
     *
     * @return null
     */
    function __construct(
        $endPoint,
        $method = null,
        $headers = null,
        $parameters = null,
        $contentType = HttpContentTypes::UrlEncoded
    ) {
        if (is_array($endPoint) && $method === null) {
            $options = $endPoint;
            $this->endPoint = $options["endPoint"];
            $this->method = $options["method"];
            $this->headers = $options["headers"];
            $this->parameters = $options["parameters"];
            $this->contentType = $options["contentType"];
            return;
        } // end if endPoint is array and method is null

        $this->endPoint = $endPoint;
        $this->method = $method;
        $this->headers = $headers;
        $this->parameters = $parameters;
        $this->contentType = $contentType;

    } // end __construct

} // end class
