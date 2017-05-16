<?php
/**
 * Route.php
 *
 * PHP Version 5
 *
 * Route File Doc Comment
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
 * Represents a route
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
class Route
{

    public $method;
    public $uri;
    public $newUri;

    /**
     * Construction of the Route
     *
     * @param String $method * for all, 'GET', 'POST', 'PUT', 'DELETE'
     * @param String $uri    The uri origin
     * @param String $newUri The uri target
     *
     * @return Route
     */
    function __construct(
        $method,
        $uri,
        $newUri
    ) {

        $this->method = $method;
        $this->uri = $uri;
        $this->newUri = $newUri;

    } // end construct

    /**
     * Construct the Route from a url
     *
     * @param String $url The url to compare
     *
     * @return void
     */
    function match( $url )
    {

        if (Str::contains($this->uri, ":") ) {
            $segments = explode("/", $this->uri);
            $newSegments = explode("/", $this->newUri);
            $urlSegments = explode("/", $url);

            $segments = array_filter($segments);
            $newSegments = array_filter($newSegments);
            $urlSegments = array_filter($urlSegments);

            $pattern = "";
            foreach ($segments as $key => $value) {
                if (!empty($pattern) ) {
                    $pattern.="\\/";
                }
                if (Str::contains($value, ":") ) {
                    $pattern .= "(\d+)";
                } else {
                    $pattern .= $value;
                }
            } // end foreach
            $pattern="/".$pattern."/";

            if (preg_match($pattern, $url) ) {
                $patterns = array();
                $replacements = array();
                foreach ( $segments as $key => $value ) {
                    if (Str::contains($value, ":") ) {
                        $segments[$key] = $urlSegments[$key];
                        $patterns[] = "/$value/";
                        $replacements[] = $urlSegments[$key];
                    } // end if
                } // end foreach
                $this->newUri
                    = preg_replace($patterns, $replacements, $this->newUri);
                $this->uri
                    = preg_replace($patterns, $replacements, $this->uri);
            } // end if preg_match
        } // end if contains ":"
    } // end function getUrl
} // end class
