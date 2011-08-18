<?php
use BaseApp\Application\Module\BootstrapAbstract;
class Zfstatus_Bootstrap extends BootstrapAbstract
{
   protected function _initCache()
    {
        $this->bootstrap('DiContainer');
        $this->getApplication()->bootstrap('cachemanager');
        Zend_Registry::get('Zfstatus_DiContainer')->setCacheManager($this->getApplication()->getResource('cachemanager'));
    }
}
