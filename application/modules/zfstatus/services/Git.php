<?php
use Git\Parser,
    Git\Repo;
class Zfstatus_Service_Git
{
    protected $_parser;
    protected $_repo;

    public function __construct($path)
    {
        $this->_parser = new Parser($path);
        $this->_repo = new Repo($this->_parser);
    }
}
