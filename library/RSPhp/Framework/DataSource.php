<?php
/**
 * DataSource.php
 *
 * PHP Version 5
 *
 * DataSource File Doc Comment
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
 * Represents a data source
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
class DataSource
{

    public $connection;
    public $name;
    public $type;
    public $text;
    public $file;
    public $parameters;
    public $filters;

    /**
     * Creates a new instance of a datasource
     *
     * @param String $connection The connection to use
     * @param String $name       The datasource's name
     * @param String $type       The datasource's type
     * @param String $text       The datasource's text, the actual sql e.g.
     * @param String $text       The datasource's file path, may include "$root" as reference
     *
     * @return void
     */
    function __construct(
        $connection,
        $name,
        $type,
        $text = "",
        $file = ""
    ) {
        $this->connection = $connection;
        $this->name = $name;
        $this->type = $type;
        $this->text = $text;
        $this->file = $file;
    } // end class DataSource  } // end function __construct

    /**
     * Adds a parameter to the datasource
     *
     * @param String       $name         The parameter's name
     * @param String       $type         The parameter's type ( Session|Input )
     * @param mixed[]|null $defaultValue The parameter's default value
     *
     * @return void
     */
    function addParam( $name, $type, $defaultValue = null )
    {
        $param = new Parameter($name, $type, $defaultValue);
        $this->parameters[] = $param;
    }

    /**
     * Adds a filter to the datasource
     *
     * @param String  $key   The filter's name
     * @param mixed[] $value The filter's value
     *
     * @return void
     */
    function addFilter( $key, $value )
    {
        $this->filters[$key] = $value;
    }
} // end class DataSource
