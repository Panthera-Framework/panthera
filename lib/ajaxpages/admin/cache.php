<?php
/**
  * Cache management
  *
  * @package Panthera\modules\cache
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

$panthera = pantheraCore::getInstance();

// check if user have right meta attributes to see this page
if (!getUserRightAttribute($panthera->user, 'can_manage_cache')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('cache');
$panthera -> importModule('autoloader.tools');

/**
  * Saving cache and varCache settings
  *
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'save')
{
    $cache = $_POST['cache'];
    $varcache = $_POST['varcache'];
    
    if ($dir = getContentDir('modules/cache/varCache_' .$cache. '.module.php'))
    {
        include_once $dir;
    }

    // check if selected cache is avaliable
    if (!class_exists('varCache_' .$cache))
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find class "varCache_' .$cache. '" for this caching method', 'cache')));

    if (!class_exists('varCache_' .$varcache))
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find class "varCache_' .$varcache. '" for this caching method', 'cache')));

    // clear cache
    if ($cache != $panthera->config->getKey("cache_type"))
    {
        if ($panthera->cache)
            $panthera->cache->clear();
    }
    
    // and so, clear varCache too
    if ($varcache != $panthera->config->getKey("varcache_type"))
    {
        if ($panthera->varCache)
            $panthera->varCache->clear();
    }
    
    // refresh cache
    $panthera -> loadCache($varcache, $cache, $panthera->config->getKey('session_key'));
    
    // and again...
    if ($cache != $panthera->config->getKey("cache_type"))
    {
        if ($panthera->cache)
        {
            $panthera->cache->clear();
            $panthera->config->setKey("cache_type", $cache, 'string');
        }
    }
    
    if ($varcache != $panthera->config->getKey("varcache_type"))
    {
        if ($panthera->varCache)
        {
            $panthera->varCache->clear();
            $panthera->config->setKey("varcache_type", $varcache, 'string');
        }
    }

    ajax_exit(array('status' => 'success'));

/**
  * Add new Redis server
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'addRedisServer') {
    // TODO: Support UNIX-socket connections

    $config = $panthera -> config -> getKey('redis_servers');
    $persistent = False;
    
    if ($_POST['persistent'] == "1")
        $persistent = True;
    
    $found = False;
    foreach ($config as $server)
    {
        if ($server['host'] == $_POST['ip'] and $server['port'] == $_POST['port'])
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Server is already on the list', 'cache')));
        }
    }
    
    try {
        $r = new Redis();
        $r -> connect($_POST['ip'], $_POST['port']);
        
        if ($r -> info())
        {
            $config[] = array('host' => $_POST['ip'], 'port' => $_POST['port'], 'persistent' => $persistent, 'socket' => False);
        }
        
        $panthera -> config -> setKey('redis_servers', $config, 'array');
    } catch (Exception $e) {
        // nothing
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot connect to Redis server', 'cache')));
    }
    
    ajax_exit(array('status' => 'success'));
    
/**
  * Remove a Redis server
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'removeRedisServer') {
    $details = explode(':', $_POST['address']);
    $config = $panthera -> config -> getKey('redis_servers');

    foreach ($config as $key => $server)
    {
        if ($server['host'] == $details[0] and $server['port'] == $details[1])
        {
            unset($config[$key]);
            $panthera -> config -> setKey('redis_servers', $config, 'array');
            ajax_exit(array('status' => 'success'));
        }
    }
    
    ajax_exit(array('status' => 'failed'));
    
/**
  * Adding new Memcached server
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'addMemcachedServer') {

    if (!class_exists('Memcached'))
    {
        ajax_exit(array('status' => 'failed'));
    }

    $m = new Memcached();
    $m->addServer($_POST['ip'], $_POST['port'], 50);
    $stats = $m->getStats();

    // configuration
    $priority = 50; // default priority

    if (array_key_exists($_POST['ip']. ':' .$_POST['port'], $stats))
    {
        // check if connection to server was successful
        if ($stats[$_POST['ip']. ':' .$_POST['port']]['pid'] == -1)
            ajax_exit(array('status' => 'failed', 'message' => localize('Server IP or address is invalid', 'cache')));

        $servers = $panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');

        foreach ($servers as $name => $config)
        {
            if ($config[0] == $_POST['ip'] and $config[1] == $_POST['port'])
            {
                ajax_exit(array('status' => 'success'));
            }
        }

        if (intval($_POST['priority']) > 0)
            $priority = intval($_POST['priority']);

        $servers[md5($_POST['ip'].$_POST['port'])] = array($_POST['ip'], $_POST['port'], $priority);
        $panthera -> config -> setKey('memcached_servers', $servers, 'array');
        ajax_exit(array('status' => 'success'));
    }

    ajax_exit(array('status' => 'failed', 'message' => localize('Server IP or address is invalid', 'cache')));

/**
  * Remove Memcached server
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'removeMemcachedServer') {
    $servers = $panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');
    $exp = explode(':', $_POST['server']);

    foreach ($servers as $name => $config)
    {
        if ($config[0] == $exp[0] and $config[1] == $exp[1])
        {
            unset($servers[$name]);
        }
    }

    $panthera -> config -> setKey('memcached_servers', $servers, 'array');
    ajax_exit(array('status' => 'success'));

/**
  * Clear cache
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'clear') {

    $result = False;

    switch ($_POST['type'])
    {
        case 'files':
            $result = apc_clear_cache('opcode');
        break;
        
        case 'variables':
            $result = apc_clear_cache('user');
        break;
        
        case 'varCache':
            if ($panthera -> varCache)
                $result = $panthera -> varCache -> clear();
        break;
        
        case 'cache':
            if ($panthera -> cache)
                $result = $panthera -> cache -> clear();
        break;
        
        case 'memcached':
            $id = $_POST['id'];
            
            $memcached = new pantheraMemcached($panthera);
            $stats = $memcached -> getStats();
            $serverPort = null;

            // get server and port from id
            $i = 0;
            foreach ($stats as $server => $attributes)
            {
                if ($id == $i)
                    $serverPort = $server;
                
                $i++;
            }

            if ($serverPort) {
                $server = explode(':', $serverPort);
                $m = new Memcached;
                $m -> addServer($server[0], $server[1]);
                $result = $m -> flush();
            }
        break;
        
        case 'xcache':
            $result = xcache_clear_cache(XC_TYPE_VAR, intval($_POST['id']));
        break;
    }
    
    if ($result)
        ajax_exit(array('status' => 'success', 'message' => localize('Done')));
    else
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot clear cache', 'cache'), 'dump' => object_dump($result)));
}


/**
  * APC statistics
  * Supported only by APC module. APCU still missess most of those features.
  *
  * @author Damian Kęska
  */

