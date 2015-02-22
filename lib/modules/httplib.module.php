<?php
/**
 * Simple HTTP library for Panthera Framework
 *
 * @package Panthera\core\modules\httplib
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Simple HTTP library for Panthera Framework
 *
 * Example:
 * <code>
 * $http = new httplib;
 * $http -> get('http://google.com');
 * $http -> setProxy('192.168.1.242', 8080);
 * $http -> get('http://google.com'); // google.com through proxy
 * $http -> outgoingAddress = 'eth1'; // bind to eth1 interface
 * $http -> get('http://google.com'); // connect to google.com through eth1 interface
 * $http -> post('http://example.org', array('test' => 1));
 * $http -> close();
 * $http::request('http://example.org', 'GET'); // make a static request
 *
 * @package Panthera\core\modules\httplib
 * @author Damian Kęska
 */

class httplib
{
    public $userAgentOptions = ''; // {random_chromium}
    public static $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1667.0 Safari/537.36';
    protected $cookiesTempFile = '';
    public $timeout = 16;
    public $instanceID = null;
    public $enableCookies = True;
    public $curlHeader = False;

    // proxy settings
    protected $proxyAuth = null;
    protected $proxy = null;
    protected $proxyType = 'http';

    /**
     * Put here a interface name eg. eth0, eth1 or IPv6 address to be used (if multiple available)
     */

    protected $addressesTmp = array();
    public $outgoingAddress = null; // eg. eth0 or eth0:1. For big IP count use eg. eth1, array(0 => '1.1.1.1', 1 => '2.2.2.2')
    public $outgoingIPCount = null; // eg. 10000 IP addresses to use, or range 100-150
    public $outgoingIPSelection = 'in_sequence'; // in_sequence or random
    public $outgoingIPCursor = null;

    /**
     * Constructor
     *
     * @return void
     * @author Damian Kęska
     */

    public function __construct()
    {
        $panthera = pantheraCore::getInstance();
		$this -> instanceID = generateRandomString(64);
        $panthera -> addOption('page_load_ends', array($this, 'close'));
    }

    /**
     * Select address from available range
     *
     * Let's say that we have 5000 IPv6 addresses that we want to use. We are creating a httplib session, setting interface ($outgoingAddress)
     * setting IP count ($outgoingIPCount) to 5000 and random choosing ($outgoingIPSelection = 'random'). Every single request httplib will change IP address.
     *
     * Example:
     * <code>
     * $http = new httplib;
     * $http -> outgoingAddress = 'eth0';
     * $http -> outgoingIPCount = 1000;
     * $http -> outgoingIPSelection = 'random';
     * $http -> get('ipv6.google.com'); // make a request from random ip eg. eth0:5
     * $http -> get('ipv6.google.com'); // make a request from random ip eg. eth0:400
     *
     * // change strategy
     * $http -> outgoingIPSelection = 'in_sequence';
     * $http -> outgoingIPCursor = 0; // reset the cursor
     * $http -> get('ipv6.google.com'); // eth0:1
     * $http -> get('ipv6.google.com'); // eth0:2
     * $http -> get('ipv6.google.com'); // eth0:3
     * $http -> close();
     * </code>
     *
     * @return null
     */

    public function selectAddress()
    {
        if (is_array($this -> outgoingAddress))
        {
            $this -> addressesTmp = $this -> outgoingAddress;
            $this -> outgoingIPCount = count($this -> addressesTmp);
        }

        if ($this -> outgoingIPCount)
        {
            if (is_string($this -> outgoingIPCount))
            {
                // range eg. 100-150
                if (strpos($this -> outgoingIPCount, '-') !== False)
                    $range = explode('-', $this -> outgoingIPCount);
                /*elseif (strpos($this -> outgoingIPCount, ',') !== False) {
                    $range = explode(',', str_replace(' ', '', $this -> outgoingIPCount));
                }*/
            } else {
                $range = array(0, $this -> outgoingIPCount);
            }

            if (count($range) == 2)
            {
                // random address choosing
                if ($this -> outgoingIPSelection == 'random')
                    $selected = rand($range[0], ($range[1]-1));
                else {

                    // in_sequence address choosing (next, next, next, end, begin, next, next, ...)
                    if (!is_int($this -> outgoingIPCursor) or $this -> outgoingIPCursor >= ($range[1]-1))
                        $this -> outgoingIPCursor = $range[0];

                    $selected = $this -> outgoingIPCursor++;
                }

                if ($this -> addressesTmp)
                {
                    if (!isset($this -> addressesTmp[$selected]))
                        $selected = key($this -> addressesTmp);

                    $this -> outgoingAddress = $this -> addressesTmp[$selected];
                } else {
                    $exp = explode(':', $this -> outgoingAddress);
                    $this -> outgoingAddress = $exp[0]. ':' .$selected;
                }

                $panthera = pantheraCore::getInstance();
                $panthera -> logging -> output('Selected "' .$this -> outgoingAddress. '" (#' .$selected. ') for interface', 'httplib');
            }
        }
    }

