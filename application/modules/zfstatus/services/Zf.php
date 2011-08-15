<?php
class Zfstatus_Service_Zf
{
    /**
     * ZF2 Components
     *
     * This could probably be semi-automated? 
     * 
     * @var array
     * @access protected
     */
    protected $_components = array (
        'Documentation'       => array(),
        'Zend\Acl'            => array(),
        'Zend\Amf'            => array(),
        'Zend\Application'    => array(),
        'Zend\Authentication' => array(),
        'Zend\Barcode'        => array(),
        'Zend\Cache'          => array(),
        'Zend\Captcha'        => array(),
        'Zend\Cloud'          => array(),
        'Zend\Code'           => array(),
        'Zend\CodeGenerator'  => array(),
        'Zend\Config'         => array(),
        'Zend\Console'        => array(),
        'Zend\Controller'     => array(),
        'Zend\Crypt'          => array(),
        'Zend\Currency'       => array(),
        'Zend\Date'           => array(),
        'Zend\Db'             => array(),
        'Zend\Di'             => array(),
        'Zend\Dojo'           => array(),
        'Zend\Dom'            => array(),
        'Zend\EventManager'   => array(),
        'Zend\Feed'           => array(),
        'Zend\File'           => array(),
        'Zend\Filter'         => array(),
        'Zend\Form'           => array(),
        'Zend\GData'          => array(),
        'Zend\Http'           => array(),
        'Zend\InfoCard'       => array(),
        'Zend\Json'           => array(),
        'Zend\Layout'         => array(),
        'Zend\Ldap'           => array(),
        'Zend\Loader'         => array(),
        'Zend\Locale'         => array(),
        'Zend\Log'            => array(),
        'Zend\Mail'           => array(),
        'Zend\Markup'         => array(),
        'Zend\Measure'        => array(),
        'Zend\Memory'         => array(),
        'Zend\Mime'           => array(),
        'Zend\Mvc'            => array(),
        'Zend\Navigation'     => array(),
        'Zend\OAuth'          => array(),
        'Zend\OpenId'         => array(),
        'Zend\Paginator'      => array(),
        'Zend\Pdf'            => array(),
        'Zend\ProgressBar'    => array(),
        'Zend\Queue'          => array(),
        'Zend\Reflection'     => array(),
        'Zend\Rest'           => array(),
        'Zend\Search'         => array(),
        'Zend\Serializer'     => array(),
        'Zend\Server'         => array(),
        'Zend\Service'        => array(),
        'Zend\Session'        => array(),
        'Zend\Soap'           => array(),
        'Zend\Stdlib'         => array(),
        'Zend\Tag'            => array(),
        'Zend\Test'           => array(),
        'Zend\Text'           => array(),
        'Zend\TimeSync'       => array(),
        'Zend\Tool'           => array(),
        'Zend\Translator'     => array(),
        'Zend\Uri'            => array(),
        'Zend\Validator'      => array(),
        'Zend\View'           => array(),
        'Zend\Wildfire'       => array(),
        'Zend\XmlRpc'         => array(),
    );

    protected $_gh;

    public function __construct($gh)
    {
        $this->_gh = $gh;
    }

    public function getRecentActivity($repo)
    {
        $componentIndex = array();
        foreach ($repo->getCommitsByBranch(3, '--no-merges') as $remote => $branches) {
            // Skip origin, most work probably done in user forks
            if ($remote == 'origin') continue;
            foreach ($branches as $branch => $commits) {
                // Skip master, work should be in topic branches
                //if ($branch == 'master') continue;
                foreach ($commits as $hash) {
                    $commit = $repo->getCommit($hash);
                    $components = $this->_commitToComponents($commit);
                    foreach ($components as $component) {
                        $componentIndex[$component]['commits'][$hash] = $commit;
                        uasort($componentIndex[$component]['commits'], function($a, $b){
                            if ($a->getAuthorTime() > $b->getAuthorTime()) return 0;
                            return 1;
                        });
                        $componentIndex[$component]['remotes'][$remote][$branch][] = $hash;
                        if (isset($componentIndex[$component]['latest'])) {
                            if ($commit->getAuthorTime() > $componentIndex[$component]['latest']) {
                                $componentIndex[$component]['latest'] = $commit->getAuthorTime();
                            }
                        } else {
                            $componentIndex[$component]['latest'] = $commit->getAuthorTime();
                        }
                    }
                }
            }
        }
        ksort($componentIndex);
        return $componentIndex;
    }

    public function getComponents()
    {
        return array_keys($this->_components);
    }

    public function getForks()
    {
        return $this->_gh->getForks('zendframework','zf2');
    }


