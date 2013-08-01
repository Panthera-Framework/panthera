<?php
/**
  * Cache management 
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
 
if (!defined('IN_PANTHERA'))
    exit;

if (!extension_loaded('memcached')) {
    pa_exit();
}

$tpl = 'cache.tpl';

$panthera -> locale -> loadDomain('cache');

/* Ajax actions */
if ($_GET['action'] == 'save')
{
    if (!getUserRightAttribute($user, 'can_update_config_overlay')) {
         $template->display('no_access.tpl');
         pa_exit();
    }
    
    $cache = $_POST['cache'];
    $varcache = $_POST['varcache'];
            
    if (gettype($cache) != 'string' OR gettype($varcache) != 'string')
        ajax_exit(array('status' => 'failed', 'message' => 'Invalid type of variables'));
            
    if (!$panthera->config->setKey("cache_type", $cache) OR !$panthera->config->setKey("varcache_type", $varcache))
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Invalid value for this data type')));
        pa_exit();
    } else {
        ajax_exit(array('status' => 'success', 'message' => localize('Cache variables have been saved!')));
        pa_exit();
    }
}
/* End of actions */

// Import memcached module
$panthera -> importModule('memcached');
$memcached = new pantheraMemcached($panthera);

/* Popup */
if ($_GET['popup'] == 'stats')
{
    $tpl = 'cache_stats.tpl';

    $string = $_GET['server'].':'.$_GET['port'];
    
    $stats = $memcached -> getStats();
    
    foreach($stats as $key => $value)
    {
        if ($key == $string) {
            $template -> push('server', $key);
            $template -> push('stats', $value);
        }
    }
}
/* End of popups */

// Detection of APC, XCache and Memcached.
$cache_list = array();

if (extension_loaded('xcache'))
    $cache_list['xcache'] = True;

if (extension_loaded('apc'))
    $cache_list['apc'] = True;

if (extension_loaded('memcached'))
    $cache_list['memcached'] = True;

$cache_list['db'] = True; // db is required (it must be)
 
$template -> push('cache', $panthera -> config -> getKey('cache_type'));
$template -> push('varcache', $panthera -> config -> getKey('varcache_type'));
$template -> push('cache_list', $cache_list);
$template -> push('servers', $memcached->getServerList());