<?php
/**
  * Session management, navigation, "run" table
  * 
  * @package Panthera\core\session
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

    
/**
  * User session management class
  *
  * @package Panthera\core\session
  * @author Damian Kęska
  */
    
class pantheraSession
{
    protected $sessionKey, $panthera, $_cookies;

    /**
     * Constructor, adds session key etc.
     *
     * @return void
     * @author Damian Kęska
     */


    public function __construct ($panthera)
    {
        $this->panthera = $panthera;
        $this->sessionKey = $this->panthera->config->getKey('session_key');
        $this->_cookies = new pantheraCookie($this->sessionKey, $this->panthera);

        // Security: Check user-agent
        if ($panthera->config->getKey('session_useragent', True, 'bool'))
        {
            if (!$this->browserCheck())
            {
                $check = $panthera->get_filters('session_security_remove', 'BROWSER_CHECK_FAILED');

                if ($check == True and is_bool($check))
                    return True;

                $this->removeSession();
            }
        }

        // Security: Check session life-time (default is 1 hour = 3600 seconds)
        if ((int)$panthera->config->getKey('session_lifetime', '3600', 'int') > 0 and $this->exists('time'))
        {
            if (!$this->lifetimeCheck())
            {
                $check = $panthera->get_filters('session_security_remove', 'LIFETIME_EXCEEDED');

                if ($check == True and is_bool($check))
                    return True;

                $this->removeSession();
            }
        }

        // Security: store current IP adress and time
        $this->set('addr', $_SERVER['REMOTE_ADDR']);
        $this->set('time', time());

        // Security: store all used IP adresses in this session
        $addrs = $this->get('s_addrs');
        $addrs[$_SERVER['REMOTE_ADDR']] = True;
        $this->set('s_addrs', $addrs);
    }

    /**
     * Remove all user session variables
     *
     * @return bool
     * @author Damian Kęska
     */


    public function removeSession()
    {
        unset($_SESSION[$this->sessionKey]);
        return True;
    }

    /**
     * Validate session life time, if exceeded return true
     *
     * @return bool
     * @author Damian Kęska
     */

    public function lifetimeCheck()
    {
        $max = (int)$this->panthera->config->getKey('session_lifetime', '3600', 'int');

        if ((time() - $this->get('time')) >= $max)
            return False;

        return True;
    }

    /**
     * Perform a browser "User-agent" check (we cant change browser during session, only hijacked sessions can have diffirent user-agent string). So the cross-browser cookies importing will not work here.
     *
     * @return bool
     * @author Damian Kęska
     */

    public function browserCheck()
    {
        if ($this->exists('browser'))
        {
            if ($this->get('browser') == $_SERVER['HTTP_USER_AGENT'])
                return True;

            return False;
        } else {
            // if its our first time we are in this session
            $this->set('browser', $_SERVER['HTTP_USER_AGENT']);
            return True;
        }
    }

    /**
     * Check if variable exists
     *
     * @param string $cookieName Cookie name
     * @return bool
     * @author Damian Kęska
     */

    public function exists($cookieName)
    {
        if (@array_key_exists($cookieName, $_SESSION[$this->sessionKey]))
            return True;

        return False;
    }


    /**
     * Get session variable
     *
     * @param string $key Variable name
     * @return mixed
     * @author Damian Kęska
     */


    public function __get($key)
    {
        if ($key == 'cookies')
            return $this->_cookies;

        if (array_key_exists($key, $_SESSION[$this->sessionKey]))
            return $_SESSION[$this->sessionKey][$key];

        return NULL;
    }

    /**
     * Return all saved variables
     *
     * @param string $key Variable name
     * @return mixed
     * @author Damian Kęska
     */


    public function getAll($key)
    {
        return $_SESSION[$this->sessionKey];
    }


    /**
     * Remove session variable
     *
     * @return bool
     * @author Damian Kęska
     */

    public function remove($key)
    {
        unset($_SESSION[$this->sessionKey][$key]);
        return True;
    }

    /**
     * Set session variable
     *
     * @param string $key Variable name
     * @param string $value Value
     * @return mixed
     * @author Damian Kęska
     */

    public function __set($key, $value)
    {
        if ($key == 'cookies')
            return False;

        // dont accept integers, arrays or objects
        if (!is_string($key))
            return False;

        $_SESSION[$this->sessionKey][$key] = $value;

        return True;
    }

    /**
     * Dump session to file
     *
     * @return string
     * @author Damian Kęska
     */

    public function dump()
    {
        return serialize($_SESSION[$this->sessionKey]);
    }

    /**
     * Restore dumped session
     *
     * @return bool
     * @author Damian Kęska
     */

    public function restore($dumpedArray)
    {
        $array = @unserialize($dumpedArray);

        if (is_array($array))
        {
            $_SESSION[$this->sessionKey] = $array;
            return True;
        }

        return False;
    }

    // aliases
    public function get($key) { return $this->__get($key); }
    public function set($key, $value) { return $this->__set($key, $value); }
}

