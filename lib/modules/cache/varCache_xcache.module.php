<?php
/**
  * XCache cache support
  *
  * @package Panthera\modules\cache\varCache_xcache
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * varCache stored in XCache PHP cache
  *
  * @package Panthera\modules\cache\varCache_xcache
  * @author Damian Kęska
  */

class varCache_xcache
{
    public $name = 'xcache';
    public $type = 'memory';
    protected $panthera;

    /**
     * Constructor
     *
     * @return null
     */

    public function __construct ($panthera, $sessionKey='')
    {
        $this->panthera = $panthera;

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
        if (!ini_get('xcache.admin.user') or !ini_get('xcache.admin.pass'))
            return FALSE;

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
        if(!is_int($expire))
            $expire = $this->panthera->getCacheTime($expire);

        if($expire < 1)
            $expire = 3600;

        if ($expire > -1)
            xcache_set($this->prefix.'.vc.' .$var, $value, $expire);
        else
            xcache_set($this->prefix.'.vc.' .$var, $value);

        return True;
    }
}