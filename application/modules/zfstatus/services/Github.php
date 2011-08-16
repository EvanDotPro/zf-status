<?php
/**
 * @TODO: Use https://github.com/raphaelstolt/github-api-client
 */
class Zfstatus_Service_Github
{
    protected $_apiUrl = 'http://github.com/api/v2/json/';

    protected $_cache;

    public function __construct($cache)
    {
        $this->_cache = $cache;
    }

    public function getUser($username)
    {
        $user = $this->_get("user/show/{$username}");
        if ($user) {
            return $user->user;
        }
        return false;
    }

    public function getRepo($username, $repo)
    {
        return $this->_get("repos/show/{$username}/{$repo}")->repository;
    }

    public function getForks($username, $repo)
    {
        return $this->_get("repos/show/{$username}/{$repo}/network")->network;
    }

    public function getBranches($username, $repo)
    {
        return $this->_get("repos/show/{$username}/{$repo}/branches")->branches;
    }

    public function getCommit($username, $repo, $hash)
    {
        return $this->_fixCommit($this->_get("commits/show/{$username}/{$repo}/{$hash}")->commit);
    }

    public function getCommits($username, $repo, $branch)
    {
        $commits = $this->_get("commits/list/{$username}/{$repo}/{$branch}")->commits;
        foreach ($commits as $i => $commit) {
            $commits[$i] = $this->_fixCommit($commit);
        }
        return $commits;
    }

    public function getContributors($username, $repo)
    {
        return $this->_get("repos/show/{$username}/{$repo}/contributors")->contributors;
    }

    protected function _fixCommit($commit)
    {
        $commit->committed_date = new DateTime($commit->committed_date);
        $commit->committed_date = $commit->committed_date->setTimezone(new DateTimeZone('UTC'));
        $commit->link = $this->linkCommit($commit->url);
        $msg = explode("\n", $commit->message);
        $commit->messageBrief = array_shift($msg); 
        return $commit;
    }

    protected function _get($url)
    {
        $url = $this->_apiUrl.$url;

        if (($result = $this->_cache->load($this->_cacheTag($url))) !== false ) return $result;

        $result = $this->_decode(@file_get_contents($url));
        if ($result) {
            $this->_cache->save($result, $this->_cacheTag($url));
        }

        return $result;
    }

    protected function _decode($data)
    {
        return json_decode($data);
    }

    protected function _cacheTag($tag)
    {
        return preg_replace('/[^A-Za-z0-9]/','_', $tag);
    }

    public function gravatar($gravatarId)
    {
        return "<img src=\"https://secure.gravatar.com/avatar/{$gravatarId}?s=40&d=http://framework.zend.com/wiki/s/en/2148/48/_/images/icons/profilepics/anonymous.png\" class=\"floatLeft\"/>"; 
    }

    public function linkUser($username)
    {
        return "<a href=\"http://github.com/{$username}\" target=\"_BLANK\">{$username}</a>";
    }

    public function linkBranch($username, $repo, $branch)
    {
        return "<a href=\"https://github.com/{$username}/{$repo}/tree/{$branch}\" target=\"_BLANK\">{$branch}</a>";
    }

    public function linkCommit($username, $repo = false, $sha1 = false)
    {
        if ($repo == false && $sha1 == false) { 
            $sha1 = explode('/', $username);
            $sha1 = array_pop($sha1);
            return "<a href=\"https://github.com{$username}\" target=\"_BLANK\">".substr($sha1,0,7)."</a>";
        }
        return "<a href=\"https://github.com/{$username}/{$repo}/commit/{$sha1}\" target=\"_BLANK\">".substr($sha1,0,7)."</a>";
    }
}
