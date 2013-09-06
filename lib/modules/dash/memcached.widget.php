<?php
/**
  * Memcached widget
  *
  * @param string 
  * @return mixed 
  * @author Mateusz Warzyński
  */

  
if (!defined('IN_PANTHERA'))
    exit;
  
class memcached_dashWidget extends pantheraClass
{
    public function display()
    {
			
		if (class_exists('Memcached'))
		{
		    $this -> panthera -> importModule('filesystem');
		    $this -> panthera -> importModule('memcached');
			
			$memcached = new pantheraMemcached($this -> panthera);
			
			$stats = $memcached -> getStats();
		
		    $servers = array();
		    $i=0;
		    foreach ($stats as $server => $attributes)
		    {
		        $servers[$server] = array();
		        $servers[$server]['num'] = $i++;
		
		        // percent of memory usage
		        $usage = strval(bytesToSize($attributes['bytes']) / bytesToSize($attributes['limit_maxbytes']));
				
				if (!$usage)
					$servers[$server]['memory_usage'] = localize('Not connected', 'dash');
				else
		        	$servers[$server]['memory_usage'] = substr($usage, 0, 4)."%";
		    } 
		
			$this -> panthera -> template -> push ('memcachedServers', $servers);
		}
    }
}
