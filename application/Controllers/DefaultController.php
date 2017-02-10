<?php
namespace Application\Controllers;
use Espino\RSPhp\Controller;

/**
 * Controller for testing
 */
class DefaultController extends Controller
{
    /**
     * Creates a new instance of TestingController
     */
    function __construct()
    {
    } // end function constructs

    /**
     * The home %baseUrl/Testing/
     */
    function index()
    {
        echo "Default ok";
    } // end function index

} // end class TestingController
