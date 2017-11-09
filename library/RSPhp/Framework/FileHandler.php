<?php
namespace RSPhp\Framework;

use SplFileObject;

class FileHandler
{
    private $filePath;
    private $handler;
    private $fileInfo;
    public $eof = false;

    function __construct($filePath)
    {
        $this->filePath = $filePath;
    } // end construct

    function open()
    {
        if (!$this->handler) {
            $this->handler = new SplFileObject($this->filePath);
        } // end if not handler
    } // end function open

    function close()
    {
        $this->handler = null;
    } // end function close

    function info()
    {
        if (!$this->fileInfo) {
            $this->fileInfo = new SplFileInfo($this->filePath);
        } // end if not file info
        return $this->fileInfo;
    } // end function info

    function readChar()
    {
        if (!$this->handler) {
            $this->handler = new SplFileObject($this->filePath);
        } // end if not handler

        if ($this->handler->eof()) {
            $this->eof = true;
            $this->close();
            return false;
        } // end if eof

        return $this->handler->fgetc();
    } // end function readChar

    function readAll()
    {
        return file_get_contents($this->filePath);
    } // end function readAll

    function readLine()
    {
        if (!$this->handler) {
            $this->handler = new SplFileObject($this->filePath);
        } // end if not handler

        if ($this->handler->eof()) {
            $this->eof = true;
            $this->close();
            return false;
        } // end if eof

        return $this->handler->fgets();
    } // end function readLin

    function seek()
    {
    } // end function seekLine

    function write()
    {
    } // end function write

    function append()
    {
    } // end function append

} // end clas FileReader
