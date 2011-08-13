<?php
namespace Git;
use Exception;
class Parser
{
    protected $_gitPath = '/usr/bin/git';

    protected $_projectPath = false;

    /**
     * __construct 
     * 
     * @param string $projectPath 
     * @return void
     */
    public function __construct($projectPath)
    {
        $this->setProjectPath($projectPath);
    }

    /**
     * run 
     * 
     * @param string $command 
     * @param string $projectPath 
     * @return string
     */
    public function run($command, $projectPath = false)
    {
        $projectPath = $projectPath ?: $this->getProjectPath();
        $cmd = $this->getGitPath() . ' --git-dir=' . escapeshellarg($projectPath) . ' ' . $command;
        $output = `$cmd`;
        return trim($output);
    }

    /**
     * Get gitPath.
     *
     * @return gitPath
     */
    public function getGitPath()
    {
        return $this->_gitPath;
    }
 
    /**
     * Set gitPath.
     *
     * @param $gitPath the value to be set
     */
    public function setGitPath($gitPath)
    {
        $this->_gitPath = $gitPath;
        return $this;
    }
 
    /**
     * Get projectPath.
     *
     * @return projectPath
     */
    public function getProjectPath()
    {
        return $this->_projectPath;
    }
 
    /**
     * Set projectPath.
     *
     * @param $projectPath the value to be set
     */
    public function setProjectPath($projectPath)
    {
        $realProjectPath = realpath($projectPath . '/.git');
        if ($realProjectPath === false) {
            throw new Exception('Failed to resolve path: '.$projectPath);
        }
        $this->_projectPath = $realProjectPath;
        return $this;
    }

    public function status()
    {
        $status = $this->run('status --porcelain --short');
    }

    public function isGitRepo($path)
    {
        $stderr = $this->run('status --porcelain --short 2>&1 1> /dev/null', $path);
        if ($stderr) {
            return false;
        }
        return true;
    }
}
