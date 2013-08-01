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

// check if user have right meta attributes to see this page
if (!getUserRightAttribute($panthera->user, 'can_manage_cache')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('cache');


if ($_GET['action'] == 'save')
{
    $cache = $_POST['cache'];
    $varcache = $_POST['varcache'];
    
    // check if selected cache is avaliable
    if (!class_exists('varCache_' .$cache))
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find class "varCache_' .$cache. '" for this caching method', 'cache')));
        
    if (!class_exists('varCache_' .$varCache))
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find class "varCache_' .$varCache. '" for this caching method', 'cache')));
            
    // set our cache
    if (!$panthera->config->setKey("cache_type", $cache, 'string') OR !$panthera->config->setKey("varcache_type", $varcache, 'string'))
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Invalid value for this data type', 'cache')));
        pa_exit();
    } else {
        ajax_exit(array('status' => 'success'));
        pa_exit();
    }
}


// Check if memcached support is avaliable in PHP, if yes import our wrapper library
 $panthera -> template -> push('memcachedServers', array());

if (extension_loaded('memcached'))
{
    $panthera -> importModule('filesystem');
    $panthera -> importModule('memcached');
    $memcached = new pantheraMemcached($panthera);

    /* Popup window with additional advanced informations */
    if ($_GET['popup'] == 'stats')
    {
        $serverName = $_GET['server'];
        $stats = $memcached -> getStats();
        
        if (!array_key_exists($serverName, $stats))
        {
            $panthera -> template -> display('no_page.tpl');
            pa_exit();
        }
        
        $serverStats = $stats[$serverName];
        
        $panthera -> template -> push('server', $serverName);
        $panthera -> template -> push('stats', $serverStats);
        $panthera -> template -> display('cache_stats.tpl');
        pa_exit();
    }
    
    
    $servers = array();
    $i=0;
    foreach ($memcached -> getStats() as $server => $attributes)
    {
        $servers[$server] = array();
        $servers[$server]['num'] = $i++;
        $servers[$server]['uptime'] = date_calc_diff(time() - $attributes['uptime'], time());
        $servers[$server]['version'] = 'memcached ' .$attributes['version'];
        $servers[$server]['threads'] = $attributes['threads'];
        $servers[$server]['pid'] = $attributes['pid'];
        
        // total transfer
        $servers[$server]['read'] = bytesToSize($attributes['bytes_read']);
        $servers[$server]['written'] = bytesToSize($attributes['bytes_written']);
        
        // items stored
        $servers[$server]['items_current'] = $attributes['curr_items'];
        $servers[$server]['items_total'] = $attributes['total_items'];
        
        // commands get/set count
        $servers[$server]['get'] = $attributes['cmd_get'];
        $servers[$server]['set'] = $attributes['cmd_set'];
        
        if ($attributes['cmd_set'] > $attributes['cmd_get'])
            $servers[$server]['readWarning'] = True;
        
        // number of connections
        $servers[$server]['connections_current'] = $attributes['curr_connections'];
        $servers[$server]['connections_total'] = $attributes['total_connections'];
        
        // memory usage
        $servers[$server]['memory_used'] = bytesToSize($attributes['bytes']);
        $servers[$server]['memory_max'] = bytesToSize($attributes['limit_maxbytes']);
    }
    
    $panthera -> template -> push('memcachedServers', $servers);
}
/* End of popups */

// Detection of APC, XCache and Memcached.
$cacheList = array('xcache' => False, 'apc' => False, 'memcached' => False);

// check for requirements for built-in caching methods
if (extension_loaded('xcache'))
    $cacheList['xcache'] = True;

if (extension_loaded('apc'))
    $cacheList['apc'] = True;

if (extension_loaded('memcached'))
    $cacheList['memcached'] = True;
    
$cacheList['db'] = True; // db is always avaliable

// get list of avaliable cache methods from list of declared classes
foreach (get_declared_classes() as $className)
{
    if (substr($className, 0, 9) == 'varCache_')
    {
        $cacheName = substr($className, 9);

        // if cache is not on a list, add it
        if (!array_key_exists($cacheName, $cacheList))
        {
            $cacheList[$cacheName] = True;
        }
    }
}

// allow plugins modyfing list
$cacheList = $panthera -> get_filters('cache.list', $cacheList);

$panthera -> template -> push('cache', $panthera -> config -> getKey('cache_type'));
$panthera -> template -> push('varcache', $panthera -> config -> getKey('varcache_type'));
$panthera -> template -> push('cache_list', $cacheList);
$panthera -> template -> display('cache.tpl');
pa_exit();
