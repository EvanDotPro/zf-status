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
        $this->view->repoName = $repo;
        $this->view->zfService = Zend_Registry::get('Zfstatus_DiContainer')->getZfService();
        $this->view->gh = Zend_Registry::get('Zfstatus_DiContainer')->getGitHubService();
        $this->view->outputCache = Zend_Registry::get('Zfstatus_DiContainer')->getCacheManager()->getCache('output');
    }
}
