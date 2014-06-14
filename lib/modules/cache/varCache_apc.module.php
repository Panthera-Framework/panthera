<?php
/**
  * APC cache support
  *
  * @package Panthera\modules\cache\varCache_apc
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * varCache stored in APC PHP cache
  *
  * @package Panthera\modules\cache\varCache_apc
  * @author Damian Kęska
  */

class varCache_apc
{
    public $name = 'apc';
    public $type = 'memory';
    protected $panthera;

    public function __construct ($panthera, $sessionKey='')
    {
        $this->panthera = $panthera;

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
        if(!is_int($expire))
            $expire = $this->panthera->getCacheTime($expire);

        if($expire < 1)
            $expire = 3600;

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