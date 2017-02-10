<?php
namespace Application\Controllers;

use Espino\RSPhp\Controller;

/**
 * Awesome Controller
 */
class AwesomeController extends Controller
{
    /**
     * Creates a new instance of AwesomeController
     */
    function __construct()
    {
    } // end function constructs

    /**
     * The home %baseUrl/Awesome/
     */
    function index()
    {
        echo "Awesome!";
    } // end function index

} // end class AwesomeController
