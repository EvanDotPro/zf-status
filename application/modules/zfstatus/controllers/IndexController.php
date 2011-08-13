<?php
class Zfstatus_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->zfComponents = Zend_Registry::get('Zfstatus_DiContainer')->getZfService()->getActiveComponents();
    }

    public function gitAction()
    {
        $repo = $this->_getParam('repo');
        $gitService = Zend_Registry::get('Zfstatus_DiContainer')->getGitService();
        $repos = $gitService->getRepositories();
        var_dump($repos);die();
    }
}
