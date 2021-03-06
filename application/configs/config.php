<?php
$config = array();
$config['autoloadernamespaces'][]            = 'BaseApp';
$config['autoloadernamespaces'][]            = 'Git';
$config['resources']['layout']['layoutPath'] = APPLICATION_PATH . '/layouts';
$config['resources']['layout']['layout']     = 'layout';
$config['resources']['modules']              = array();
$config['resources']['view']                 = array();
$config['bootstrap']['path']                 = APPLICATION_PATH . '/Bootstrap.php';
$config['bootstrap']['class']                = 'Bootstrap';

$config['resources']['frontController']['moduleDirectory']     = APPLICATION_PATH . '/modules';
$config['resources']['frontController']['defaultModule']       = 'zfstatus';
$config['resources']['frontController']['throwExceptions']     = true;
$config['resources']['frontController']['prefixDefaultModule'] = true;

$config['resources']['jquery']['enable']     = true;
$config['resources']['jquery']['uienable']   = true;
$config['resources']['jquery']['version']    = '1.6';
$config['resources']['jquery']['uiversion']  = '1.8';
$config['resources']['jquery']['stylesheet'] = 'https://ajax.googleapis.com/ajax/libs/jqueryui/'.$config['resources']['jquery']['uiversion'].'/themes/redmond/jquery-ui.css';

$config['resources']['router']['routes']['sort']['route']                  = '/recent';
$config['resources']['router']['routes']['sort']['defaults']['module']     = 'zfstatus';
$config['resources']['router']['routes']['sort']['defaults']['controller'] = 'index';
$config['resources']['router']['routes']['sort']['defaults']['action']     = 'index';
$config['resources']['router']['routes']['sort']['defaults']['sort']       = 'recent';

$config['resources']['cachemanager']['default']['frontend']['name']                               = 'Core';
$config['resources']['cachemanager']['default']['frontend']['options']['lifetime']                = 0;
$config['resources']['cachemanager']['default']['frontend']['options']['automatic_serialization'] = true;
$config['resources']['cachemanager']['default']['backend']['name']                                = 'File';
$config['resources']['cachemanager']['default']['backend']['options']['cache_dir']                = APPLICATION_PATH . '/data/cache';
$config['git']['options']['cache_dir'] = APPLICATION_PATH . '/data/git';

$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.'.APPLICATION_ENV.'.php';
if (file_exists($file)) {
    include_once $file;
}

return $config;
