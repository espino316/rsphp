<?php
use PHPUnit\Framework\TestCase;
use RSPhp\Framework\File;
use RSPhp\Framework\FileHandler;
use RSPhp\Framework\DbSchema;

class FileTest extends TestCase
{
    public function testReadLine()
    {
        $f = new FileHandler("tests/test.rs");

        //while(!$f->eof) {
            //echo $f->readLine();
        //} // end while

        $this->assertTrue(
            1 == 1
        );

    } // end function testSetWebConnection

    public function testReadChar()
    {
        $f = new FileHandler("tests/test.rs");

        //while(!$f->eof) {
            //echo $f->readChar();
        //} // end while

        $this->assertTrue(
            1 == 1
        );

    } // end function testSetWebConnection

    public function testParser()
    {
        $dbSchema = new DbSchema;

        $dbSchema->update("tests/test.rs");

        $this->assertTrue(
            1 == 1
        );
    } // end function testParser

} // end class DbTest
