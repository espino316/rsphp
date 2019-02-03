<?php
use PHPUnit\Framework\TestCase;
use RSPhp\Framework\Crypt;

require 'config/tdeskeys.php';

class CryptTest extends TestCase
{
    public function testCrypt()
    {
        $data = "Some text";
        $crypt = new Crypt();
        $cipherText = $crypt->tripleDesEncrypt($data);
        $this->assertTrue(
            $data == $crypt->tripleDesDecrypt($cipherText)
        );

    } // end function testCrypt

    public function testEncrypt() {
        $data = "Some text";
        $crypt = new Crypt();
        $cipherText = $crypt->tripleDesEncrypt($data);
        $this->assertTrue(
            $cipherText != $data
        );
    } // end function testEncrypt

    public function testDecrypt()
    {
        $data = "Some text";
        $cipherText = "+Zzn8tb2xn7eAtxN1pmf+g==";
        $crypt = new Crypt();
        $result = $crypt->tripleDesDecrypt($cipherText);
        $this->assertTrue(
            $result == $data
        );
    } // end function testDecrypt

    public function testGenerateKey()
    {
        $crypt = new Crypt();
        $result = $crypt->generateKey(24);
        echo "result: $result\n";
        $this->assertTrue(
            $result != ""
        ); // end assert
    } // end function testGenerateKey


} // end class CrypTest
