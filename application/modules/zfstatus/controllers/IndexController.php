<?php
class Zfstatus_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $zfService = Zend_Registry::get('Zfstatus_DiContainer')->getZfService();
        $gitService = Zend_Registry::get('Zfstatus_DiContainer')->getGitService();
        $repo = $this->_getParam('repo');
        $repos = $gitService->getRepositories();
        if (!$repo) {
            $reposNames = array_keys($repos);
            $repo = array_shift($reposNames);
            
        }
        $this->view->zfComponents = $zfService->getRecentActivity($repos[$repo]);
        //var_dump($this->view->zfComponents);die();
    }
}
