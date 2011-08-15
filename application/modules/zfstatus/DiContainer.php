<?php
use BaseApp\DiContainerAbstract;
class Zfstatus_DiContainer extends DiContainerAbstract
{
    public function getGitHubService()
    {
        if (!isset($this->_storage['ghService'])) {
            $this->_storage['ghService'] = new Zfstatus_Service_Github($this->getCacheManager()->getCache('default'));
        }
        return $this->_storage['ghService'];
    }

    public function getZfService()
    {
        if (!isset($this->_storage['zfService'])) {
            $this->_storage['zfService'] = new Zfstatus_Service_Zf;
        }
        return $this->_storage['zfService'];
    }

    public function getGitService()
    {
        if (!isset($this->_storage['gitService'])) {
            $options = $this->getOptions();
            $this->_storage['gitService'] = new Zfstatus_Service_Git($options['git']['options']['cache_dir']);
        }
        return $this->_storage['gitService'];
    }

    public function getCacheManager()
    {
        return $this->_storage['cachemanager'];
    }

    public function setCacheManager($cacheManager)
    {
        $this->_storage['cachemanager'] = $cacheManager;
    }
}
