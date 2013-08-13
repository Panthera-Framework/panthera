<?php
/**
  * Cache support for Panthera Framework
  * 
  * @package Panthera\core\cache
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * varCache stored in database
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  */

if (!defined('IN_PANTHERA'))
    exit;
  
class varCache_db
{
    public $name = 'db';
    public $type = 'database';

    protected $cache = array (), $panthera;
 
    public function __construct($obj, $sessionKey='')
    {
        $this->panthera = $obj;
        
        if (!$this->panthera->config)
        {
            throw new Exception('varCache_db cannot be initialized from configuration from app.php');
        }
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
        // return from memory
        if (array_key_exists($var, $this->cache))
            return $this->cache[$var][0];
    
        $SQL = $this->panthera->db->query('SELECT `value`, `expire` FROM `{$db_prefix}var_cache` WHERE `var` = :var', array('var' => $var));
        
        if ($SQL->rowCount() == 1)
        {
            $Array = $SQL -> fetch();
            $v = unserialize($Array['value']);
            
            $this->cache[$var] = array($v, $Array['expire']); // update memory cache
            return $v;
        }
        
        return null;
    }
    
    /**
      * Get expiration time
      *
      * @param string $var Variable
      * @return mixed
      * @author Damian Kęska
      */
    
    /*public function getExpirationTime($var)
    {
        if (!$this->exists($var))
            return False;
            
        // return from memory cache
        return $this->cache[$var][1];
    }*/
    
    /**
      * Check if variable exists in the cache
      *
      * @param string $var Variable name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function exists($var)
    {
        if ($this->get($var) != null)
            return True;
            
        return False;
    }
    
    /**
      * Clear entire cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clear()
    {
        // sqlite dont have TRUNCATE TABLE command
        if ($this->panthera->db->getSocketType() == 'sqlite')
            $this->panthera -> db -> query ('DELETE FROM `{$db_prefix}var_cache`');
        else 
            $this->panthera->db->query('TRUNCATE TABLE `{$db_prefix}var_cache`');
            
        return True;
    }
    
    public function remove($var)
    {
        unset($this->cache[$var]);
        $SQL = $this -> panthera -> db -> query('DELETE FROM `{$db_prefix}var_cache` WHERE `var` = :var', array('var' => $var));
        return (bool)$SQL -> rowCount();
    }
    
    /**
      * Set variable value
      *
      * @param string $var Variable name
      * @param string $value Value
      * @param int $expire Expiration time in seconds
      * @return bool 
      * @author Damian Kęska
      */
    
    public function set($var, $value, $expire=-1)
    {
        if ($expire > 0 and is_int($expire))
            $expire = time()+$expire;
    
        if (!$this->exists($var))
        {
            $SQL = $this -> panthera -> db -> query ('INSERT INTO `{$db_prefix}var_cache` (`var`, `value`, `expire`) VALUES (:var, :value, :expire)', array('var' => $var, 'value' => serialize($value), 'expire' => $expire));
        } else {
        
            if ($expire > 0)
                $SQL = $this-> panthera -> db -> query ('UPDATE `{$db_prefix}var_cache` SET `value` = :value, `expire` = :expire WHERE `var` = :var', array('var' => $var, 'value' => serialize($value), 'expire' => $expire));
            else
                $SQL = $this-> panthera -> db -> query ('UPDATE `{$db_prefix}var_cache` SET `value` = :value WHERE `var` = :var', array('var' => $var, 'value' => serialize($value)));
        }
        
        $this->cache[$var] = $value;
        
        // true or false if affected any row
        return (bool)$SQL -> rowCount();
    }
}




/**
  * varCache stored in APC PHP cache
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  */

class varCache_apc extends pantheraClass
{
    public $name = 'apc';
    public $type = 'memory';
    
    public function __construct ($panthera, $sessionKey='')
    {
        parent::__construct($panthera);
        
        if ($panthera -> config)
            $this->prefix = $panthera -> config -> getKey('session_key');
        else
            $this->prefix = $sessionKey;
        
        if (!function_exists('apc_fetch'))
            throw new Exception('Cannot find APC module in PHP');
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
        return apc_exists($this->prefix.'.vc.' .$var);
    }

    /**
      * Get entry from APC cache
      *
      * @param string $var Cache variable
      * @return mixed 
      * @author Damian Kęska
      */

    public function get($var)
    {
        if (!$this->exists($var))
            return null;
            
        return apc_fetch($this->prefix.'.vc.' .$var);
    }
    
