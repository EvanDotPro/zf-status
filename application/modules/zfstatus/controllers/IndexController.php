<?php
class Zfstatus_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $gitService = Zend_Registry::get('Zfstatus_DiContainer')->getGitService();
        $repo = $this->_getParam('repo');
        $repos = $gitService->getRepositories();
        if (!$repo) {
            $reposNames = array_keys($repos);
            $repo = array_shift($reposNames);
        }

        $this->view->repo = $repos[$repo];
        $this->view->sort = $this->_getParam('sort');
        $this->view->zfService = Zend_Registry::get('Zfstatus_DiContainer')->getZfService();
        $this->view->gh = Zend_Registry::get('Zfstatus_DiContainer')->getGitHubService();
        
        $cache = Zend_Registry::get('Zfstatus_DiContainer')->getCacheManager()->getCache('default');
        $cacheTag = 'accordion_' . preg_replace('/[^A-Za-z0-9_]/','',$repo.$this->view->sort);
        if ( ($this->view->recentActivity = $cache->load($cacheTag)) === false ) {
            $this->view->recentActivity = $this->view->zfService->getRecentActivity($this->view->repo, $this->view->sort);
            $cache->save($this->view->recentActivity, $cacheTag);
        }
    }
}
