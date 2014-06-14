<?php
/**
 * Cache management
 *
 * @package Panthera\core\system\cache
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

if (!defined('IN_PANTHERA'))
	exit ;

/**
 * Cache management page controller
 *
 * @package Panthera\modules\cache
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

class cacheAjaxControllerSystem extends pageController {
	protected $permissions = 'can_manage_cache';

	protected $uiTitlebar = array('Cache management', 'cache');

	protected $cacheList = array();



	/**
	 * Saving cache and varCache settings
	 *
	 * @author Mateusz Warzyński
	 */

	public function saveAction() {
		$cache = $_POST['cache'];
		$varcache = $_POST['varcache'];

		if ($dir = getContentDir('modules/cache/varCache_' . $cache . '.module.php'))
			include_once $dir;

		// check if selected cache is avaliable
		if (!class_exists('varCache_' . $cache))
			ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find class "varCache_' . $cache . '" for this caching method', 'cache')));

		if (!class_exists('varCache_' . $varcache))
			ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find class "varCache_' . $varcache . '" for this caching method', 'cache')));

		// clear cache
		if ($cache != $this -> panthera -> config -> getKey("cache_type")) {
			if ($this -> panthera -> cache)
				$this -> panthera -> cache -> clear();
		}

		// and so, clear varCache too
		if ($varcache != $this -> panthera -> config -> getKey("varcache_type")) {
			if ($this -> panthera -> varCache)
				$this -> panthera -> varCache -> clear();
		}

		// refresh cache
		$this -> panthera -> loadCache($varcache, $cache, $this -> panthera -> config -> getKey('session_key'));

		// and again...
		if ($cache != $this -> panthera -> config -> getKey("cache_type")) {
			if ($this -> panthera -> cache) {
				$this -> panthera -> cache -> clear();
				$this -> panthera -> config -> setKey("cache_type", $cache, 'string');
			}
		}

		if ($varcache != $this -> panthera -> config -> getKey("varcache_type")) {
			if ($this -> panthera -> varCache) {
				$this -> panthera -> varCache -> clear();
				$this -> panthera -> config -> setKey("varcache_type", $varcache, 'string');
			}
		}

		ajax_exit(array('status' => 'success'));
	}



	/**
	 * Add new Redis server
	 *
	 * @author Damian Kęska
	 */

	public function addRedisServerAction() {
		// TODO: Support UNIX-socket connections

		$config = $this -> panthera -> config -> getKey('redis_servers');
		$persistent = False;

		if ($_POST['persistent'] == "1")
			$persistent = True;

		$found = False;
		foreach ($config as $server) {
			if ($server['host'] == $_POST['ip'] and $server['port'] == $_POST['port'])
				ajax_exit(array('status' => 'failed', 'message' => localize('Server is already on the list', 'cache')));
		}

		try {
			$r = new Redis();
			$r -> connect($_POST['ip'], $_POST['port']);

			if ($r -> info())
				$config[] = array('host' => $_POST['ip'], 'port' => $_POST['port'], 'persistent' => $persistent, 'socket' => False);

			$this -> panthera -> config -> setKey('redis_servers', $config, 'array');
		} catch (Exception $e) {
			// nothing
			ajax_exit(array('status' => 'failed', 'message' => localize('Cannot connect to Redis server', 'cache')));
		}

		ajax_exit(array('status' => 'success'));
	}



	/**
	 * Remove a Redis server
	 *
	 * @author Damian Kęska
	 */

	public function removeRedisServerAction() {
		$details = explode(':', $_POST['address']);
		$config = $this -> panthera -> config -> getKey('redis_servers');

		foreach ($config as $key => $server) {
			if ($server['host'] == $details[0] and $server['port'] == $details[1]) {
				unset($config[$key]);
				$this -> panthera -> config -> setKey('redis_servers', $config, 'array');
				ajax_exit(array('status' => 'success'));
			}
		}

		ajax_exit(array('status' => 'failed'));
	}



	/**
	 * Adding new Memcached server
	 *
	 * @author Damian Kęska
	 * @author Mateusz Warzyński
	 */

	public function addMemcachedServerAction() {
		if (!class_exists('Memcached'))
			ajax_exit(array('status' => 'failed'));

		$m = new Memcached();
		$m -> addServer($_POST['ip'], $_POST['port'], 50);
		$stats = $m -> getStats();

		// configuration
		$priority = 50;
		// default priority

		if (array_key_exists($_POST['ip'] . ':' . $_POST['port'], $stats)) {
			// check if connection to server was successful
			if ($stats[$_POST['ip'] . ':' . $_POST['port']]['pid'] == -1)
				ajax_exit(array('status' => 'failed', 'message' => localize('Server IP or address is invalid', 'cache')));

			$servers = $this -> panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');

			foreach ($servers as $name => $config) {
				if ($config[0] == $_POST['ip'] and $config[1] == $_POST['port'])
					ajax_exit(array('status' => 'success'));
			}

			if (intval($_POST['priority']) > 0)
				$priority = intval($_POST['priority']);

			$servers[md5($_POST['ip'] . $_POST['port'])] = array($_POST['ip'], $_POST['port'], $priority);
			$this -> panthera -> config -> setKey('memcached_servers', $servers, 'array');
			ajax_exit(array('status' => 'success'));
		}

		ajax_exit(array('status' => 'failed', 'message' => localize('Server IP or address is invalid', 'cache')));
	}



	/**
	 * Remove Memcached server
	 *
	 * @author Damian Kęska
	 * @author Mateusz Warzyński
	 */

	public function removeMemcachedServerAction() {
		$servers = $this -> panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');
		$exp = explode(':', $_POST['server']);

		foreach ($servers as $name => $config) {
			if ($config[0] == $exp[0] and $config[1] == $exp[1])
				unset($servers[$name]);
		}

		$this -> panthera -> config -> setKey('memcached_servers', $servers, 'array');
		ajax_exit(array('status' => 'success'));
	}



	/**
	 * Clear cache
	 *
	 * @author Damian Kęska
	 * @author Mateusz Warzyński
	 */

	public function clearAction() {

		$result = False;

		switch ($_POST['type']) {
			case 'files' :
				$result = apc_clear_cache('opcode');
				break;

			case 'variables' :
				$result = apc_clear_cache('user');
				break;

			case 'varCache' :
				if ($this -> panthera -> varCache)
					$result = $this -> panthera -> varCache -> clear();
				break;

			case 'cache' :
				if ($this -> panthera -> cache)
					$result = $this -> panthera -> cache -> clear();
				break;

			case 'memcached' :
				$id = $_POST['id'];

				$memcached = new pantheraMemcached($this -> panthera);
				$stats = $memcached -> getStats();
				$serverPort = null;

				// get server and port from id
				$i = 0;
				foreach ($stats as $server => $attributes) {
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

			case 'xcache' :
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

	protected function apcSupport() {
		$info = apc_cache_info();

		// And create a popup with detailed list of files when clicking on a table title.
		if ($_GET['popup'] == 'apc') {
			foreach ($info['cache_list'] as $number => $file_info) {
				foreach ($file_info as $key => $value) {
					if ($key == 'filename')
						$file[$number][$key] = basename($value);
					else
						$file[$number][$key] = $value;
				}
			}

			$this -> panthera -> template -> push('files', $file);
			$this -> panthera -> template -> display('cache_files_apc.tpl');
			pa_exit();
		}

		if ($info['start_time'])
			$apcInfo['start_time'] = date($this -> panthera -> dateFormat, $info['start_time']);
		else
			$apcInfo['start_time'] = '?';

		$apcInfo['cached_files'] = count($info['cache_list']);
		$apcInfo['num_hits'] = $info['num_hits'];
		$apcInfo['num_misses'] = $info['num_misses'];
		$apcInfo['module'] = 'APC';

		foreach ($apcInfo as $key => $value) {
			if (!$value)
				$apcInfo[$key] = '?';
		}

		if (extension_loaded('apcu'))
			$apcInfo['module'] = 'APCu';

		$this -> panthera -> template -> push('acp_info', $apcInfo);
	}



	/**
	 * List of Memcached servers
	 *
	 * @author Damian Kęska
	 * @author Mateusz Warzyński
	 */

	protected function memcachedSupport() {
		$memcached = new pantheraMemcached($this -> panthera);

		/* Popup window with additional advanced informations */
		if ($_GET['popup'] == 'memcached') {
			$serverName = $_GET['server'];
			$stats = $memcached -> getStats();

			if (!array_key_exists($serverName, $stats)) {
				$this -> panthera -> template -> display('no_page.tpl');
				pa_exit();
			}

			$serverStats = $stats[$serverName];

			$this -> panthera -> template -> push('server', $serverName);
			$this -> panthera -> template -> push('stats', $serverStats);
			$this -> panthera -> template -> display('cache_stats_memcached.tpl');
			pa_exit();
		}

		$stats = $memcached -> getStats();
		$maxLoad = 0;

		if (count($stats) > 1) {
			foreach ($stats as $server => $attributes)
				$maxLoad += intval($attributes['cmd_get']) + intval($attributes['cmd_set']);
		}

		$servers = array();
		$i = 0;
		foreach ($stats as $server => $attributes) {
			$servers[$server] = array();
			$servers[$server]['num'] = $i++;
			$servers[$server]['uptime'] = date_calc_diff(time() - $attributes['uptime'], time());
			$servers[$server]['version'] = 'memcached ' . $attributes['version'];
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

            if ($maxLoad != 0 )
			    $servers[$server]['load_percent'] = (($attributes['cmd_get'] + $attributes['cmd_set']) / $maxLoad) * 100;
            else
                $servers[$server]['load_percent'] = localize('Unknown');
		}

		$this -> panthera -> template -> push('memcachedServers', $servers);
	}




	/**
	 * XCache support
	 *
	 * @author Damian Kęska
	 * @author Mateusz Warzyński
	 */

	protected function xcacheSupport() {
		if (ini_get('xcache.admin.user') and ini_get('xcache.admin.pass')) {
			$xcacheInfo = array();

			for ($i = 0; $i < xcache_count(XC_TYPE_VAR); $i++) {
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
					$free += $block['size'];

				$xcacheInfo[$i]['free'] = filesystem::bytesToSize($free);
				$xcacheInfo[$i]['used'] = filesystem::bytesToSize($info['size'] - $free);

				// hits and misses (usage)
				$xcacheInfo[$i]['hits'] = $info['hits'];
				$xcacheInfo[$i]['misses'] = $info['misses'];

				// stats
				$xcacheInfo[$i]['hourlyStats'] = $info['hits_by_hour'];
			}

			$this -> panthera -> template -> push('xcacheInfo', $xcacheInfo);
		}

		$this -> cacheList['xcache'] = True;
	}



	/**
	 * Redis support
	 *
	 * @author Damian Kęska
	 */

	protected function RedisSupport() {
		if ($this -> panthera -> cache) {
			if ($this -> panthera -> cache -> name == 'redis') {
				$info = $this -> panthera -> cache -> redis -> info();
				$hosts = '';

				foreach ($this->panthera->config->getKey('redis_servers') as $server)
					$hosts .= $server['host'] . ':' . $server['port'] . ', ';

				if (!$info['os'])
					$info['os'] = localize('Unknown');

				$redisInfo = array('hosts' => rtrim($hosts, ', '), 'clients' => $info['connected_clients'], 'uptime' => date_calc_diff(time() - $info['uptime_in_seconds'], time()), 'expiredKeys' => $info['expired_keys'], 'hits' => $info['keyspace_hits'], 'misses' => $info['keyspace_misses'], 'usedMemory' => filesystem::bytesToSize($info['used_memory']), 'commands' => $info['total_commands_processed'], 'totalConnections' => $info['total_connections_received'], 'role' => $info['role'], 'slaves' => $info['connected_slaves'], 'cpu' => $info['used_cpu_user'], 'os' => $info['os'], 'arch' => $info['arch_bits'], 'pid' => $info['process_id'], 'version' => $info['redis_version']);

				$this -> panthera -> template -> push('redisInfo', $redisInfo);
				$this -> panthera -> template -> push('redisServers', $this -> panthera -> config -> getKey('redis_servers'));
			}
		}

		$this -> cacheList['redis'] = True;
	}



	/**
	 * Display main page
	 *
	 * @author Damian Kęska
	 * @author Mateusz Warzyński
	 */

	public function display() {
		$this -> panthera -> locale -> loadDomain('cache');

		$this -> dispatchAction();

		if (extension_loaded('apc') && function_exists('apc_cache_info'))
			$this -> apcSupport();

		$this -> panthera -> template -> push('memcachedServers', array());
		if (class_exists('Memcached'))
			$this -> memcachedSupport();

		// Detection of APC, XCache and Memcached.
		$this -> cacheList = array('xcache' => False, 'apc' => False, 'memcached' => False, 'redis' => False);

		if (function_exists('xcache_set'))
			$this -> xcacheSupport();

		if (extension_loaded('apc'))
			$this -> cacheList['apc'] = True;

		if (class_exists('Memcached')) {
			$this -> cacheList['memcached'] = True;
			$this -> panthera -> template -> push('memcacheAvaliable', True);

			if (extension_loaded('memcached')) {
				$this -> panthera -> template -> push('memcachedSerializer', ini_get('memcached.serializer'));
				$this -> panthera -> template -> push('memcachedCompression', ini_get('memcached.compression_type'));
			}
		}

		if (class_exists('Redis'))
			$this -> RedisSupport();

		$this -> cacheList['files'] = True;
		$this -> cacheList['db'] = True;
		// db is always available

		$this -> panthera -> importModule('autoloader.tools');

		// get list of avaliable cache methods from list of declared classes
		foreach (pantheraAutoLoader::getClasses() as $key => $className) {
			if (substr($className, 0, 9) == 'varCache_') {
				$cacheName = substr($className, 9);

				// if cache is not on a list, add it
				if (!array_key_exists($cacheName, $this -> cacheList))
					$this -> cacheList[$cacheName] = True;
			}
		}

		// check if memcached is used as session save handler
		if (ini_get('session.save_handler') != 'php')
			$this -> panthera -> template -> push('sessionHandler', ini_get('session.save_handler'));
		else
			$this -> panthera -> template -> push('sessionHandler', localize('php default, on disk', 'cache'));

		// allow plugins modyfing list
		$this -> cacheList = $this -> panthera -> get_filters('cache.list', $this -> cacheList);

		$this -> panthera -> template -> push('sessionSerializer', ini_get('session.serialize_handler'));
		$this -> panthera -> template -> push('cache', $this -> panthera -> config -> getKey('cache_type'));
		$this -> panthera -> template -> push('varcache', $this -> panthera -> config -> getKey('varcache_type'));
		$this -> panthera -> template -> push('cache_list', $this -> cacheList);

		return $this -> panthera -> template -> compile('cache.tpl');
	}

}