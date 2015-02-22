<?php
/**
 * Session management, navigation, "run" table
 *
 * @package Panthera\core\system\session
 * @author Damian Kęska
 * @license LGPLv3
 */

if (!defined('IN_PANTHERA'))
	exit;

/**
 * User session management class
 *
 * @package Panthera\core\system\session
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
        $this->_cookies = new pantheraCookie($this->sessionKey);

        // Security: Check user-agent
        if ($panthera->config->getKey('session_useragent', True, 'bool'))
        {
            if (!$this->browserCheck())
            {
                $check = $panthera->get_filters('session_security_remove', 'BROWSER_CHECK_FAILED');

                if ($check == True and is_bool($check))
                    return True;

                $panthera -> logging -> output ('Useragent check failed', 'pantheraSession');
                $this->removeSession();
            }
        }

        ini_set("session.gc_divisor", "1");
        ini_set("session.gc_probability", "1");
        ini_set('session.gc_maxlifetime', (int)$panthera->config->getKey('session_lifetime', '3600', 'int'));
        ini_set('session.cookie_lifetime', (int)$panthera->config->getKey('session_lifetime', '3600', 'int'));
        session_name('PFW-' .$this->sessionKey);
        session_start();

        if (is_numeric($this->sessionKey))
        {
            throw new UnexpectedValueException('Invalid "session_key" configuration variable value, cannot be numeric - expecting [A-Z], [a-z]', 'CONFIG.SESSION_KEY.INVALID_VALUE');
        }

        // Security: Check session life-time (default is 1 hour = 3600 seconds)
        if ((int)$panthera->config->getKey('session_lifetime', '3600', 'int') > 0 and $this->exists('time'))
        {
            if (!$this->lifetimeCheck())
            {
                $check = $panthera->get_filters('session_security_remove', 'LIFETIME_EXCEEDED');

                if ($check == True and is_bool($check))
                    return True;

                $panthera -> logging -> output ('Session expired', 'pantheraSession');
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

        // Browser detection check
        if (!$this->get('clientInfo'))
            $this -> set('clientInfo', (array)$this->detectBrowser());

        // extending session life-time on some server configurations
        if (!$this -> exists('__lifetime'))
        {
            setcookie(session_name(), session_id(), (time()+(int)$panthera->config->getKey('session_lifetime', '3600', 'int'))); 
            $this -> set('__lifetime', true);
        }

        $this->panthera->add_option('page_load_ends', array($this, 'close'));
    }

    /**
     * Detect a browser and os types and versions
     *
     * @return array
     * @author Damian Kęska
     */

    public function detectBrowser()
    {
        // require Mobile Detect library
        if (!class_exists('Mobile_Detect') and is_file(PANTHERA_DIR. '/share/mobiledetectlib/Mobile_Detect.php'))
            require PANTHERA_DIR. '/share/mobiledetectlib/Mobile_Detect.php';
        
        if (!class_exists('Mobile_Detect'))
        {
            $this -> panthera -> logging -> output('Warning: Mobile Detect library not installed', 'pantheraSession');
            return array();
        }
        
        $info = array(
            'deviceType' => 'desktop',
            'browser' => 'Unknown',
            'os' => 'Unknown',
            'browserVersion' => '',
            'engineVersion' => '',
        );

        $detect = new Mobile_Detect;
        // device type detection
        if ($detect->isMobile()) { $info['deviceType'] = 'mobile'; }
        elseif ($detect -> isTablet()) { $info['deviceType'] = 'tablet'; }
        else {
            // desktop os and browser type & version
            $ua = strtolower($detect->getUserAgent());

            if (strpos($ua, 'linux') !== False) { $info['os'] = 'Linux'; }
            elseif (strpos($ua, 'macintosh') !== False) { $info['os'] = 'OS X'; }
            elseif (strpos($ua, 'windows') !== False) { $info['os'] = 'Windows';}

            if (strpos($ua, 'chrome') !== False) { $info['browser'] = 'Chrome'; $info['engineVersion'] = $detect->version('Webkit'); $info['browserVersion'] = $detect->version('Chrome'); }
            elseif (strpos($ua, 'msie') !== False) { $info['browser'] = 'IE'; $info['engineVersion'] = $detect->version('MSIE'); $info['browserVersion'] = $detect->version('Trident'); }
            elseif (strpos($ua, 'opera') !== False) { $info['browser'] = 'Opera'; $info['engineVersion'] = $detect->version('Webkit'); $info['browserVersion'] = $detect->version('Opera'); }
            elseif (strpos($ua, 'firefox') !== False) { $info['browser'] = 'Firefox'; $info['engineVersion'] = $detect->version('Gecko'); $info['browserVersion'] = $detect->version('Firefox'); }
            elseif (strpos($ua, 'safari') !== False) { $info['browser'] = 'Safari'; $info['engineVersion'] = $info['browserVersion'] = $detect->version('Webkit'); }

        }

        // detect browser
        if ($detect->isChrome()) { $info['browser'] = 'Chrome'; $info['browserVersion'] = $detect->version('Chrome'); $info['engineVersion'] = $detect->version('Webkit'); }
        elseif ($detect->isOpera()) { $info['browser'] = 'Opera'; $info['engineVersion'] = $detect->version('Webkit'); $info['browserVersion'] = $detect->version('Opera'); }
        elseif ($detect->isFirefox()) { $info['browser'] = 'Firefox'; $info['engineVersion'] = $detect->version('Gecko'); $info['browserVersion'] = $detect->version('Firefox'); }
        elseif ($detect->isDolfin()) { $info['browser'] = 'Dolphin'; $info['engineVersion'] = $detect->version('Webkit'); $info['browserVersion'] = $detect->version('Dolfin'); }
        elseif ($detect->isSafari()) { $info['browser'] = 'Safari'; $info['browserVersion'] = $detect->version('Safari'); $info['engineVersion'] = $detect->version('Webkit');  }
        elseif ($detect->isIE()) { $info['browser'] = 'IE'; }
        elseif ($detect->isGenericBrowser()) { $info['browser'] = 'Generic'; }

        // detect mobile os
        if ($detect->isAndroidOS()) { $info['os'] = 'Android'; }
        elseif ($detect->isiOS()) { $info['os'] = 'iOS'; }
        elseif ($detect->isWindowsMobileOS()) { $info['os'] = 'Windows Mobile'; }
        elseif ($detect->isWindowsPhoneOS()) { $info['os'] = 'Windows Phone'; }
        elseif ($detect->isSymbianOS()) { $info['os'] = 'Symbian'; }
        elseif ($detect->isMeeGoOS()) { $info['os'] = 'Meego'; }
        elseif ($detect->isTizen()) { $info['os'] = 'Tizen'; }
        elseif ($detect->isMaemoOS()) { $info['os'] = 'Maemo'; }
        elseif ($detect->isbadaOS()) { $info['os'] = 'Bada OS'; }
        elseif ($detect->isBlackBerryOS()) { $info['os'] = 'Blackberry OS'; }

        return $info;
    }

    /**
     * Remove all user session variables
     *
     * @param bool $forceDestroy Force destroy session (uses session_destroy() and can affect other web applications on same domain)
     * @return bool
     * @author Damian Kęska
     */
    public function removeSession($forceDestroy=false)
    {
        if ($forceDestroy)
            session_destroy();

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
        $max = (int)$this->panthera->config->getKey('session_lifetime', 86400, 'int');

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
     * @param string $key Key name
     * @return bool
     * @author Damian Kęska
     */
    public function exists($key)
    {
        if (@array_key_exists($key, $_SESSION[$this->sessionKey]))
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

        if (isset($_SESSION[$this->sessionKey][$key]))
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

        if (!isset($_SESSION[$this->sessionKey]))
        {
            $_SESSION[$this->sessionKey] = array();
        }
        
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

    /**
     * Write session data and close
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function close()
    {
        return session_write_close();
    }

    // aliases
    public function get($key) { return $this->__get($key); }
    public function set($key, $value) { return $this->__set($key, $value); }
}

/**
 * Cookie support extension for session management
 *
 * @package Panthera\core\system\session
 * @author Damian Kęska
 */
class pantheraCookie
{
    protected $cookieKey, $panthera, $encryption = False, $ivsize = 16, $encryptionVector = '', $encryptionKey = '';
    
    /**
     * Panthera Cookies management constructor
     * 
     * @param string $cookieKey Cookie prefix
     * @author Damian Kęska
     */

    public function __construct($cookieKey)
    {
        $this->panthera = pantheraCore::getInstance();
        $this->cookieKey = substr(md5($cookieKey), 0, 6);

        // Security: Encrypt cookies with AES-128 bit in CBC mode if possible
        if ($this -> panthera -> config -> getKey ('cookie_encrypt', 0, 'bool') and function_exists('mcrypt_encrypt'))
        {
            $this->encryptionKey = base64_decode($this->panthera->config->getKey('cookie_encrypt_key'));

            // create new encryption key
            if (!$this->encryptionKey)
            {
                $this->encryptionKey = hash("SHA256", rand(999999, 9999999999), True);
                $this->panthera->config->setKey('cookie_encrypt_key', base64_encode($this->encryptionKey), "string");
            }

            $this->encryption = True;

            // length of initialization vector
            $this->ivsize = mcrypt_get_iv_size('rijndael-128', 'cbc');

            // create new encryption initialization vector if no any present
            if (!$this->panthera->config->getKey('cookie_encrypt_vector'))
            {
                $iv = mcrypt_create_iv($this->ivsize, MCRYPT_DEV_URANDOM);
                $this->panthera->config->setKey('cookie_encrypt_vector', base64_encode($iv), 'string');

            }

            // get encryption initialization vector
            $this->encryptionVector = base64_decode($this->panthera->config->getKey('cookie_encrypt_vector'));
        }
    }

    /**
     * Encrypt a string using RIJNDAEL 128 bit and encode to base64
     * 
     * @param string $string Input string to encode
     * @author Damian Kęska
     * @return string
     */

    public function encrypt($string)
    {
        $string = utf8_encode($string);
        return base64_encode($this->encryptionVector.mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->encryptionKey, 'hdr^' .$string, MCRYPT_MODE_CBC, $this->encryptionVector));
    }
    
    /**
     * Decode from base64 and decrypt from RIJNDAEL 128
     * 
     * @param string $string Input encoded string
     * @author Damian Kęska
     * @return string|null Returns null on decoding error (when header not found)
     */

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
        if (!is_array($value) or count($value) < 2)
            return False;

        if ($this->encryption == True)
            $value[0] = $this->encrypt($value[0]);
        
        if (!isset($value[2]) or !$value[2])
            $name = $this->cookieKey. '_' .$cookieName;
        else
            $name = $cookieName;

        setCookie($name, $value[0], $value[1]);

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

    /**
     * Alias to __get()
     * 
     * Example:
     * <code>
     * // see $this -> set() method example
     * var_dump($panthera -> session -> get('name'));
     * </code>
     * 
     * @see this->__get()
     * @author Damian Kęska
     */
    
    public function get($key) { return $this->__get($key); }
    
    /**
     * Alias to __set()
     * 
     * Example:
     * <code>
     * $panthera -> session -> set('name', 'John', time()+120); // set cookie "name" with value "John" for 120 seconds
     * </code>
     * 
     * @see this->__set()
     * @param string $key Cookie name
     * @param string $value Value
     * @param int $time Expiration date in UNIX timestamp eg. time()+60 = 60 seconds from now
     * @param bool $globalCookie (Optional) Don't use cookie prefix, other Panthera-based websites on this domain should be able to use that cookie
     * @author Damian Kęska
     * @return bool
     */
    
    public function set($key, $value, $time, $globalCookie=False) { return $this->__set($key, array($value, $time, $globalCookie)); }
}

/**
 * Tool for benchmarking, monitoring activity on website
 * In UNIX systems we would call it /var/run where are stored pids/sockets
 *
 * @package Panthera\core\system\session
 * @author Damian Kęska
 */

class run extends pantheraFetchDB
{
    protected $_tableName = 'run';
    protected $_idColumn = 'rid';
    protected $_constructBy = array('rid', 'array');
    protected $_unsetColumns = array('rid', 'started', 'name', 'expired');
    protected $cache = 0;

    public function __get($var)
    {
        if ($var == 'data' or $var == 'storage')
            return unserialize(parent::__get('storage'));

        return parent::__get($var);
    }

    public function __set($var, $value)
    {
        if ($var == 'data' or $var == 'storage')
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
        $panthera -> db -> query('INSERT INTO `{$db_prefix}run` (`rid`, `pid`, `name`, `storage`, `started`, `expired`) VALUES (NULL, :pid, :name, :data, :started, :expired);', $values);
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

    public static function closeSocket($name, $pid='', $rid='')
    {
        global $panthera;

        try {
            if (is_int($rid))
                $panthera -> db -> query('UPDATE `{$db_prefix}run` SET `expired` = :expired WHERE `rid` = :rid', array('expired' => microtime(true), 'rid' => $rid));
            else
                $panthera -> db -> query('UPDATE `{$db_prefix}run` SET `expired` = :expired WHERE `pid` = :pid AND `name` = :name', array('expired' => microtime(true), 'pid' => intval($pid), 'name' => $name));

            return True;
        } catch (Exception $e) {
            $panthera -> logging -> output ('Warning: Cannot close socket pid=' .$pid. ', rid=' .$rid. ', exception=' .$e->getMessage(), 'run');
            return False;
        }
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
            $panthera -> db -> query('DELETE FROM `{$db_prefix}run` WHERE `pid` = :pid AND `name` = :name', array('pid' => intval($pid), 'name' => $name));
            return True;
        } catch (Exception $e) {
            $panthera -> logging -> output ('Warning: Cannot remove socket pid=' .$pid. ', name=' .$name. ', exception=' .$e->getMessage(), 'run');
            return False;
        }
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
 * @package Panthera\core\system\session
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

        if ($_GET['display'] == 'navigation_history' or substr($_GET['display'], 0, 6) == '_popup')
            return False;

        if (array_key_exists('__navigationBack', $_GET))
        {
            array_pop(self::$history);
            return False;
        }

        if (!is_array(self::$history))
        {
            self::$history = array();
        }

        if(end(self::$history) != $url)
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
        if (!is_array(self::$history))
            return '';

        $btn = end(self::$history);

        if (strpos($btn, '__navigationBack') === False)
        {
            if (strpos($btn, '?') === False)
                $btn .= '?__navigationBack=True';
            else
                $btn .= '&__navigationBack=True';
        }

        return $btn;
    }

    /**
     * Save navigation history to session
     *
     * @return void
     * @author Damian Kęska
     */

    public static function save()
    {
        $panthera = pantheraCore::getInstance();
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
            $url = preg_replace('/\&?\_\=([0-9]+)/', '', $url);

            navigation::appendHistory(substr(PANTHERA_FRONTCONTROLLER, 1, strlen(PANTHERA_FRONTCONTROLLER)). '?' .$url['query']);
            navigation::save();
        }
    }
}