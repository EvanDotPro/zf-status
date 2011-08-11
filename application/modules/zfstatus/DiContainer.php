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
            $this->_storage['zfService'] = new Zfstatus_Service_Zf($this->getGitHubService());
        }
        return $this->_storage['zfService'];
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