// Check if apc support is available in PHP, if yes show some statistics
if (extension_loaded('apc') && function_exists('apc_cache_info'))
{
    $info = apc_cache_info();

     // And create a popup with detailed list of files when clicking on a table title.
    if ($_GET['popup'] == 'apc')
    {
        foreach ($info['cache_list'] as $number => $file_info)
        {
            foreach ($file_info as $key => $value)
            {
                if ($key == 'filename')
                    $file[$number][$key] = basename($value);
                else
                    $file[$number][$key] = $value;
            }
        }

        $panthera -> template -> push('files', $file);
        $panthera -> template -> display('cache_files_apc.tpl');
        pa_exit();
    }

    if ($info['start_time'])
        $apcInfo['start_time'] = date($panthera -> dateFormat, $info['start_time']);
    else
        $apcInfo['start_time'] = '?';
    
    $apcInfo['cached_files'] = count($info['cache_list']);
    $apcInfo['num_hits'] = $info['num_hits'];
    $apcInfo['num_misses'] = $info['num_misses'];
    $apcInfo['module'] = 'APC';
    
    foreach ($apcInfo as $key => $value)
    {
        if (!$value)
            $apcInfo[$key] = '?';
    }
    
    if (extension_loaded('apcu'))
    {
        $apcInfo['module'] = 'APCu';
    }

    $panthera -> template -> push('acp_info', $apcInfo);
}


/**
  * List of Memcached servers
  *
  * @author Damian Kęska
  */

// Check if memcached support is avaliable in PHP, if yes import our wrapper library
$panthera -> template -> push('memcachedServers', array());

if (class_exists('Memcached'))
{
    $panthera -> importModule('filesystem');
    $panthera -> importModule('memcached');
    $memcached = new pantheraMemcached($panthera);

    /* Popup window with additional advanced informations */
    if ($_GET['popup'] == 'memcached')
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
        $panthera -> template -> display('cache_stats_memcached.tpl');
        pa_exit();
    }

    $stats = $memcached -> getStats();
    $maxLoad = 0;

    if (count($stats) > 1)
    {
        foreach ($stats as $server => $attributes)
        {
            $maxLoad += intval($attributes['cmd_get']) + intval($attributes['cmd_set']);
        }
    }


    $servers = array();
    $i=0;
    foreach ($stats as $server => $attributes)
    {
        $servers[$server] = array();
        $servers[$server]['num'] = $i++;
        $servers[$server]['uptime'] = date_calc_diff(time() - $attributes['uptime'], time());
        $servers[$server]['version'] = 'memcached ' .$attributes['version'];
        $servers[$server]['threads'] = $attributes['threads'];
        $servers[$server]['pid'] = $attributes['pid'];

        // total transfer
        $servers[$server]['read'] = filesystem::bytesToSize($attributes['bytes_read']);
        $servers[$server]['written'] = filesystem::bytesToSize($attributes['bytes_written']);

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
        $servers[$server]['memory_used'] = filesystem::bytesToSize($attributes['bytes']);
        $servers[$server]['memory_max'] = filesystem::bytesToSize($attributes['limit_maxbytes']);
        $servers[$server]['load_percent'] = (($attributes['cmd_get'] + $attributes['cmd_set'])/$maxLoad)*100;
    }

    $panthera -> template -> push('memcachedServers', $servers);
}