/**
  * Cookie support extension for session management
  *
  * @package Panthera\core\session
  * @author Damian Kęska
  */

class pantheraCookie
{
    protected $cookieKey, $panthera, $encryption = False, $ivsize = 16, $encryptionVector = '', $encryptionKey = '';

    public function __construct($cookieKey, $panthera)
    {
        $this->panthera = $panthera;
        $this->cookieKey = substr(md5($cookieKey), 0, 6);

        // Security: Encrypt cookies with AES-128 bit in CBC mode if possible
        if ($panthera->config->getKey('cookie_encrypt', "False", 'bool') and function_exists('mcrypt_encrypt'))
        {
            $this->encryptionKey = base64_decode($panthera->config->getKey('cookie_encrypt_key'));

            // create new encryption key
            if (!$this->encryptionKey)
            {
                $this->encryptionKey = hash("SHA256", rand(999999, 9999999999), True);
                $panthera->config->setKey('cookie_encrypt_key', base64_encode($this->encryptionKey), "string");
            }

            $this->encryption = True;

            // length of initialization vector
            $this->ivsize = mcrypt_get_iv_size('rijndael-128', 'cbc');

            // create new encryption initialization vector if no any present
            if (!$panthera->config->getKey('cookie_encrypt_vector'))
            {
                $iv = mcrypt_create_iv($this->ivsize, MCRYPT_DEV_URANDOM);
                $panthera->config->setKey('cookie_encrypt_vector', base64_encode($iv), 'string');

            }

            // get encryption initialization vector
            $this->encryptionVector = base64_decode($panthera->config->getKey('cookie_encrypt_vector'));
        }
    }

    public function encrypt($string)
    {
        $string = utf8_encode($string);
        return base64_encode($this->encryptionVector.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->encryptionKey, 'hdr^' .$string, MCRYPT_MODE_CBC, $this->encryptionVector));
    }

    public function decrypt($string)
    {
        $ciphertext = base64_decode($string);
        $iv_dec = substr($ciphertext, 0, $this->ivsize);
        $ciphertext_dec = substr($ciphertext, $this->ivsize);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->encryptionKey, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

        if (substr($decrypted, 0, 4) == 'hdr^')
            return substr($decrypted, 4, strlen($decrypted));

        return NULL;
    }

    /**
     * Get cookie value
     *
     * @param string $cookieName Variable name
     * @return mixed
     * @author Damian Kęska
     */

    public function __get($cookieName)
    {
        if (isset($_COOKIE[$this->cookieKey. '_' .$cookieName]))
        {
            $value = $_COOKIE[$this->cookieKey. '_' .$cookieName];

            if ($this->encryption == True)
                $value = $this->decrypt($value);

            return $value;
        }

        return NULL;
    }

    /**
     * Set cookie
     *
     * @param string $cookieName Cookie name
     * @param array $value Value and expiration date eg. array("Test", time()+3600)
     * @return bool
     * @author Damian Kęska
     */

    public function __set($cookieName, $value)
    {
        if (!is_array($value))
            return False;

        if (count($value) != 2)
            return False;

        if ($this->encryption == True)
            $value[0] = $this->encrypt($value[0]);

        setCookie($this->cookieKey. '_' .$cookieName, $value[0], $value[1]);

        // caching for encryption support
        $this->cache[$cookieName] = $value;

        return True;
    }

    /**
     * Check if cookie exists
     *
     * @param string $cookieName Cookie name
     * @return bool
     * @author Damian Kęska
     */

    public function exists($cookieName)
    {
        if (isset($_COOKIE[$this->cookieKey. '_' .$cookieName]))
            return True;

        return False;
    }

    /**
     * Get all cookies as array
     *
     * @return array
     * @author Damian Kęska
     */

    public function getAll()
    {
        $cookies = array();

        foreach ($_COOKIE as $name => $value)
        {
            $exp = explode('_', $name);

            if($exp[0] == $this->cookieKey)
                $cookies[$exp[1]] = $this->__get($exp[1]);
        }

        return $cookies;
    }

    /**
     * Set cookie
     *
     * @param string $cookieName Cookie name
     * @return bool
     * @author Damian Kęska
     */

    public function remove($cookieName)
    {
        $this->__set($cookieName, array("", time()-3600));
        return True;
    }

    // aliases
    public function get($key) { return $this->__get($key); }
    public function set($key, $value, $time) { return $this->__set($key, array($value, $time)); }
}

/**
  * Tool for benchmarking, monitoring activity on website
  * In UNIX systems we would call it /var/run where are stored pids/sockets
  *
  * @package Panthera\core\session
  * @author Damian Kęska
  */

class run extends pantheraFetchDB
{
    protected $_tableName = 'run';
    protected $_idColumn = 'rid';
    protected $_constructBy = array('rid', 'array');
    protected $_unsetColumns = array('rid', 'started', 'name', 'expired');

