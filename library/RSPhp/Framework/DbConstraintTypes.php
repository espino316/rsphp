<?php
/**
 * DbContraintTypes.php
 *
 * PHP Version 5
 *
 * DbConstraintTypes Doc Comment
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
 * Helper to do Http requests
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
abstract class DbConstraintTypes
{
    const PrimaryKey = "PRIMARY KEY";
    const ForeignKey = "FOREIGN KEY";
    const Unique = "UNIQUE";
    const Check = "CHECK";
} // end class HttpContentTypes
