<?php
use BaseApp\DiContainerAbstract;
class Zfstatus_DiContainer extends DiContainerAbstract
{
    public function getGitHubService()
    {
        if (!isset($this->_storage['ghService'])) {
            $this->_storage['ghService'] = new Zfstatus_Service_Github($this->getCache());
        }
        return $this->_storage['ghService'];
    }

    public function getZfService()
    {
        if (!isset($this->_storage['zfService'])) {
            $this->_storage['zfService'] = new Zfstatus_Service_Zf($this->getGitHubService(), $this->getGitService());
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

    public function getCache()
    {
        return $this->_storage['cache'];
    }

    public function setCache($cache)
    {
        $this->_storage['cache'] = $cache;
    }
}
