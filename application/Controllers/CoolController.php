<?php

namespace Application\Controllers;

use Espino\RSPhp\Controller;
use Espino\RSPhp\View;

/**
 * My Cool Controller
 */
class CoolController extends Controller
{
    /**
     * Creates a new instance of CoolController
     */
    function __construct()
    {
    } // end function constructs

    /**
     * The home %baseUrl/Cool/
     */
    function index()
    {
        View::load("MyView");
    } // end function index

    function look()
    {
        echo "Hello look!";
    } // end function look

    function be( $name ) {
        echo "Be cool $name!";
    } // end function be

} // end class CoolController