    /**
     * Generate an useragent if needed
     * 
     * @return null
     */

    public function selectUserAgent()
    {
        if ($this -> userAgentOptions == '{random_chromium}')
        {
            $webkit = rand(530, 580). '.' .rand(10, 40);
            $chrome = rand(35, 45). '.' .rand(1,5). '.' .rand(500,1500). '.' .rand(1, 5); // 35.0.1667.0
            
            static::$userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/' .$webkit. ' (KHTML, like Gecko) Chrome/' .$chrome. ' Safari/' .$webkit;
            
            $panthera = pantheraCore::getInstance();
            $panthera -> logging -> output('Generated Chromium UA: ' .static::$userAgent, 'GooglePR');
        }
    }

    /**
     * Destruct object
     *
     * @return null
     */

    public function __destruct()
    {
        $this -> close();
    }

    /**
     * Close connection and clean up
     *
     * @return null
     */

    public function close()
    {
        $this -> cleanup();

        $panthera = pantheraCore::getInstance();
        $panthera -> executeRef('httplib.close', $this, $this -> instanceID);
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

    public function cleanup($input='')
    {
        $panthera = pantheraCore::getInstance();

        if ($this->cookiesTempFile)
        {
			if ($panthera)
				$panthera -> logging -> output('Cleaning up file "' .$this->cookiesTempFile. '" for instance id=' .$this -> instanceID, 'httplib');

			if (is_file($this->cookiesTempFile))
				@unlink($this->cookiesTempFile);
        } else {
        	$panthera -> logging -> output('Nothing to clean for instance id=' .$this -> instanceID, 'httplib');
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
        $panthera = pantheraCore::getInstance();

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
        $panthera = panthera::getInstance();
        $this -> selectAddress();
        $this -> selectUserAgent();
        $panthera -> get_options_ref('httplib.get.prepare', $this, $this -> instanceID);

        // compatibility
        if (!is_array($options))
            $options = array();

        if (!$method)
            $method = 'GET';

        if ($panthera -> logging -> debug)
        {
            $panthera -> logging -> output('Preparing to ' .$method. ' web url "' .$url. '"', 'httplib');
            
            if ($method == 'POST')
                $panthera -> logging -> output('POST data: ' .json_encode($postFields), 'httplib');
        }
        
        // restoring session from previous connection on this object
        $curl = curl_init();

        // initialize curl resource
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, False);
        curl_setopt($curl, CURLOPT_HEADER, $this -> curlHeader);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, True);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5 );
        curl_setopt($curl, CURLOPT_TIMEOUT, intval($this->timeout));

        // set outgoing address or interface
        if ($this -> outgoingAddress)
            curl_setopt($curl, CURLOPT_INTERFACE, $this -> outgoingAddress);

        // proxy suppport
        if ($this->proxy === False) // unconfigured proxy
        {
            throw new Exception('Failed to configure proxy, please check proxy settings');
            return False; // just in case
        }

        if ($this->proxy)
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy);

            if ($this->proxyType == 'socks5')
            {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            } elseif ($this -> proxyType == 'socks4') {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
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
        if (isset($options['headers']) and is_array($options['headers']))
            $headers = array_merge($headers, $options['headers']);

        // referer
        if (!isset($options['referer']))
            $options['referer'] = parse_url($url, PHP_URL_SCHEME). '//' .parse_url($url, PHP_URL_HOST);

        curl_setopt($curl, CURLOPT_REFERER, $options['referer']);

        // useragent
        $userAgent = self::$userAgent;

        if (isset($options['userAgent']))
            $userAgent = $options['userAgent'];

        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);

        if ($method == 'POST')
        {
            if (is_array($postFields) and !$uploadingFile)
                $postFields = http_build_query($postFields);

            curl_setopt ($curl, CURLOPT_POST, 1);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, $postFields);
        }

        if ($this -> enableCookies and (!isset($options['disablecookies']) or !$options['disableCookies']))
        {
            // cookies
            curl_setopt($curl, CURLOPT_COOKIESESSION, false); // force keep old cookies
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->getTempFile());
            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->getTempFile());
        }
        
        if (is_array($headers) and $headers)
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        

        $data = curl_exec($curl);

        // plugins support
        $panthera -> get_options_ref('httplib.get', $this, $this -> instanceID);

        if ($data === False)
            throw new Exception('Failed to make HTTP request, details: ' .curl_error($curl). ', url: ' .$url);

        $panthera -> logging -> output('Request finished', 'httplib');
        curl_close($curl);

        return $data;
    }
}
