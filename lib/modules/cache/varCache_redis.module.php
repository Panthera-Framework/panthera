<?php
/**
  * Redis cache server's support
  * 
  * @package Panthera\modules\cache\varCache_redis
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * varCache stored by Redis servers
  *
  * @package Panthera\modules\cache\varCache_redis
  * @author Damian Kęska
  */

class varCache_redis 
{
    public $name = 'redis';
    public $type = 'memory';
    public $redis;
    protected $panthera;

    public function __construct($panthera, $sessionKey)
    {
        $this->panthera = $panthera;
        
        if (!class_exists('Redis'))
            throw new Exception('Warning: Redis PHP module is not loaded, cache cannot be initialized');
            
        $this->redis = new Redis();
        
        $default = array(
                      array(
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'persistent' => True, // persistent?
                        'socket' => False
                      )
                    );
        
        $servers = $panthera -> config -> getKey('redis_servers', $default, 'array');
        
        foreach ($servers as $server)
        {
            // connect through UNIX socket
            if ($server['socket'] != False)
            {
                $this->redis->pconnect($server['socket']);
                continue;
            }
            
            // is a persistent connection?
            if ($server['persistent'] == True)
                $this->redis->pconnect($server['host'], $server['port']);
            else
                $this->redis->connect($server['host'], $server['port']);
        }
        
        // use PHP serializer by default
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        
        if (extension_loaded('igbinary') and $panthera->config->getKey('redisIgbinary') == True)
        {
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
        }
        
        if (!$this->redis)
        {
            throw new Exception('Cannot connect to one or more Redis nodes, disabling cache');
        }
    }
    
    /**
      * Set variable value
      *
      * @param string $var Variable name
      * @param string $value Value
      * @return mixed 
      * @author Damian Kęska
      */
      
    public function set($var, $value, $expire=-1)
    {
        if(!is_int($expire))
            $expire = $this->panthera->getCacheTime($expire);
        
        if($expire < 1)
            $expire = 3600;
            
        $this->redis->set($var, $value);
        $this->redis->setTimeout($var, $expire);
        return True;
    }
    
    /**
      * Check if variable exists in the cache
      *
      * @param string $var Variable
      * @return bool 
      * @author Damian Kęska
      */
    
    public function exists($var)
    {
        return $this->redis->exists($var);
    }
    
    /**
      * Get entry from cache
      *
      * @param string $var Cache variable
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function get($var)
    {
        if (!$this->exists($var))
            return null;
        
        return $this->redis->get($var);
    }
    
    /**
      * Remove variable from cache
      *
      * @param string $var Variable name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function remove($var)
    {
        return $this->redis->delete($var);
    }
    
    /**
      * Clear entire cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clear()
    {
        $this->redis->flushAll();
    }
}
