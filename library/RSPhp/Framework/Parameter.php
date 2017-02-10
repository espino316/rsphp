<?php
/**
 * Parameter.php
 *
 * PHP Version 5
 *
 * Parameter File Doc Comment
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
 * Represent a parameter for data sources
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
class Parameter
{
    public $name;
    public $type;
    public $defaultValue = null;

    /**
     * Creates an instance of Parameter
     *
     * @param String       $name         The parameter's name
     * @param String       $type         <The parameter's type (SESSION|INPUT)
     * @param mixed[]|null $defaultValue The parameter's default value
     *
     * @return void
     */
    function __construct( $name, $type, $defaultValue = null ) 
    {
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $defaultValue;
    } // end function construct
} // end class Parameter
