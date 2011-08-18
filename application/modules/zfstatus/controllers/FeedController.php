<?php
class Zfstatus_FeedController extends Zend_Controller_Action
{
    public function indexAction()
    {
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $gitService = Zend_Registry::get('Zfstatus_DiContainer')->getGitService();
        $repo = $this->_getParam('repo');
        $repos = $gitService->getRepositories();
        if (!$repo) {
            $reposNames = array_keys($repos);
            $repo = array_shift($reposNames);
            
        }
        $outputCache = Zend_Registry::get('Zfstatus_DiContainer')->getCacheManager()->getCache('output');
        if (!$outputCache->start('accordion_feed_'.preg_replace('/[^A-Za-z0-9_]/','',$repo))) {
            $repo = $repos[$repo];
            $zfService = Zend_Registry::get('Zfstatus_DiContainer')->getZfService();
            $feed = new Zend_Feed_Writer_Feed;
            $feed->setTitle('ZF2 Recent Git Activity');
            $feed->setLink('http://zf2.evan.pro/');
            $feed->setFeedLink('http://zf2.evan.pro/feed', 'atom');
            $feed->setTitle('ZF2 Recent Git Activity');
            $commits = $zfService->getRecentActivity($repo, 'recent', true);
            $feed->setDateModified(reset($commits['commits'])->getAuthorTime()->getTimestamp());
            foreach ($commits['commits'] as $hash => $commit) {
                $meta = $commits['meta'][$hash];
                $fullBranch = $meta['remote'] . '/' . $meta['branch'];
                $entry = $feed->createEntry();
                $entry->setTitle(substr($hash,0,7) . ' on ' . $fullBranch . ': ' . $commit->getSubject());
                $entry->setLink('http://github.com/' . $meta['remote'] . '/zf2/commit/'.$hash);
                $entry->addAuthor(array(
                    'name'  => $commit->getAuthorName(),
                    'email' => $commit->getAuthorEmail(),
                ));
                $entry->setDateCreated($commit->getAuthorTime()->getTimestamp());
                $entry->setDateModified($commit->getCommitterTime()->getTimestamp());
                $entry->setDescription(
                    'Commit by <a href="http://github.com/' . $meta['remote'] . '">' . 
                    $commit->getAuthorName() .
                    '</a> ' . 
                    ' on branch ' . 
                    '<a href="http://github.com/' . $meta['remote'] . '/zf2/tree/' . $meta['branch'] . '">' .
                    $fullBranch .
                    '</a>'
                );
                $entry->setContent(
                    '<img src="https://secure.gravatar.com/avatar/' .
                    $commit->getAuthorGravatar() .
                    '?s=40&d=http://framework.zend.com/wiki/s/en/2148/48/_/images/icons/profilepics/anonymous.png" '.
                    'style="float: left; margin-right: 5px;" /> '.
                    '<strong>' . $entry->getDescription() . '</strong><br/>' .
                    nl2br($commit->getMessage()) . 
                    '<br/>' .
                    '<strong>Affects component(s):</strong> ' . 
                    implode(', ', $meta['components'])
                );
                $feed->addEntry($entry);
            }
            echo $feed->export('atom');
            $outputCache->end();
        }
    }
}
