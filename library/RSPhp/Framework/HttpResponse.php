<?php
/**
 * HttpResponse.php
 *
 * PHP Version 5
 *
 * Http Doc Comment
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
 * Contains a web response
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
class HttpResponse
{
    public $code;
    public $headers;
    public $data;

    /**
     * Creates an instance of HttpResponse
     *
     * @param Mixed $data The actual response data
     * @param Array $headers The headers returned in the response
     * @param String $code The http code returned in the response
     */
    public function __construct( $data, $headers = null, $code = null )
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->code = $code;
    } // end function __construct
} // end class HttpResponse