    public function __get($var)
    {
        if ($var == 'data')
            return unserialize(parent::__get('storage'));

        return parent::__get($var);
    }
    
    public function __set($var, $value)
    {
        if ($var == 'data')
        {
            $var = 'storage';
            $value = serialize($value);
        }   
        
        return parent::__set($var, $value);
    }

    /**
     * Lockup a socket
     *
     * @param string $name Socket name
     * @param int $pid Process id or content id
     * @param mixed $data Data to be stored in database
     * @return bool|int
     * @author Damian Kęska
     */

    public static function openSocket($name, $pid, $data)
    {
        global $panthera;

        if ($name == '' or !is_numeric($pid))
            return False;

        $values = array('pid' => intval($pid), 'name' => $name, 'data' => serialize($data), 'started' => microtime(true), 'expired' => 0);
        $panthera -> db -> query('INSERT INTO `{$db_prefix}run` (`pid`, `name`, `storage`, `started`, `expired`) VALUES (:pid, :name, :data, :started, :expired);', $values);
        return $panthera -> db -> sql -> lastInsertId();
    }

    /**
     * Unlock a socket
     *
     * @param int $pid Process id or content id
     * @param string $name Socket name
     * @param int $rid Process run id
     * @return bool
     * @author Damian Kęska
     */

    public static function closeSocket($name, $pid, $rid='')
    {
        global $panthera;

        try {
            if (is_int($rid))
                $panthera -> db -> query('UPDATE `{$db_prefix}run` SET `expired` = :expired WHERE `rid` = :rid', array('expired' => microtime(true), 'rid' => $rid));
            else
                $panthera -> db -> query('UPDATE `{$db_prefix}run` SET `expired` = :expired WHERE `pid` = :pid AND `name` = :name', array('expired' => microtime(true), 'pid' => intval($pid), 'name' => $name));
                
            return True;        
        } catch (Exception $e) { return False; }    
    }

    /**
     * Remove a socket
     *
     * @param int $pid Process id or content id
     * @param string $name Socket name
     * @return string
     * @author Damian Kęska
     */

    public static function removeSocket($pid, $name)
    {
        global $panthera;

        try {
            //$panthera -> db -> query('DELETE FROM `{$db_prefix}run` WHERE `pid` = :pid AND `name` = :name', array('expired' => time(), 'pid' => intval($pid), 'name' => $name));
            return True;        
        } catch (Exception $e) { return False; }    
    }

    /**
	 * List sockets
	 *
     * @param array $by DB Columns
     * @param int $limit SQL Limit
     * @param int $limitFrom SQL limit position
     * @param string $sortBy Column to sort by
     * @param string $sortHow ASC or DESC
	 * @return string
	 * @author Damian Kęska
	 */


    public static function getSockets($by='', $limit=0, $limitFrom=0, $sortBy='rid', $sortHow='DESC')
    {
        global $panthera;
        return $panthera->db->getRows('run', $by, $limit, $limitFrom, 'run', $sortBy, $sortHow);  
    }
}

/**
  * Website navigation based on user session storage
  *
  * @package Panthera\core\session
  * @author Damian Kęska
  */

class navigation
{
    private static $history = array();
    private static $bufferMax = 8;
    
    /**
      * Add link to history
      *
      * @param string $url
      * @return bool
      * @author Damian Kęska
      */

    public static function appendHistory($url)
    {
        // going back by two records $n-2 record equals url
        //if(self::$history[count(self::$history)-2] == $url)
        //    self::$history = array_slice(self::$history, 0, (count(self::$history)-2));
    
        if(self::$history[count(self::$history)] != $url)
        {
            // remove old element to keep buffer in static size
            if (count(self::$history) >= self::$bufferMax)
                array_shift(self::$history);
                
            self::$history[] = $url;
       }
       
       return True;
    }
    
    /**
      * Return array with visited urls
      *
      * @return array 
      * @author Damian Kęska
      */
    
    public function getHistory()
    {
        return self::$history;
    }
    
    /**
      * Load history from session
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public static function loadHistoryFromSession()
    {
        global $panthera;
        self::$history = $panthera->session->get('navigation_history');
    }
    
    /**
      * Get back button link
      *
      * @param string name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function getBackButton()
    {
        return end(self::$history);
    }
    
    /**
      * Save navigation history to session
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public static function save()
    {
        global $panthera;
        $panthera->session->set('navigation_history', self::$history);
    }
    
    /**
      * Appends current page to history
      *
      * @param bool $ajaxExit Are we in ajax mode or not
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function appendCurrentPage($ajaxExit)
    {
        if ($ajaxExit == False and PANTHERA_MODE == "CGI")
        {
            $url = parse_url($_SERVER['REQUEST_URI']);
            navigation::appendHistory(substr(PANTHERA_FRONTCONTROLLER, 1, strlen(PANTHERA_FRONTCONTROLLER)). '?' .$url['query']);
            navigation::save();
        }
    }
}
