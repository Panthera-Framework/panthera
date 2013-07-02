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
  
class varCache_db
{
    public $name = 'db';
    public $type = 'database';

    protected $cache = array (), $panthera;
 
    public function __construct($obj)
    {
        $this->panthera = $obj;
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
    
    public function getExpirationTime($var)
    {
        if (!$this->exists($var))
            return False;
            
        // return from memory cache
        return $this->cache[$var][1];
    }
    
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
    
    public function __construct ($panthera)
    {
        parent::__construct($panthera);
        $this->prefix = $panthera -> config -> getKey('session_key');
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
            
        return unserialize(apc_fetch($this->prefix.'.vc.' .$var));
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
        $value = serialize($value);
    
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

    public function __construct ($panthera)
    {
        parent::__construct($panthera);
        $this->prefix = $panthera -> config -> getKey('session_key');
        
        if (PANTHERA_MODE == 'CLI')
            throw new Exception('Xcache will not work in CLI mode.');
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
        return xcache_isset($this->prefix.'.vc.' .$var);
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
        return xcache_unset($this->prefix.'.vc.' .$var);
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
            
        return unserialize(xcache_get($this->prefix.'.vc.' .$var));
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
        $value = serialize($value);
    
        if ($expire > -1)
            xcache_set($this->prefix.'.vc.' .$var, $value, $expire);
        else
            xcache_set($this->prefix.'.vc.' .$var, $value);
            
        return True;
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

    public function __construct ($panthera)
    {
        parent::__construct($panthera);
        $this->prefix = $panthera -> config -> getKey('session_key');
        
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
      * Check if variable exists in the cache
      *
      * @param string $var Variable
      * @return bool 
      * @author Damian Kęska
      */

    public function exists($var)
    {
        $this->m->get($var);
        
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
        return $this->m->delete($this->prefix.'.vc.' .$var);
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
            
        return unserialize($this->m->get($this->prefix.'.vc.' .$var));
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
        $value = serialize($value);
    
        if ($expire > -1)
            $this->m->set($this->prefix.'.vc.' .$var, $value, $expire);
        else
            $this->m->get($this->prefix.'.vc.' .$var, $value);
            
        return True;
    }
}
