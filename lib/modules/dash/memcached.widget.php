<?php
/**
 * Memcached statistics dash widget
 *
 * @package Panthera\core\modules\cache
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */


if (!defined('IN_PANTHERA'))
    exit;

/**
 * Memcached statistics dash widget
 *
 * @package Panthera\core\modules\cache
 */

class memcached_dashWidget extends pantheraClass
{
    /**
     * Main function that display widget
     *
     * @return string
     */

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
                $usage = strval($attributes['bytes'] / $attributes['limit_maxbytes']);

                if (!$usage)
                    $servers[$server]['memory_usage'] = localize('Not connected', 'dash');
                else
                    $servers[$server]['memory_usage'] = substr($usage, 0, 4)."%";
            }

            $this -> panthera -> template -> push ('memcachedServers', $servers);
            return $this -> panthera -> template -> compile('dashWidgets/memcached.tpl');
        }
    }
}