<?php
/**
  * Memcached/Memcache cache support
  * 
  * @package Panthera\modules\cache\memcached
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (class_exists('Memcache') and !class_exists('Memcached'))
{
    /**
      * Memcached compatibility layer
      *
      * @package Panthera\modules\cache\memcached
      * @author Damian Kęska
      */

    class Memcached extends Memcache
    {
        public function __construct()
        {
            // do nothing
        }
        
        public function getStats()
        {
            return $this->getExtendedStats();
        }
        
        public function set($key, $value, $expire)
        {
            if ($this->get($key) === False)
            {
                return parent::set($key, $value, false, $expire);
            } else {
                return $this->replace($key, $value, false, $expire);
            }
        }
    }
}

/**
  * varCache stored by memcached servers
  *
  * @package Panthera\modules\cache\memcached
  * @author Damian Kęska
  */

class varCache_memcached
{
    public $name = 'memcached';
    public $type = 'memory';
    public $m = null;
    protected $panthera;

    public function __construct ($panthera, $sessionKey='')
    {
        $this->panthera = $panthera;
        
        if ($panthera -> config)
            $this->prefix = $panthera -> config -> getKey('session_key');
        else
            $this->prefix = $sessionKey;
        
        if (!class_exists('Memcached'))
            throw new Exception('Memcached support is not installed in PHP, cache will be disabled');
        
        $servers = $panthera -> config -> getKey('memcached_servers', array('default' => array('localhost', 11211, 50)), 'array');
        $this->m = new Memcached();
        $serversConnected = 0;
        
        foreach ($servers as $server)
        {
            if (!is_array($server))
                continue;
                
            // host, port, weight
            if (!$this -> m -> addServer($server[0], intval($server[1]), intval($server[2])))
            {
                $panthera -> logging -> output('Cannot connected to Memcached server ' .$server[0]. ':' .$server[1], 'pantheraCache');
                continue;
            }
            
            $serversConnected++;
        }
        
        if ($serversConnected == 0)
            throw new Exception('Cannot initialize Memcached, no servers connected');
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
        //return str_replace(' ', '', $this->prefix.'.vc.' .$var);
        return substr(hash('md4', $this->prefix.'.vc.' .$var), 0, 9);
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
        $result = $this->m->get($this->filterVar($var));
        
        if (method_exists($this->m, 'getResultCode'))
        {
            if ($this->m->getResultCode() == Memcached::RES_NOTFOUND)
                return False;
        } else {
            if ($result === false)
                return False;
        }
        
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
        //$this -> panthera -> logging -> output('DEL ' .$this->filterVar($var). ' (' .$var. ')', 'cache');
        return $this->m->delete($this->filterVar($var));
    }
    

    /**
      * Get entry from server cache
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
            
        if ($value == '_$bool_$False')
            $value = False;
            
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
     
        if(!is_int($expire))
            $expire = $this->panthera->getCacheTime($expire);
        
        if($expire < 1)
            $expire = 3600;
            
        if ($value === False)
            $value = '_$bool_$False';
            
        //$this->panthera->logging->output('SET ' .$var. ', expire=' .$expire, 'cache');
            
        return $this->m->set($var, $value, $expire);
    }
}