<?php
/**
 * CryptHelper.php
 *
 * PHP Version 5
 *
 * CryptHelper File Doc Comment
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
class CryptHelper
{

    protected $tripleDesKey;
    protected $tripleDesVector;

    /**
     * Creates an instance of a CryptHelper class
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

        $cipher = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');

        // get the amount of bytes to pad
        $extra = 8 - (strlen($buffer) % 8);
        //printvar($extra, 'Padding with n zeros');

        // add the zero padding
        if ($extra > 0) {
            for ($i = 0; $i < $extra; $i++) {
                $buffer .= "\0";
            } // end for
        } // end if extra

        mcrypt_generic_init($cipher, $this->tripleDesKey, $this->tripleDesVector);

        $result = bin2hex(mcrypt_generic($cipher, $buffer));
        mcrypt_generic_deinit($cipher);
        return $result;
    } // end function tripleDesEncrypt

    /**
     * Decrypts $buffer with Triple Des algorith
     *
     * @param mixed[] $buffer The crypted  data
     *
     * @return String
     */
    function tripleDesDecrypt( $buffer )
    {

        $cipher = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');

        mcrypt_generic_init($cipher, $this->tripleDesKey, $this->tripleDesVector);
        $result = rtrim(mdecrypt_generic($cipher, hex2bin($buffer)), "\0");
        mcrypt_generic_deinit($cipher);
        return $result;
    } // end function tripleDesDecrypt
} // end class CryptHelper
