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
     * _gravatarMap 
     * 
     * @var array
     */
    protected $_gravatarMap;

    /**
     * _gravatarFileHashes 
     * 
     * @var array
     */
    protected $_gravatarFileHashes = array();

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
        $remotes = $this->getParser()->run('remote -v');
        $pattern = '/(?P<remote>[^\s]+)\t(?P<url>[^\s]+)/';
        preg_match_all($pattern, $remotes, $allMatches, PREG_SET_ORDER);
        $matches = array();
        foreach ($allMatches as $match) {
            $matches[$match['remote']] = $match['url'];
        }
        return $matches;
    }

    /**
     * gravatarMap 
     *
     * Hack to figure out GitHub usernames from commits
     * 
     * @param mixed $gravatarHash 
     * @return string
     */
    public function gravatarMap($gravatarHash)
    {
        if (!isset($this->_gravatarMap)) {
            $this->_gravatarMap = $this->_buildGravatarMap();
        }
        if (is_array($this->_gravatarMap)) {
            if(isset($this->_gravatarMap[$gravatarHash])) {
                return $this->_gravatarMap[$gravatarHash];
            } else {
                if (!isset($this->_gravatarFileHashes[$gravatarHash])) {
                    $this->_gravatarFileHashes[$gravatarHash] = md5(file_get_contents("http://gravatar.com/avatar/{$gravatarHash}?s=5"));
                }
                if(isset($this->_gravatarMap[$this->_gravatarFileHashes[$gravatarHash]])) {
                    return $this->_gravatarMap[$this->_gravatarFileHashes[$gravatarHash]];
                }
                return false;
            }
        }
        return false;
    }

    /**
     * _buildGravatarMap 
     *
     * Hack to figure out GitHub usernames from commits.
     * Sorry this is so confusing. Would love a better way.
     * 
     * @return array
     */
    protected function _buildGravatarMap()
    {
        $remotes = $this->getRemotes();
        foreach ($remotes as $remote => $url) {
            if ($remote != 'origin') continue;
            unset($remotes['origin']);
            // Must have an origin that is from GitHub.
            if (!isset($url) || strstr($url, '://github.com') === false) return false;
            $url = parse_url(substr($url, 0, -4));
            $apiUrl = "https://api.github.com/repos{$url['path']}/contributors";
            $return = json_decode(file_get_contents($apiUrl));
            $gravatars = array();
            foreach ($return as $contributor) {
                $gravatarUrl = parse_url($contributor->avatar_url);
                $gravatarHash = substr($gravatarUrl['path'], -32);
                if (!isset($gravatars[$gravatarHash])) {
                    $gravatars[$gravatarHash] = $contributor->login;
                    // hack for multiple email addresses...
                    // this will break if they have different commit/github 
                    // email addresses, but no gravatar set for either.
                    $gravatarFileHash = md5(file_get_contents("http://gravatar.com/avatar/{$gravatarHash}?s=5"));
                    $gravatars[$gravatarFileHash] = $contributor->login;
                    unset($remotes[$contributor->login]);
                }
            }
            break;
        }
        // for the ones left that we didn't find a gravatar for:
        foreach ($remotes as $remote => $url) {
            $url = parse_url(substr($url, 0, -4));
            $apiUrl = 'https://api.github.com/repos' . $url['path'];
            $return = json_decode(file_get_contents($apiUrl));
            $gravatarUrl = parse_url($return->owner->avatar_url);
            $gravatarHash = substr($gravatarUrl['path'], -32);
            if (!isset($gravatars[$gravatarHash])) {
                $gravatars[$gravatarHash] = $return->owner->login;
                $gravatarFileHash = md5(file_get_contents("http://gravatar.com/avatar/{$gravatarHash}?s=5"));
                $gravatars[$gravatarFileHash] = $return->owner->login;
            }
        }
        return $gravatars;
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
    public function getCommitsByBranch($limit = 5, $extra = '')
    {
        $this->_commitsByBranch = array();
        $remoteBranches = $this->getRemoteBranches();
        foreach ($remoteBranches as $branch) {
            $commits = $this->getParser()->run('log ' . $extra . ' -n ' . $limit . ' --pretty=format:\'</files>%n</commit>%n<commit>%n<json>%n{%n  "commit": "%H",%n  "tree": "%T",%n  "parent": "%P",%n  "author": {%n    "name": "%aN",%n    "email": "%aE",%n    "date": "%ai"%n  },%n  "committer": {%n    "name": "%cN",%n    "email": "%cE",%n    "date": "%ci"%n  }%n}%n</json>%n<message><![CDATA[%B]]></message>%n<files>\' --numstat '."{$branch['remote']}/{$branch['branch']}");
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
                    $commit->setFiles($this->_parseFiles((string)$log->files));
                    $this->setCommit($commit->getHash(), $commit);
                }
                $this->_commitsByBranch[$branch['remote']][$branch['branch']][] = $hash;
            }
        }
        return $this->_commitsByBranch;
    }

    protected function _parseFiles($files)
    {
        $pattern = '/\s*(?P<insertions>\d+)\s(?P<deletions>\d+)\s+(?P<file>[^\s]+)/';
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
