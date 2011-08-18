<?php
class Zfstatus_FeedController extends Zend_Controller_Action
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
        $repo = $repos[$repo];
        $zfService = Zend_Registry::get('Zfstatus_DiContainer')->getZfService();
        $feed = new Zend_Feed_Writer_Feed;
        $feed->setTitle('ZF2 Recent Git Activity');
        $feed->setLink('http://zf2.evan.pro/');
        $feed->setFeedLink('http://zf2.evan.pro/feed', 'atom');
        $feed->setTitle('ZF2 Recent Git Activity');
        $feed->setDateModified(time());

        $commits = $zfService->getRecentActivity($repo, 'recent', true);
        foreach ($commits['commits'] as $hash => $commit) {
            $meta = $commits['meta'][$hash];
            $entry = $feed->createEntry();
            $entry->setTitle(substr($hash,0,7) . ': ' . $commit->getSubject());
            $entry->setLink('http://github.com/' . $meta['remote'] . '/zf2/commit/'.$hash);
            $entry->addAuthor(array(
                'name'  => $commit->getAuthorName(),
                'email' => $commit->getAuthorEmail(),
            ));
            $entry->setDateCreated($commit->getAuthorTime()->getTimestamp());
            $entry->setDateModified($commit->getCommitterTime()->getTimestamp());
            $entry->setDescription('Commit by ' . $commit->getAuthorName() . ' on branch ' . $meta['remote'] . '/' . $meta['branch']);
            $entry->setContent(
                $commit->getMessage() . 
                '<br/>' .
                '<strong>Affects components:</strong> ' . 
                implode(', ', $meta['components'])
            );
            $feed->addEntry($entry);
        }

        $out = $feed->export('atom');

        die($out);
    }
}
