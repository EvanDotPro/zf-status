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
     * _commitCache 
     * 
     * @var array
     */
    protected $_commitCache;

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
        $pattern = '/\s*(?P<remote>[^\/\s]+)\/(?P<branch>[^\s]+)\s+(?P<hash>[a-z0-9]{40})\s/';
        preg_match_all($pattern, $branches, $matches, PREG_SET_ORDER);
        return $matches;
    }

    /**
     * getCommitsByBranch 
     * 
     * Returns a array of commits by remote/branch and build a cache
     * 
     * @param int $limit 
     * @return array
     */
    public function getCommitsByBranch($limit = 5)
    {
        $this->_commitsByBranch = array();
        $remoteBranches = $this->getRemoteBranches();
        foreach ($remoteBranches as $branch) {
            $commits = $this->getParser()->run('log -n ' . $limit . ' --pretty=format:\'</files>%n</commit>%n<commit>%n<json>%n{%n  "commit": "%H",%n  "tree": "%T",%n  "parent": "%P",%n  "author": {%n    "name": "%aN",%n    "email": "%aE",%n    "date": "%ai"%n  },%n  "committer": {%n    "name": "%cN",%n    "email": "%cE",%n    "date": "%ci"%n  }%n}%n</json>%n<message><![CDATA[%B]]></message>%n<files>\' --numstat '."{$branch['remote']}/{$branch['branch']}");
            $commits = simplexml_load_string('<commits>'.substr($commits,18).'</files></commit></commits>');
            foreach ($commits->commit as $log) {
                $details = json_decode($log->json);
                $hash = (string)$details->commit;
                if (!$this->getCommit($hash)) { 
                    $commit = new Commit;
                    $commit->setHash($hash);
                    $commit->setTree((string)$details->tree);
                    $commit->setParents(explode(' ', $details->parent));
                    $commit->setAuthorName((string)$details->author->name);
                    $commit->setAuthorEmail((string)$details->author->email);
                    $commit->setAuthorTime((string)$details->author->date);
                    $commit->setCommitterName((string)$details->committer->name);
                    $commit->setCommitterEmail((string)$details->committer->email);
                    $commit->setCommitterTime((string)$details->committer->date);
                    $commit->setMessage((string)$log->message);
                    $commit->setFiles($this->_parseFiles($log->files));
                    $this->setCommit($commit->getHash(), $commit);
                }
                $this->_commitsByBranch[$branch['remote']][$branch['branch']][] = $hash;
            }
        }
        return $this->_commitsByBranch;
    }

    protected function _parseFiles($files)
    {
        $pattern = '/\s*(?P<inserts>\d+)\s(?P<deletions>\d+)\s+(?P<file>[^\s]+)/';
        preg_match_all($pattern, $files, $matches, PREG_SET_ORDER);
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

    /**
     * setCommit 
     * 
     * @param string $hash 
     * @param Commit $commit 
     */
    public function setCommit($hash, $commit)
    {
        $this->_commitCache[$hash] = $commit;
        return $this;
    }

    /**
     * getCommit 
     * 
     * @param string $hash 
     */
    public function getCommit($hash)
    {
        if (isset($this->_commitCache[$hash])) {
            return $this->_commitCache[$hash];
        } else {
            return false;
        }
    }
}
