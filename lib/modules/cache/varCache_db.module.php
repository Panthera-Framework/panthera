<?php
/**
  * Database cache support
  *
  * @package Panthera\core\cache
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

class varCache_db
{
    public $name = 'db';
    public $type = 'database';
    protected $cache = array ();
    protected $panthera;

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
      * @return mixed Key's value or null if key was not found
      * @author Damian Kęska
      */

    public function get($var, $dontRemove=False)
    {
        // return from memory
        if (isset($this->cache[$var]))
        {
            if (intval($this->cache[$var][1]) < time() and intval($this->cache[$var][1]) !== -1 and !$dontRemove)
            {
                $this -> remove($var);
                return null;
            }

            return $this->cache[$var][0];
        }

        // get from database
        $SQL = $this->panthera->db->query('SELECT `value`, `expire` FROM `{$db_prefix}var_cache` WHERE `var` = :var', array('var' => $var));

        if ($SQL -> rowCount() > 0)
        {
            $Array = $SQL -> fetch();

            // if key is expired, remove it
            if (intval($Array['expire']) < time() and intval($Array['expire']) !== -1 and !$dontRemove)
            {
                unset($this -> cache[$var]);
                $this -> remove($var);
                return null;
            }

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

    public function exists($var, $dontRemove=False)
    {
        if ($this->get($var, $dontRemove) !== null)
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
        if(!is_int($expire) and $expire)
            $expire = $this->panthera->getCacheTime($expire);

        if($expire === 0 or $expire === '')
            $expire = 3600;

        if ($expire > 0)
            $expire = time()+$expire;
        elseif ($expire < 0)
            $expire = -1;

        try {
            $this->cache[$var] = $value;

            if (!$this->exists($var, True))
            {
                $SQL = $this -> panthera -> db -> query ('INSERT INTO `{$db_prefix}var_cache` (`var`, `value`, `expire`) VALUES (:var, :value, :expire)', array('var' => $var, 'value' => serialize($value), 'expire' => $expire));
            } else {
                $SQL = $this-> panthera -> db -> query ('UPDATE `{$db_prefix}var_cache` SET `value` = :value, `expire` = :expire WHERE `var` = :var', array('var' => $var, 'value' => serialize($value), 'expire' => $expire));
            }

            // true or false if affected any row
            return (bool)$SQL -> rowCount();

        } catch (Exception $e) {
            $this -> panthera -> logging -> output('Something went wrong in database varCache (database exception: ' .$e->getMessage(). ') set for var=' .$var, 'cache');
        }
    }
}