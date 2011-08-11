<?php
class Zfstatus_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->zfComponents = Zend_Registry::get('Zfstatus_DiContainer')->getZfService()->getActiveComponents();
    }
}
