<?php
/**
  * Simple HTTP library for Panthera Framework
  *
  * @package Panthera\modules\httplib
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
class httplib
{
    public static $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36';
    protected $cookiesTempFile = '';
    
    // proxy settings
    protected $proxyAuth = null;
    protected $proxy = null;
    protected $proxyType = 'http';

    public $timeout = 16;

    /**
      * Constructor
      *
      * @return void
      * @author Damian Kęska
      */
 
    public function __construct()
    {
        global $panthera;
        
        $panthera -> add_option('page_load_ends', array($this, 'cleanup'));
    }
    
    public function __destruct()
    {
        $this->cleanup();
    }
    
    /**
      * Set proxy connection
      *
      * @param string|bool $host Set this field to IP address or False value to disable proxy
      * @param int $port
      * @param string $type Proxy type: http, socks4 or socks5
      * @param string $auth Login:password format authentication
      * @return bool
      * @author Damian Kęska
      */
    
    public function setProxy($host=false, $port=8080, $type='http', $auth=null)
    {
        if ($host === False)
        {
            $this->proxy = null;
            return True;
        }
    
        if (!filter_var($host, FILTER_VALIDATE_IP) or !is_numeric($port))
        {
            $this->proxy = False;
            return False;
        }
        
        $this->proxy = $host. ':' .$port;
        
        if ($type == 'http' or $type == 'socks4' or $type == 'socks5')
        {
            $this->proxyType = $type;
        }
        
        if ($auth)
        {
            if (strpos($auth, ':') !== False)
            {
                $this->proxyAuth = $auth;
            }
        }
        
        return True;
    }
    
    /**
      * Clean up
      *
      * @parm mixed $input
      * @return mixed
      * @author Damian Kęska
      */
    
    public function cleanup($input)
    {
        global $panthera;
    
        if ($this->cookiesTempFile)
        {
            $panthera -> logging -> output('Cleaning up file "' .$this->cookiesTempFile. '"', 'httplib');
            unlink($this->cookiesTempFile);
        }
        
        return $input;
    }
    
    /**
      * Static method for creating GET and POST requests
      *
      * @param string $url
      * @param string $method
      * @param array $options
      * @param string|array $postFields
      * @return string
      * @author Damian Kęska
      */
    
    public static function request($url, $method=null, $options=null, $postFields=null)
    {
        if ($options === null)
        {
            $options = array();
        }
        
        $options['disablecookies'] = True;
    
        $obj = new httplib;
        $result = $obj -> get($url, $method, $options, $postFields);
        unset($obj);
        return $result;
    }
    
    /**
      * Get temporary file for cookies storage
      *
      * @return string
      * @author Damian Kęska
      */
    
    public function getTempFile()
    {
        global $panthera;
    
        if (!$this->cookiesTempFile)
        {
            $name = substr(md5(rand(999999,99999999)), 0, 3). '.curl.txt';
            $this->cookiesTempFile = SITE_DIR. '/content/tmp/' .$name;
            $panthera -> logging -> output ('Creating temporary file "' .$this->cookiesTempFile. '"', 'httplib');
            
            $fp = fopen($this->cookiesTempFile, 'w');
            fwrite($fp, '');
            fclose($fp);
        }
        
        return $this->cookiesTempFile;
    }
    
    /**
      * Make a POST request
      *
      * @param string $url
      * @param string|array $postFields
      * @param array $options
      * @author Damian Kęska
      */
    
    public function post($url, $postFields, $options=null)
    {
        return $this->get($url, 'POST', $options, $postFields);
    }
    
    /**
      * Perform request
      *
      * @param string $url
      * @param string $method
      * @param array $options
      * @param string|array $postFields
      * @param bool $uploadingFile Are we uploading a file? Default - False
      * @return string
      * @author Damian Kęska
      */

    public function get($url, $method=null, $options=null, $postFields=null, $uploadingFile=False)
    {
        global $panthera;
    
        // compatibility
        if (!is_array($options))
        {
            $options = array();
        }
        
        $panthera -> logging -> output('Preparing to ' .$method. ' web url "' .$url. '"', 'httplib');
        
        // restoring session from previous connection on this object        
        $curl = curl_init();
        
        // initialize curl resource
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, False);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, True); 
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5 );
        curl_setopt($curl, CURLOPT_TIMEOUT, intval($this->timeout));
        
        // proxy suppport
        if ($this->proxy === False) // unconfigured proxy
        {
            throw new Exception('Failed to configure proxy, please check proxy settings');
            return False; // just in case
        }
        
        if ($this->proxy)
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
            
            if ($this->proxyType == 'socks4' or $this->proxyType == 'socks5')
            {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            } else {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            }
            
            if ($this->proxyAuth)
            {
                curl_setopt($curl, CURLOPT_PROXYAUTH, $this->proxyAuth);
            }
            
            $panthera -> logging -> output('Using proxy server address=' .$this->proxy. ', type=' .$this->proxyType. ', auth=len(' .strlen($this->proxyAuth). ')', 'httplib');
        }
        
        // default headers
        $headers = array(
            'Accept-Language:en-US,en;q=0.8,pl;q=0.6',
            'Cache-Control:max-age=0',
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'DNT:1'
        );

        // user defined headers
        if (isset($options['headers']))
        {
            if (is_array($options['headers']))
            {
                $headers = array_merge($headers, $options['headers']);
            }
        }

        // referer
        if (!isset($options['referer']))
        {
            $options['referer'] = parse_url($url, PHP_URL_SCHEME). '//' .parse_url($url, PHP_URL_HOST);
        }
        
        curl_setopt($curl, CURLOPT_REFERER, $options['referer']);
        
        // useragent
        $userAgent = self::$userAgent;
        
        if (isset($options['userAgent']))
        {
            $userAgent = $options['userAgent'];
        }
        
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        
        if ($method == 'POST')
        {
            if (is_array($postFields) and !$uploadingFile)
            {
                $postFields = http_build_query($postFields);
            }
        
            curl_setopt ($curl, CURLOPT_POST, 1);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $postFields);
        }
        
        if (!@$options['disablecookies'])
        {
            // cookies
            curl_setopt($curl, CURLOPT_COOKIESESSION, false); // force keep old cookies
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->getTempFile());
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->getTempFile());
        }
        
        $data = curl_exec($curl);
        
        if ($data === False)
        {
            throw new Exception('Failed to make HTTP request, details: ' .curl_error($curl));
        }
        
        $panthera -> logging -> output('Request finished', 'httplib');
        curl_close($curl);
        
        return $data;
    }
}