    /**
      * Remove variable from cache
      *
      * @param string $var Variable name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function remove ($var)
    {
        return apc_delete($this->prefix.'.vc.' .$var);
    }
    
    /**
      * Clear entire cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clear()
    {
        apc_clear_cache('user');
        return True;
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
        $value = $value;
    
        if ($expire > -1)
            apc_store($this->prefix.'.vc.' .$var, $value, $expire);
        else
            apc_store($this->prefix.'.vc.' .$var, $value);
            
        return True;
    }
}

if(!function_exists('apc_exists') and function_exists('apc_fetch'))
{
    /**
      * Backwards compatibility with old APC
      *
      * @param mixed $keys
      * @return bool 
      * @author Damian Kęska
      */

    function apc_exists($keys)
    {
        $result = False;
        apc_fetch($keys, $result);
        
        return $result;
    }
}


/**
  * varCache stored in xdebug PHP cache
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  */

class varCache_xcache extends pantheraClass
{
    public $name = 'xcache';
    public $type = 'memory';

    public function __construct ($panthera, $sessionKey='')
    {
        parent::__construct($panthera);
        
        if ($panthera -> config)
            $this->prefix = $panthera -> config -> getKey('session_key');
        else
            $this->prefix = $sessionKey;
        
        if (PANTHERA_MODE == 'CLI')
            throw new Exception('Xcache will not work in CLI mode.');
            
        if (!function_exists('xcache_isset'))
            throw new Exception('Cannot find XCache PHP extension');
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
        return (bool)$this->get($var);
    }
    
    /**
      * Clear entire cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clear()
    {
        xcache_clear_cache();
        return True;
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
        return (bool)$this->set($var, null, 1);
        //return xcache_unset($this->prefix.'.vc.' .$var); // i dont know why, but this is not working (bug?)
    }
    

    /**
      * Get entry from APC cache
      *
      * @param string $var Cache variable
      * @return mixed 
      * @author Damian Kęska
      */

    public function get($var)
    {
        $c = xcache_get($this->prefix.'.vc.' .$var);
        
        if ($c == null)
            return null;
            
        return $c;
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
        if ($expire > -1)
            xcache_set($this->prefix.'.vc.' .$var, $value, $expire);
        else
            xcache_set($this->prefix.'.vc.' .$var, $value);
            
        return True;
    }
}

if (class_exists('Memcache') and !class_exists('Memcached'))
{
    class Memcached extends Memcache
    {
        public function getStats()
        {
            return $this->getExtendedStats();
        }
    }
}

/**
  * varCache stored by memcached servers
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  */

class varCache_memcached extends pantheraClass
{
    public $name = 'memcached';
    public $type = 'memory';
    public $m = null;

    public function __construct ($panthera, $sessionKey='')
    {
        parent::__construct($panthera);
        
        if ($panthera -> config)
            $this->prefix = $panthera -> config -> getKey('session_key');
        else
            $this->prefix = $sessionKey;
        
        if (!class_exists('Memcached'))
            throw new Exception('Memcached support is not installed in PHP, cache will be disabled');
        
        $servers = $panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');
        $this->m = new Memcached();
        
        foreach ($servers as $server)
        {
            if (!is_array($server))
                continue;
                
            // host, port, weight
            $this -> m -> addServer($server[0], intval($server[1]), intval($server[2]));
        }
    }
    
    /**
      * Filter a var to avoid syntax errors
      *
      * @param string $var
      * @return string 
      * @author Damian Kęska
      */
    
    public function filterVar($var)
    {
        return hash('md4', $this->prefix.'.vc.' .$var);
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
        $this->m->get($this->filterVar($var));
        
        if ($this->m->getResultCode() == Memcached::RES_NOTFOUND)
            return False;
        
        return True;
    }
    
    /**
      * Clear entire cache
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function clear()
    {
        $this->m->flush();
        return True;
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
        return $this->m->delete($this->filterVar($var));
    }
    

    /**
      * Get entry from APC cache
      *
      * @param string $var Cache variable
      * @return mixed 
      * @author Damian Kęska
      */

    public function get($var)
    {
        if (!$this->exists($var))
            return null;
    
        $var = $this->filterVar($var);
        $value = $this->m->get($var);

        if ($value == null)
            return null;
            
        return $value;
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
        $var = $this->filterVar($var);
        
        if(!is_int($expire) or $expire < 1)
            $expire = 3600;
            
        $this->m->set($var, $value, $expire);
            
        return True;
    }
}

/**
  * varCache stored by Redis servers
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  */

class varCache_redis 
{
    public $name = 'redis';
    public $type = 'memory';
    public $redis;

    public function __construct($panthera, $sessionKey)
    {
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
        if(!is_int($expire) or $expire < 1)
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
