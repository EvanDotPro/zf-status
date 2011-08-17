<?php
class Zfstatus_Service_Zf
{
    /**
     * ZF2 Components
     *
     * This could probably be semi-automated? 
     * They are each an array because I had some idea to add meta data to each 
     * component but now I cannot remember what that was. Oh well.
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
        'Zend\Ical'           => array(),
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

    /**
     * _gh 
     * 
     * @var ZfStatus_Service_Github
     */
    protected $_gh;

    /**
     * __construct 
     * 
     * @param mixed $gh 
     * @access public
     * @return void
     */
    public function __construct($gh)
    {
        $this->setGh($gh);
    }

    /**
     * getRecentActivity 
     * 
     * @param mixed $repo 
     * @return array
     */
    public function getRecentActivity($repo)
    {
        $componentIndex = array();
        $branchIndex = array();
        foreach ($repo->getCommitsByBranch(4, '--no-merges --first-parent', array('origin'), array('master')) as $remote => $branches) {
            foreach ($branches as $branch => $commits) {
                foreach ($commits as $hash) {
                    $commit = $repo->getCommit($hash);
                    $gitHubUsername = $this->getGh()->emailToUsername($commit->getAuthorEmail(), $repo);
                    if ($gitHubUsername != $remote) continue;
                    $components = $this->_commitToComponents($commit);
                    foreach ($components as $component) {
                        $componentIndex[$component]['commits'][$hash] = $commit;
                        uasort($componentIndex[$component]['commits'], function($a, $b){
                            if ($a->getAuthorTime() > $b->getAuthorTime()) return 0;
                            return 1;
                        });

                        $absBranch = $remote.'/'.$branch;
                        if (!isset($branchIndex[$absBranch])) {
                            $branchIndex[$absBranch] = new StdClass;
                            $branchIndex[$absBranch]->components = array();
                        }
                        if (!isset($branchIndex[$absBranch]->components[$component])) {
                            $branchIndex[$absBranch]->components[$component] = 0;
                        }
                        $branchIndex[$absBranch]->components[$component]++;

                        $componentIndex[$component]['remotes'][$remote][$branch]['commits'][] = $hash;
                        $componentIndex[$component]['remotes'][$remote][$branch]['components'] = $branchIndex[$absBranch];
                        $componentIndex[$component]['all-branches'][$absBranch] = $hash;
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

 
    /**
     * Get gh.
     *
     * @return gh
     */
    public function getGh()
    {
        return $this->_gh;
    }
 
    /**
     * Set gh.
     *
     * @param $gh the value to be set
     */
    public function setGh($gh)
    {
        $this->_gh = $gh;
        return $this;
    }
}
