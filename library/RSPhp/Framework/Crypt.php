<?php
/**
 * Crypt.php
 *
 * PHP Version 7
 *
 * Crypt File Doc Comment
 *
 * @category  FrameworkCore
 * @package   RSPhp\Framework
 * @author    Luis Espino <luis@espino.info>
 * @copyright 2016 Luis Espino
 * @license   MIT License
 * @link      https://rsphp.espino.info/
 */

namespace RSPhp\Framework;

use \Exception;

/**
 * Helper for encryption
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
class Crypt
{

    protected $tripleDesKey;
    protected $tripleDesVector;

    /**
     * Creates an instance of a Crypt class
     *
     * @return void
     */
    function __construct()
    {
        if (!defined('TRIPLEDES_KEY') || !defined('TRIPLEDES_IV') ) {
            throw new Exception(
                "The encryption keys are not defined. Plese, ".
                "set up encryption keys before try encription",
                1
            );
        } // end is defineds
        $this->tripleDesKey = TRIPLEDES_KEY;
        $this->tripleDesVector = TRIPLEDES_IV;
    } // end function __construct

    /**
     * Generates a key from the number of bytes
     *
     * @param Int $bytesLen The number of bytes
     *
     * @return String
     */
    static function generateKey( $bytesLen )
    {
        $string = bin2hex(openssl_random_pseudo_bytes($bytesLen));
        return $string;
    } // end function

    /**
     * Encrypts buffer with triple des algorithm
     *
     * @param String $buffer The string to crypt
     *
     * @return String
     */
    function tripleDesEncrypt($buffer)
    {
        return openssl_encrypt($buffer, 'aes-128-cbc', $this->tripleDesKey, 0, $this->tripleDesVector);
    } // end function tripleDesEncrypt

    /**
     * Decrypts $buffer with Triple Des algorith
     *
     * @param mixed[] $buffer The crypted  data
     *
     * @return String
     */
    function tripleDesDecrypt($buffer)
    {
        return openssl_decrypt((string)$buffer, 'aes-128-cbc', $this->tripleDesKey, 0, $this->tripleDesVector);
    } // end function tripleDesDecrypt
} // end class Crypt
