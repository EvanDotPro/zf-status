<?php
use Git\Parser,
    Git\Repo;
class Zfstatus_Service_Git
{
    /**
     * _repositories 
     * 
     * @var array
     */
    protected $_repositories;

    public function __construct($path)
    {
        $this->getRepositories(new DirectoryIterator(realpath($path)));
    }

    /**
     * getRepositories 
     * 
     * @param DirectoryIterator $directoryIterator 
     * @return array
     */
    public function getRepositories($directoryIterator = false)
    {
        if ($this->_repositories === NULL) {
            $this->_repositories = array();
            foreach ($directoryIterator as $fileInfo) {
                if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                    $dirName = $fileInfo->getFilename();
                    try {
                        $this->_repositories[$dirName] = new Repo(new Parser($fileInfo->getPathname()));
                    } catch (Exception $e) {
                        unset($this->_repositories[$dirName]);
                    }
                }
            }
        }
        return $this->_repositories;
    }
}