// Detection of APC, XCache and Memcached.
$cacheList = array('xcache' => False, 'apc' => False, 'memcached' => False, 'redis' => False);


/**
  * XCache support
  *
  * @author Damian Kęska
  */

// check for requirements for built-in caching methods
if (function_exists('xcache_set'))
{
    if (ini_get('xcache.admin.user') and ini_get('xcache.admin.pass'))
    {
        $xcacheInfo = array();
        
        for ($i=0; $i < xcache_count(XC_TYPE_VAR); $i++)
        {
            $info = xcache_info(XC_TYPE_VAR, $i);
            $xcacheInfo[$i] = array();
            $xcacheInfo[$i]['slots'] = $info['slots'];
            $xcacheInfo[$i]['cached'] = $info['cached'];
            $xcacheInfo[$i]['errors'] = $info['errors'];
            $xcacheInfo[$i]['deleted'] = $info['deleted'];
            
            // size
            $xcacheInfo[$i]['size'] = filesystem::bytesToSize($info['size']);
            
            $free = 0;
            foreach ($info['free_blocks'] as $block)
            {
                $free += $block['size'];
            }
            
            $xcacheInfo[$i]['free'] = filesystem::bytesToSize($free);
            $xcacheInfo[$i]['used'] = filesystem::bytesToSize($info['size']-$free);
            
            // hits and misses (usage)
            $xcacheInfo[$i]['hits'] = $info['hits'];
            $xcacheInfo[$i]['misses'] = $info['misses'];
            
            // stats
            $xcacheInfo[$i]['hourlyStats'] = $info['hits_by_hour'];
        }
        
        $panthera -> template -> push('xcacheInfo', $xcacheInfo);
    }
    $cacheList['xcache'] = True;
}




if (extension_loaded('apc'))
    $cacheList['apc'] = True;

if (class_exists('Memcached'))
{
    $cacheList['memcached'] = True;
    $panthera -> template -> push('memcacheAvaliable', True);
    
    if (extension_loaded('memcached'))
    {
        $panthera -> template -> push('memcachedSerializer', ini_get('memcached.serializer'));
        $panthera -> template -> push('memcachedCompression', ini_get('memcached.compression_type'));
    }
}

if (class_exists('Redis'))
{
    if ($panthera->cache)
    {
        if ($panthera->cache->name == 'redis')
        {
            $info = $panthera -> cache -> redis -> info();
            $hosts = '';
            
            foreach ($panthera->config->getKey('redis_servers') as $server)
            {
                $hosts .= $server['host']. ':' .$server['port']. ', ';
            }
            
            if (!$info['os'])
            {
                $info['os'] = localize('Unknown');
            }
            
            $redisInfo = array(
                'hosts' => rtrim($hosts, ', '),
                'clients' => $info['connected_clients'],
                'uptime' => date_calc_diff(time() - $info['uptime_in_seconds'], time()),
                'expiredKeys' => $info['expired_keys'],
                'hits' => $info['keyspace_hits'],
                'misses' => $info['keyspace_misses'],
                'usedMemory' => filesystem::bytesToSize($info['used_memory']),
                'commands' => $info['total_commands_processed'],
                'totalConnections' => $info['total_connections_received'],
                'role' => $info['role'],
                'slaves' => $info['connected_slaves'],
                'cpu' => $info['used_cpu_user'],
                'os' => $info['os'],
                'arch' => $info['arch_bits'],
                'pid' => $info['process_id'],
                'version' => $info['redis_version']
            );
            
            $panthera -> template -> push ('redisInfo', $redisInfo);
            $panthera -> template -> push ('redisServers', $panthera->config->getKey('redis_servers'));
        }
    }

    $cacheList['redis'] = True;
}


$cacheList['files'] = True;
$cacheList['db'] = True; // db is always available

// get list of avaliable cache methods from list of declared classes
foreach (pantheraAutoLoader::getClasses() as $key => $className)
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

// check if memcached is used as session save handler
if (ini_get('session.save_handler') != 'php')
    $panthera -> template -> push('sessionHandler', ini_get('session.save_handler'));
else
    $panthera -> template -> push('sessionHandler', localize('php default, on disk', 'cache'));

// allow plugins modyfing list
$cacheList = $panthera -> get_filters('cache.list', $cacheList);

// titlebar
$titlebar = new uiTitlebar(localize('Cache management', 'cache'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/cache.png', 'left');

$panthera -> template -> push('sessionSerializer', ini_get('session.serialize_handler'));
$panthera -> template -> push('cache', $panthera -> config -> getKey('cache_type'));
$panthera -> template -> push('varcache', $panthera -> config -> getKey('varcache_type'));
$panthera -> template -> push('cache_list', $cacheList);
$panthera -> template -> display('cache.tpl');
pa_exit();
