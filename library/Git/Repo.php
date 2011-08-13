<?php
namespace Git;
use Exception;
class Repo
{
    /**
     * _parser 
     * 
     * @var Git\Parser
     */
    protected $_parser;

    /**
     * __construct 
     * 
     * @param Git\Parser $parser 
     * @return void
     */
    public function __construct($parser)
    {
        $this->setParser($parser);
    }

    /**
     * getRemotes 
     *
     * Returns an array of remotes
     * 
     * @return array
     */
    public function getRemotes()
    {
        return explode("\n", $this->getParser()->run('remote'));
    }

    /**
     * fetchAll 
     *
     * Updates all refs
     * 
     * @return void
     */
    public function fetchAll()
    {
        $this->getParser()->run('fetch --all --prune');
    }
 
    /**
     * Get parser.
     *
     * @return parser
     */
    public function getParser()
    {
        return $this->_parser;
    }
 
    /**
     * Set parser.
     *
     * @param $parser the value to be set
     */
    public function setParser($parser)
    {
        if (!$parser instanceof Parser) {
            throw new Exception ('Parser must be instance of Git\Parser');
        }
        $this->_parser = $parser;
        return $this;
    }
}
