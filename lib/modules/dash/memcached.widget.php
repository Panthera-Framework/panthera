<?php
/**
  * Cronjobs widget
  *
  * @param string 
  * @return mixed 
  * @author Mateusz Warzyński
  */
  
  /*
 
   		The widget should show load of all Memcached servers connected to Panthera.

	Displaying load of all servers
	If there are no servers configured or memcached is disabled - display information
	Clickable links to cache configuration page
 
   */
  
if (!defined('IN_PANTHERA'))
    exit;
  
class memcached_dashWidget extends pantheraClass
{
    public function display()
    {
		/*
		   1. Displaying load of all servers
		   2. If there are no servers configured or memcached is disabled - display information
	       3. Clickable links to cache configuration page 
		 */
		
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
		
		        // memory usage
		        $servers[$server]['memory_usage'] = substr(strval(bytesToSize($attributes['bytes']) / bytesToSize($attributes['limit_maxbytes'])), 0, 4)."%";
		    } 
		
			$this -> panthera -> template -> push ('memcachedServers', $servers);
		}
    }
}