    /**
     * buildIndex 
     * @TODO: m:m branches/commits to components
     * 
     * @access public
     * @return void
     */
    public function buildIndex()
    {
        $index = $this->_components;
        foreach ($this->getForks() as $fork) {
            if (isset($this->_authors[$fork->owner])) {
                $user = $this->_gh->getUser($fork->owner);
                $user->link = $this->_gh->gravatar($user->gravatar_id).' '.$this->_gh->linkUser($user->login);
                $branches = $this->_gh->getBranches($fork->owner, $fork->name);
                foreach ($branches as $branchName => $commitHash) {
                    $lastCommit = $this->_gh->getCommit($fork->owner, $fork->name, $commitHash);
                    if ($lastCommit->committer->login != $user->login) {
                        continue;
                    }
                    // Skip merge commits?
                    //while (isset($lastCommit->parents) && count($lastCommit->parents) > 1){
                    //    $lastCommit = $this->_gh->getCommit($fork->owner, $fork->name, $lastCommit->parents[0]->id);
                    //}
                    $component = false;
                    if ($branchName != 'master') $component = $this->_gitHubCommitToComponent($lastCommit); 
                    if (isset($this->_components[$component])) $index[$component][$fork->owner][] = array(
                        'user' => $user, 
                        'branch' => $this->_gh->linkBranch($fork->owner, $fork->name, $branchName), 
                        'commit' => $lastCommit,
                        'commits' => $this->_gh->getCommits($fork->owner, $fork->name, $branchName)
                    );
                }
            }
        }
        return $index;
    }

    public function getActiveComponents()
    {
        $components = $this->buildIndex();
        foreach ($components as $i => $component) {
            if (count($component) == 0) {
                unset($components[$i]);
                continue;
            }
            $times = array();
            foreach ($component as $user=>$branches) {
                foreach ($branches as $j=>$branch) {
                    $times[] = $branch['commit']->committed_date;
                } 
            }
            foreach ($component as $user=>$branches) {
                $components[$i][$user][0]['latest'] = max($times);
            }
        }
        return $components;
    }

    protected function _gitHubCommitToComponent($commit)
    {
        $components = array('failed' => array());
        if (isset($commit->modified) && is_array($commit->modified)) {
            foreach ($commit->modified as $f) {
                if (is_object($f)) $f = $f->filename;
                if ($c = $this->_filenameToComponentName($f)) {
                    if (!isset($components[$c])) $components[$c] = 0;
                    $components[$c]++;
                } else {
                    if (!isset($components['failed'][$f])) $components['failed'][$f] = 0;
                    $components['failed'][$f]++;
                }
            }
        }
        if (isset($commit->added) && is_array($commit->added)) {
            foreach ($commit->added as $f) {
                if (is_object($f)) $f = $f->filename;
                if ($c = $this->_filenameToComponentName($f)) {
                    if (!isset($components[$c])) $components[$c] = 0;
                    $components[$c]++;
                } else {
                    if (!isset($components['failed'][$f])) $components['failed'][$f] = 0;
                    $components['failed'][$f]++;
                }
            }
        }
        if (isset($commit->removed) && is_array($commit->removed)) {
            foreach ($commit->removed as $f) {
                if (is_object($f)) $f = $f->filename;
                if ($c = $this->_filenameToComponentName($f)) {
                    if (!isset($components[$c])) $components[$c] = 0;
                    $components[$c]++;
                } else {
                    if (!isset($components['failed'][$f])) $components['failed'][$f] = 0;
                    $components['failed'][$f]++;
                }
            }
        }
        unset($components['failed']);
        if (count($components) > 1 && count(array_unique($components)) > 1) {
            arsort($components);
        }
        $components = array_keys($components); 
        return array_shift($components);
    }

    protected function _commitToComponents($commit)
    {
        $components = array('nomatch' => array());
        if (count($commit->getFiles()) > 0) {
            foreach ($commit->getFiles() as $f) {
                $f = $f['file'];
                if ($c = $this->_filenameToComponentName($f)) {
                    if (!isset($components[$c])) $components[$c] = 0;
                    $components[$c]++;
                } else {
                    if (!isset($components['nomatch'][$f])) $components['nomatch'][$f] = 0;
                    $components['nomatch'][$f]++;
                }
            }
        }
        unset($components['nomatch']); // comment out for debugging unmatched components
        if (count(array_unique($components)) > 1) arsort($components);
        $components = array_keys($components); 
        return $components;
    }

    protected function _filenameToComponentName($filename)
    {
        $parts = explode('/', $filename);
        if (count($parts) > 1 && $parts[0] == 'documentation') return 'Documentation'; 
        if (count($parts) < 2 || $parts[1] != 'Zend') return false;
        return $parts[1].'\\'.$parts[2];
    }

}
