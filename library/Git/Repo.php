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
     * getRemoteBranches 
     * 
     * @return array
     */
    public function getRemoteBranches()
    {
        $branches = $this->getParser()->run('branch -rv --no-abbrev');
        $pattern = '/\s*(?P<remote>[^\/\s]+)\/(?P<branch>[^\s]+)\s+(?P<sha1>[a-z0-9]{40})\s/';
        preg_match_all($pattern, $branches, $matches, PREG_SET_ORDER);
        return $matches;
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
