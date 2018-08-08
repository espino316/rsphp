<?php
/**
 * Controller.php
 *
 * PHP Version 5
 *
 * Controller File Doc Comment
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
 * Controller for the MVC design pattern
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
class Controller
{

    /**
     * Creates an instance of Controller
     *
     * @return void
     */
    function __construct()
    {
    } // end function __construct

    /**
     * Prints an array or object as Xml
     *
     * @param mixed[] $data The data to convert to xml and print to the response
     *
     * @return void
     */
    function xmlResponse( $data )
    {
        if (App::get('allowCORS') ) {
            $this->setCORSHeaders();
        } // end if allowCORS
        Output::xml( $data );
    } // end function xmlResponse

    /**
     * Prints an array or object as Json
     *
     * @param mixed[] $data The data to convert to xml and print to the response
     *
     * @return void
     */
    function jsonResponse( $data )
    {
        if (App::get('allowCORS') ) {
            $this->setCORSHeaders();
        } // end if allowCORS
        Output::json( $data );
    } // end function jsonResponse

    /**
     * Handles an exception
     *
     * @param Exception $ex The exception to manage
     *
     * @return void
     */
    function handleException( $ex )
    {
        //  Catch exceptions, show messages
        RS::handleException($ex);
    } // end function handleExeption

    /**
     * Sets the CORS Headers
     *
     * @return void
     */
    function setCORSHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT');
        header(
            'Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Headers, ' .
            'Origin,Accept, X-Requested-With, Content-Type, '.
            'Access-Control-Request-Method, Access-Control-Request-Headers'
        );
    } // end function setCORS

    /**
     * Loads a view with the default Header and Footer
     *
     * @param String $viewName The name of the view
     * @param Array $data The data to pass to the view
     *
     * @return Null
     */
    function loadContent($viewName, $data = null)
    {
        View::load('Header', $data);
        View::load($viewName, $data);
        View::load('Footer', $data);
    } // end function loadContent

    /**
     * Return session "hasError"
     *
     * @return Boolean
     */
    function hasError()
    {
        return Session::get("__rs__hasError__");
    }

    /**
     * Returns the errorMessage
     * Clears the message
     *
     * @return Null
     */
    function getError()
    {
        $errorMessage = Session::get("__rs__errorMessage__");
        Session::remove("__rs__hasError__");
        Session::remove("__rs__errorMessage__");
        return $errorMessage;
    } // end function getError

    /**
     * Sets an error
     *
     * @param String $errorMessage
     *
     * @return Null
     */
    function setError($errorMessage)
    {
        Session::set("__rs__hasError__", true);
        Session::set("__rs__errorMessage__", $errorMessage);
    } // end function setError
} // end class Controller

