Zend Framework Status Tool
================================
Version 0.2.0 Created by Evan Coury


Introduction
------------
This is a little project I created in an attempt to address concerns voiced on
the zf-contributors mailing list regarding difficulty locating and following all
of the various contributor forks and branches. The goal of this project is to
make the task of following the latest progress on Zend Framework which has not
yet been merged into the main zendframework/zf2 repository. 

Ironically, I built this on Zend Framework 1.11.10. I did this for the sake of
stability so that this code can be used in production on the Zend Framework
site. 

Installation
------------

* Fork/clone this repository.
* `cd zf-status/application/data/git`
* `git clone git://github.com/zendframework/zf2.git` 
* `cd zf2`
* `git remote add githubusername git://github.com/githubusername/zf2.git` (rinse and repeat for all forks you want to track)
* `git fetch --all`
* Set up a cron to run `git fetch --all --prune` on some interval.
* Set the cache for the 'output' cache in application/configs/config.php to the
  same interval (specified in seconds) 

TODO
------------

* Remove hard-coded references to ZF2
* Better solution for mapping commits to GitHub usernames
* Remove hard-coded cache lifetimes in rand() calls.

License
-------
This projet is released under the terms of the [New BSD License](http://www.opensource.org/licenses/BSD-3-Clause). See **`LICENSE`** file for details.
