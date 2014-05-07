<?php
/**
  * Panthera Framework main file
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

/**
 * Exception handler
 *
 * @param object $exception
 * @return void
 * @Package Panthera\core
 * @author Damian Kęska
 */

function pantheraExceptionHandler($exception)
{
    $panthera = pantheraCore::getInstance();
    $panthera->logging->output('Unhandled exception, starts;', 'pantheraExceptionHandler');
    $panthera->logging->output($exception->getMessage(), 'pantheraExceptionHandler');
    $panthera->logging->output($exception->getFile(). ' on line ' .$exception->getLine(), 'pantheraExceptionHandler');

    $trace = $exception->getTrace();

    foreach ($trace as $key => $stackPoint)
    {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args_content'] = null;
        $trace[$key]['args'] = null;
        $trace[$key]['class'] = null;
        
        if (isset($trace[$key]['args']))
        {
            $trace[$key]['args_content'] = json_encode($trace[$key]['args']);
        
            if (is_array($trace[$key]['args']))
                $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
        }
        
        if (isset($stackPoint['class']))
            $trace[$key]['class'] = $stackPoint['class'];
    }

    $stackTrace = array();
    foreach ($trace as $key => $stackPoint) {
        $args = '';
        
        if (is_array($stackPoint['args']))
        {
            $args = implode(', ', $stackPoint['args']);
        }
        
        $stackTrace[] = array('key' => $key, 'file' => $stackPoint['file'], 'line' => $stackPoint['line'], 'function' => $stackPoint['function'], 'args' => $args, 'args_json' => $stackPoint['args_content'], 'class' => $stackPoint['class']);

        $function = $stackPoint['function'];

        if ($stackPoint['class'] != '')
        {
            $function = $stackPoint['class']. ' -> ' .$stackPoint['function']. '(' .$args. ')';
        }
        
        $panthera->logging->output($key. ' => ' .$function. ' in ' .$stackPoint['file']. ' on line ' .$stackPoint['line'], 'pantheraExceptionHandler');
    }
    
    if ($panthera -> config)
    {
        if ($panthera -> config -> getKey('dumpErrorsToFiles'))
        {
            if (!is_dir(SITE_DIR. '/content/tmp/dumps'))
                mkdir(SITE_DIR. '/content/tmp/dumps');
            
            $dumpName = 'exception-' .hash('md4', $exception->getFile().$exception->getLine()). '.phps';
            $fp = fopen(SITE_DIR. '/content/tmp/dumps/' .$dumpName, 'w');
                
            $array = array(
                'included_files' => get_included_files(),
                'phpversion' => phpversion(),
                'uname' => @php_uname(),
                'constants' => get_defined_constants(TRUE),
                'extensions' => get_loaded_extensions(),
                'panthera_version' => PANTHERA_VERSION,
                'log' => $panthera -> logging -> getOutput(),
                'get' => $_GET,
                'post' => $_POST,
                'server' => $_SERVER,
                'stack' => $stackTrace,
                'exception' => array(
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ),
            );
            
            fwrite($fp, serialize($array));
            
            fclose($fp);
        }
    }

    if ($panthera -> logging -> debug)
    {
        $exceptionPage = getContentDir('templates/exception_debug.php');
        
        if ($exceptionPage)
        {
            include_once $exceptionPage;
        }

        $panthera->logging->saveLog();
        exit;

    } else {
        $exceptionPage = getContentDir('templates/exception.php');
        
        if ($exceptionPage)
        {
            include_once $exceptionPage;
        }

        exit;
    }
}

/**
  * Error handler
  *
  * @param int $errno
  * @param string $errstr
  * @param string $errfile
  * @param string $errline
  * @return mixed
  * @Package Panthera\core
  * @author Damian Kęska
  */

function pantheraErrorHandler($errno=0, $errstr='unknown', $errfile='unknown', $errline='unknown')
{
    $panthera = pantheraCore::getInstance();
    
    if (error_get_last() or $errno)
    {
        $details = error_get_last();
        
        // skip those error codes
        $skipErrorCodes = array(
            E_DEPRECATED,
            E_NOTICE,
            0,
            E_WARNING,
            E_USER_NOTICE,
            E_STRICT,
            E_DEPRECATED,
            E_COMPILE_WARNING,
            E_CORE_WARNING,
            E_WARNING,
        );
        
        // we will show warning messages in debugging mode
        if ($panthera->logging->debug and $panthera->logging->strict)
        {
            $skipErrorCodes = array(
                E_DEPRECATED,
                E_NOTICE,
                0,
                E_USER_NOTICE,
            );
        }
        
        if ($panthera->logging)
        {
            $panthera -> logging -> output($errstr. ' in ' .$errfile. ' on line ' .$errline, 'PHP');
        }
        
        if (in_array($errno, $skipErrorCodes))
        {
            return True;
        }
        
        if ($panthera -> config)
        {
            if ($panthera -> config -> getKey('dumpErrorsToFiles'))
            {
                if (!is_dir(SITE_DIR. '/content/tmp/dumps'))
                    mkdir(SITE_DIR. '/content/tmp/dumps');
                
                $dumpName = 'error-' .hash('md4', $errfile.$errline). '.phps';
                $fp = fopen(SITE_DIR. '/content/tmp/dumps/' .$dumpName, 'w');
                    
                fwrite($fp, serialize(array(
                    'included_files' => get_included_files(),
                    'phpversion' => phpversion(),
                    'uname' => @php_uname(),
                    'constants' => get_defined_constants(TRUE),
                    'extensions' => get_loaded_extensions(),
                    'panthera_version' => PANTHERA_VERSION,
                    'log' => $panthera -> logging -> getOutput(),
                    'get' => $_GET,
                    'post' => $_POST,
                    'server' => $_SERVER,
                    'stack' => $stack,
                    'error' => array(
                        'file' => $errfile,
                        'line' => $errline,
                        'message' => $errstr,
                        'code' => $errno,
                    )
                )));
                    
                fclose($fp);
            }
        }
        
        if(strpos('PHP Startup', $errstr) !== False)
            return True;

        if ($panthera -> logging -> debug)
        {
            $panthera->logging->output('pantheraErrorHandler::Unexcepted error ' .json_encode($details));
            $panthera->logging->saveLog();

            $stack = debug_backtrace( false );
            
            $errorPage = getContentDir('templates/error_debug.php');

            if ($errorPage)
            {
                include_once $errorPage;
            }

            exit;
        } else {
            $errorPage = getContentDir('templates/error.php');
            
            if ($errorPage)
            {
                include_once $errorPage;
            }
            
            exit;
        }
    }
}
/**
  * This class is handling all messages, saving them to file, displaying
  * its used to debug whole application based on Panthera Framework
  *
  * @Package Panthera\core
  * @author Damian Kęska
  */

class pantheraLogging
{
    public $debug = False; 
    public $tofile = True;
    public $toVarCache=True; 
    public $printOutput = False;
    public $filterMode = '';
    public $filter = array();
    private $_output = array();
    private $panthera;
    protected $timer = 0;
	public $isRealMemUsage = False;
    public $strict = False;

    /**
      * Constructor
      * Its just adding an event to hook session_save to allow saving data on application exit
      *
      * @param object $panthera
      * @return void
      * @author Damian Kęska
      */

    public function __construct($panthera)
    {
        $this->panthera = $panthera;
        $this->panthera -> add_option('session_save', array($this, 'saveLog'));

        if (defined('PANTHERA_FORCE_DEBUGGING'))
            $this->debug = True;
    }

    /**
      * Add a line to messages log
      *
      * @param string $msg Message
      * @param string $type Identifier for group of messages
      * @param bool $dontResetTimer Don't reset timer to keep real execution time
      * @hook logging.output
      * @return bool
      * @author Damian Kęska
      */

    public function output($msg, $type='', $dontResetTimer=False)
    {
        if(!$this->debug and !$this->printOutput)
            return False;

        // filter
        if ($this->filterMode == 'blacklist')
        {
            if (isset($this->filter[$type]))
                return False;
        } else if ($this->filterMode == 'whitelist') {
            if (!isset($this->filter[$type]))
                return False;
        }

        $time = microtime();

        if ($this->printOutput)
            print($msg. "\n");

        // plugins support eg. firebug
        $this->panthera -> get_options('logging.output', $msg);

        $this->_output[] = array($msg, $type, $time, $this->timer, memory_get_usage($this->isRealMemUsage));
        
        if ($dontResetTimer == False)
            $this->timer = 0;

        return True;
    }

    /**
      * Clear output string
      *
      * @return void
      * @author Damian Kęska
      */

    public function clear()
    {
        $this->_output = array();
    }
    
    /**
      * Start timer to count execution time of fragment of code
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function startTimer()
    {
        $this->timer = microtime_float();
    }

    /**
      * Get complete output
      *
      * @return string
      * @author Damian Kęska
      */

    public function getOutput($array=False)
    {
        $this -> panthera -> importModule('filesystem');
        
        if ($array === True)
            return $this->_output;

        $this->output('Generating output', 'pantheraLogging');

        if (PANTHERA_MODE == 'CLI')
        {
            $defaults = "Client addr(".@$_SERVER['SSH_CLIENT'].") => CLI ".@$_SERVER['SCRIPT_NAME']."\n";
        } else
            $defaults = "Client addr(".@$_SERVER['REMOTE_ADDR'].") => ".@$_SERVER['REQUEST_METHOD']. " ".@$_SERVER['REQUEST_URI']."\n";

        $msg = '';
        $lastTime = 0;

        // convert output to string
        foreach ($this->_output as $line)
        {
            $time = microtime_float($line[2])-$_SERVER['REQUEST_TIME_FLOAT'];
            $real = '';
            
            if ($line[3] > 0)
            {
                $executionTime = (microtime_float($line[2])-$line[3])*1000;
                $real = ' real';
            } else {
                $executionTime = ($time-$lastTime)*1000;
            }
            
            $msg .= "[".substr($time, 0, 9).", ".substr($executionTime, 0, 9)."ms".$real."] [".filesystem::bytesToSize($line[4])."] [".$line[1]."] ".$line[0]. "\n";
            $lastTime = $time;
        }

        $msg .= "[".substr(microtime_float()-$_SERVER['REQUEST_TIME_FLOAT'], 0, 9).", ".substr((microtime_float()-$_SERVER['REQUEST_TIME_FLOAT']-$lastTime)*1000, 0, 9)."ms] [".filesystem::bytesToSize(memory_get_usage($this->isRealMemUsage))."]  [pantheraLogging] Done\n";

        return $defaults.$msg;
    }

    /**
      * Save debug to file
      *
      * @return void
      * @author Damian Kęska
      */

    public function saveLog()
    {
        $output = $this->getOutput();
    
        if ($this->tofile)
        {
            $fp = @fopen(SITE_DIR. '/content/tmp/debug.log', 'w');
            @fwrite($fp, $output);
            @fclose($fp);
        }
        
        if ($this->toVarCache)
        {
            if ($this->panthera->varCache)
            {
                $this->panthera->varCache->set('debug.log', base64_encode($output), 864000);
            }
        }
    }
    
    /**
      * Read log from cache or from file
      *
      * @return string|bool
      * @author Damian Kęska
      */
    
    public function readSavedLog()
    {
        if ($this->toVarCache and $this->panthera->varCache)
        {
            if ($this->panthera->varCache->exists('debug.log'))
            {
                return base64_decode($this->panthera->varCache->get('debug.log'));
            }
        }
        
        if ($this->tofile and is_file(SITE_DIR. '/content/tmp/debug.log'))
        {
            return @file_get_contents(SITE_DIR. '/content/tmp/debug.log');
        }
        
        return False;
    }
}

/**
  * Configuration management with database storage support
  *
  * @Package Panthera\core
  * @author Damian Kęska
  */

class pantheraConfig
{
    protected $config;
    protected $panthera;
    protected $overlay = array();
    protected $overlay_modified = array();
    protected $sections = array();
    protected $overlays = 0; // count of loaded overlays
    protected $modifiedSections = array(); // list of modified overlays

    public function __construct($panthera, $config)
    {
         // TODO: Implement an error handler when there is no $config defined
        $this->config = $config;
        $this->panthera = $panthera;
        #$this->_loadOverlay();

        // add option to save configuration on exit
        $panthera -> add_option('session_save', array($this, 'save'));
    }

    /**
      * Get configuration key and create it if does not exists if provided default values and type
      *
      * @param string $key name
      * @param mixed $default value
      * @param string $type Data type
      * @return mixed
      * @author Damian Kęska
      */

    public function getKey($key, $default='__none', $type='__none', $section=null)
    {
        // load configuration section first
        if ($section !== null)
            $this->loadSection($section);
            
        if(array_key_exists($key, $this->config))
            return $this->config[$key];
            
        if(array_key_exists($key, $this->overlay))
            return $this->panthera->types->parse($this->overlay[$key][1], $this->overlay[$key][0]);

        // create new key with default value
        if (!array_key_exists($key, $this->overlay) and !array_key_exists($key, $this->config) and $default != '__none')
            $this->setKey($key, $default, $type, $section);

        if($default == '__none')
            return Null;

        return $default;
    }

    /**
      * Load configuration section
      *
      * @param string $section name
      * @return bool
      * @author Damian Kęska
      */

    public function loadSection($section)
    {
        if (!isset($this->sections[$section]) and $section !== '')
        {
            $this->loadOverlay($section);
            return True;
        }

        return False;
    }

    /**
      * Simply get key using config->KEYNAME
      *
      * @param string $var Key name
      * @return mixed
      * @author Damian Kęska
      */

    public function __get($var)
    {
        return $this->getKey($var);
    }

    /**
      * Set configuration key
      *
      * @param string $key
      * @param mixed $value
      * @param string $type
      * @return mixed
      * @author Damian Kęska
      */

    public function setKey($key, $value=null, $type=null, $section=null)
    {
        if(!$key or $value === null)
            return False;
		
		// load configuration section first
        if ($section !== null)
            $this->loadSection($section);
            
        if (isset($this->overlay[$key]))
        {
            // if section changed tell the framework that overlay changed
            if (isset($this->overlay[$key][2]) and $this->overlay[$key][2] !== $section and $section !== null)
            {
                if ($section === null)
                    $section = '';
            
                if ($this->overlay_modified[(string)$key] != 'created')
                {
                    $this->overlay_modified[(string)$key] = True;
                }
                
                $this->overlay[(string)$key][2] = $section;
            }
            
            // mark overlay as modified on value modification
            if ($this -> getKey($key) === $value)
            {
                return True;
            }
                
            if ($this->overlay_modified[(string)$key] != 'created')
            {
                $this->overlay_modified[(string)$key] = True;
            }
            
        } else {
            if ($section === null)
                $section = '';
        
            // new entry in overlay
            $this->overlay[(string)$key] = array(0 => 'string'); // default type
            $this->overlay[(string)$key][2] = $section;
            $this->overlay_modified[(string)$key] = 'created';
        }
        
        if($type !== '__none')
        {
            if ($this->panthera->types->exists($type))
            {
                $this->panthera->logging->output('config -> setKey( ' .$key. ', ' .str_replace("\n", " ", print_r($value, True)). ', ' .$type. ', \'' .$section. '\' )', 'pantheraConfig');
                $this->overlay[(string)$key][0] = $type;
            }
        }

        if ($this->panthera->types->validate($value, $this->overlay[(string)$key][0]))
        {
            $this->overlay[(string)$key][1] = $value;
            return True;
        }

        return False;
    }

    /**
     * Mark a key for removal
     * 
     * @param string $key Key name
     * @author Damian Kęska
     * @return bool
     */

    public function removeKey($key)
    {
        if (!isset($this->overlay[$key]))
            return False;

        // mark for deletion
        $this->overlay_modified[(string)$key] = 'delete';
        return True;
    }

    /**
     * Get key type
     *
     * @param string $type
     * @author Damian Kęska
     * @return string
     */

    public function getKeyType($key)
    {
        if (isset($this->overlay[$key]))
            return $this->overlay[$key][0];
        
        return 'string';
    }

    /**
     * Load configuration overlay from database
     *
     * @return int
     * @author Damian Kęska
     */

    public function loadOverlay($section='')
    {
        $array = null;
        $this->overlays++;
        
        if ($this -> panthera -> cache and $section != '*')
        {
            if ($this->panthera->cache->exists('configOverlay.' .$section))
            {
                $array = $this->panthera->cache->get('configOverlay.' .$section);
                $this->panthera->logging->output('Loaded config_overlay from cache "configOverlay.' .$section. '"', 'pantheraConfig');
            }
        }
        
        if ($array === null)
        {
            if ($section == '*')
                $SQL = 'SELECT `key`, `value`, `type`, `section` FROM `{$db_prefix}config_overlay`';
            else
                $SQL = 'SELECT `key`, `value`, `type`, `section` FROM `{$db_prefix}config_overlay` WHERE `section` = "' .trim($section). '"';
    
            $SQL = $this->panthera->db->query($SQL);
            $array = $SQL -> fetchAll(PDO::FETCH_ASSOC);
        }
        
        if (count($array) > 0)
        {
            foreach ($array as $key => $value)
            {
                if ($value['type'] == 'array')
                    $value['value'] = @unserialize($value['value']);

                if ($value['type'] == 'json')
                    $value['value'] = @json_decode($value['value']);
                        
                // remove null values
                if (!$value['section'])
                {
                    $value['section'] = '';
                }
                
                $this->overlay[$value['key']] = array($value['type'], $value['value'], $value['section']);
            }
        }
            
        if ($this->panthera->cache and $section != '*' and $section) {
            $this -> panthera -> cache -> set('configOverlay.' .$section, $array, 'configOverlay');
        } elseif (!$section and $this->panthera->cache and $section != '*')
            $this -> panthera -> cache -> set('configOverlay', $this -> overlay, 'configOverlay');

        // mark section as loaded
        if ($section)
            $this->sections[$section] = True;

        $this -> panthera -> logging -> output('Overlay "' .$section. '" loaded, total ' .count($array). ' keys', 'pantheraCore');
        
        return count($array);
    }


    /**
      * Save cached changes to database
      *
      * @return void
      * @author Damian Kęska
      */

    public function save()
    {
        $this -> panthera -> logging -> output('Preparing to save config overlay', 'pantheraConfig');
        
        if (count($this->overlay_modified) > 0)
        {
            $this->panthera->logging->output('Saving config overlay to SQL', 'pantheraConfig');

            $values = array();
            $sections = array();
            
            foreach ($this->overlay as $key => $value)
            {
                if ($this -> panthera -> cache and $value[2])
                {
                    $sections[$value[2]][] = array(
                        'value' => $value[1],
                        'key' => $key,
                        'type' => $value[0],
                        'section' => @$value[2]
                    );
                    
                    if (isset($this->overlay_modified[$key]))
                        $this -> modifiedSections[$value[2]] = true;
                }
                
                if (!isset($this->overlay_modified[$key]))
                    continue;

                if ($value[0] == 'json' and is_array($value[1]))
                    $value[1] = json_encode($value[1]);
                else {
                    if(is_array($value[1]))
                        $value[1] = serialize($value[1]);
                }

                /**
                  * Creating new entry
                  *
                  * @author Damian Kęska
                  */

                // creating new record in database
                if ($this->overlay_modified[$key] === 'created')
                {
                    $this->panthera->logging->output('Inserting ' .$key. ' variable (' .$value[0]. ')', 'pantheraConfig');
                    
                    try {
                        $q = $this->panthera->db->query('INSERT INTO `{$db_prefix}config_overlay` (`id`, `key`, `value`, `type`, `section`) VALUES (NULL, :key, :value, :type, :section);', array(
                            'key' => $key, 
                            'value' => $value[1], 
                            'type' => $value[0], 
                            'section' => @$value[2]
                        ));
                        
                    } catch (Exception $e) {
                        $this->panthera->logging->output('Cannot insert new key, SQL error: ' .print_r($e->getMessage(), True), 'pantheraConfig');
                    }

                /**
                  * Removing configuration variable
                  *
                  * @author Damian Kęska
                  */

                } elseif ($this->overlay_modified[$key] === 'delete') {

                    try {
                        $this -> panthera -> logging -> output('Removing key=' .$key. ' from configuration', 'pantheraConfig');
                        $this -> panthera -> db -> query('DELETE FROM `{$db_prefix}config_overlay` WHERE `key` = :key', array('key' => $key));
                    } catch (Exception $e) {
                        $this->panthera->logging->output('Cannot remove a key, SQL error: ' .print_r($e->getMessage(), True), 'pantheraConfig');
                    }

                /**
                  * Upading existing variable
                  *
                  * @author Damian Kęska
                  */

                // updating existing
                } else {
                    $this->panthera->logging->output('Update attempt of ' .$key. ' variable', 'pantheraConfig');
                    $this->panthera->db->query('UPDATE `{$db_prefix}config_overlay` SET `value` = :value, `type` = :type, `section` = :section WHERE `key` = :key ', array(
                        'value' => $value[1],
                        'key' => $key,
                        'type' => $value[0],
                        'section' => @$value[2]
                    ));
                }
            }

            // reset list of modified items
            $this->overlay_modified = array();

            // update cache
            if ($this->panthera->cache)
            {
                $this -> panthera -> logging -> output('Updating config_overlay cache', 'pantheraConfig');
                $this -> panthera -> cache -> set('configOverlay', $this->overlay, 'configOverlay');

                // save all modified sections
                foreach ($this -> modifiedSections as $section => $val)
                {
                    $keys = $sections[$section];
                    $this -> panthera -> logging -> output('Saving config section "' .$section. '" to cache, couting "' .count($keys). '" elements', 'pantheraConfig');
                    $this -> panthera -> cache -> set('configOverlay.' .$section, $keys, 'configOverlay');
                }
            }
            
            return true;
        }
    }

    /**
      * Get all configuration variables from app.php
      *
      * @return array
      * @author Damian Kęska
      */

    public function getConfig()
    {
        return $this->config;
    }

    /**
      * Update in-memory configuration
      *
      * @param array $array
      * @return void
      * @author Damian Kęska
      */

    public function updateConfigCache($array)
    {
        if (is_array($array))
        {
            $this->config = $array;
        }
    }

    /**
      * Get all configuration variables from overlay in database
      *
      * @return array
      * @author Damian Kęska
      */

    public function getOverlay()
    {
        return $this->overlay;
    }
}

// here will be our plugin system etc.
class pantheraCore
{
    protected $hooks = array();
    protected $plugins;
    protected $_savedSession;
    protected $permissionsTable = array();
    protected $modules = array();
    protected static $instance = null;

    public $config;
    public $db;
    public $user;
    public $template;
    public $session;
    public $pluginsDir;
    public $router;
    public $varCache=False;
    public $cache=False;
    public $hashingAlgorithm = 'md5';
    public $dateFormat = 'G:i:s d.m.Y';
    // public $qSerialize = 'serialize';

    // exit right after all plugins are loaded
    public $quitAfterPlugins = False;
    
    /**
	 * Panthera core class constructor
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public function __construct($config) 
    {
        self::$instance = $this;
        $config['SITE_DIR'] = realpath($config['SITE_DIR']);
        define('SITE_DIR', $config['SITE_DIR']); // get SITE_DIR from configuration if avaliable

        if (!is_file(SITE_DIR. '/content/app.php'))
            throw new Exception('Cannot find /content/app.php, looking in SITE_DIR=' .SITE_DIR);

        $c = _PANTHERA_CORE_TYPES_; $this->types = new $c($this); // data types
        $c = _PANTHERA_CORE_LOGGING_; $this->logging = new $c($this);
        $c = _PANTHERA_CORE_OUTPUT_CONTROL_; $this->outputControl = new $c($this);

        if ((isset($config['varCache']) and isset($config['cache'])) and !defined('SKIP_CACHE'))
        {
            $this->logging -> output('Initializing cache configured in app.php', 'pantheraCore');
            $this->loadCache($config['varCache'], $config['cache'], $config['session_key']);
        }
        
        /** Debugging **/
        $this -> logging -> toVarCache = (bool)$config['debug_to_varcache'];
        $this -> logging -> tofile = (bool)$config['debug_to_file'];

        $this -> logging -> output('Loading configuration', 'pantheraCore');
        $c = _PANTHERA_CORE_CONFIG_; $this->config = new $c($this, $config);
        
        // Panthera random SEED
        define('PANTHERA_SEED', hash('md4', $config['session_key'].rand(99, 999)));
        
        // Panthera database wrapper
        $c = _PANTHERA_CORE_DB_; $this->db = new $c($this);
        $this->config->loadOverlay();
        
        // debugging part two
        $this -> logging -> debug = (bool)$this->config->getKey('debug');
        
        if ($this -> logging -> debug)
        {
            $this -> logging -> strict = (bool)$this->config->getKey('debug.strict', 0, 'bool');
        }
        
        /** Cryptography support **/
        if (!function_exists('password_hash'))
        {
            // in older PHP versions there is no password hashing tools
            require PANTHERA_DIR. '/share/password-compat/lib/password.php';
            $this -> logging -> output ('Including userspace implementation of password hashing', 'pantheraCore');
        }

        // get hashing algorithm
        $this -> hashingAlgorithm = $this->config->getKey('hashing_algorithm', 'sha512', 'string');
        $this -> logging -> output ('Using "' .$this->hashingAlgorithm. '" algorithm', 'pantheraCore');

        /** End of Cryptography support **/

        /** CACHE SYSTEM **/

        // load cache if not loaded already
        if (!defined('SKIP_CACHE') and !$this->varCache)
        {
            // load variable cache system
            $varCacheType = $this->config->getKey('varcache_type', 'db', 'string');
            $cacheType = $this->config->getKey('cache_type', '', 'string');

            $this->loadCache($varCacheType, $cacheType);
        }
        /** END OF CACHE SYSTEM **/
        
        $c = _PANTHERA_CORE_ROUTER_;
        include_once PANTHERA_DIR. '/router.class.php';
        $this -> routing = new $c;
        $this -> routing -> setBasePath(rtrim(parse_url($this -> config -> getKey('url'), PHP_URL_PATH), '/'). '/');
        
        $c = _PANTHERA_CORE_LOCALE_;
        if (class_exists($c))
            $this->locale = new $c($this);

        $c = _PANTHERA_CORE_SESSION_;
        if (class_exists($c))
        {
            $this->session = new $c($this);

            if ($this->session->get('debug.filter.mode'))
            {
                $this->logging->filterMode = $this->session->get('debug.filter.mode');
                
                if ($this->session->exists('debug.filter'))
                    $this->logging->filter = $this->session->get('debug.filter');
            }
        }
        
        $this -> dateFormat = $this -> config -> getKey('dateFormat', 'G:i:s d.m.Y', 'string');

        //$this->config->getKey('pluginsContext', array(), 'array');

        // enable or disable caching on a server
        if (intval($this->config->getKey('mod_cache', '0', 'int')) > 0)
        {
            header("Cache-Control: must-revalidate, max-age=" .$this->config->getKey('mod_cache', '0', 'int'));
            header("Vary: Accept-Encoding");
        }

        // Security: iframe policy
        if ($this->config->getKey('header_framing', 'sameorigin', 'string'))
            header('X-Frame-Options: ' .$this->config->getKey('header_framing'));

        // Security: Mask PHP version
        if ($this->config->getKey('header_maskphp'))
            header('X-Powered-By: Django/1.2.1 SVN-13336');

        // Security: XSS protection for IE
        if ($this->config->getKey('header_xssprot'))
            header('X-XSS-Protection: 1; mode=block');

        // Security: this should reduce some drive-by-download attacks
        if ($this->config->getKey('header_nosniff'))
            header('X-Content-Type-Options: nosniff');

        $this->pluginsDir = array(
            PANTHERA_DIR. '/plugins', 
            SITE_DIR. '/content/plugins'
        );
    }

    /**
     * Get self instance
     * 
     * @return pantheraCore object
     */

    public static function getInstance()
    {
        return self::$instance;
    }
    
    /**
     * Raise an error eg. notfound
     * 
     * @param string $name Error template name eg. notfound, forbidden
     * @param mixed $info Additional informations passed to template
     * @return null
     */
    
    public static function raiseError($name, $info=null)
    {
        $panthera = pantheraCore::getInstance();
        $file = False;
        
        // in debugging mode we can have special versions of error pages
        if ($panthera -> logging -> debug)
            $file = getContentDir('templates/' .$name. '.debug.php');
        
        if (!$file)
            $file = getContentDir('templates/' .$name. '.php');
        
        if ($file)
        {
            include $file;
            exit;
        }
    }
    
    /**
      * Get cache time for selected object type
      *
      * @param string $cacheObjectType
      * @return int 
      * @author Damian Kęska
      */
    
    public function getCacheTime($cacheObjectType)
    {
        if (!$cacheObjectType)
        {
            $this->logging->output('Warning, an empty cache object type passed to getCacheTime', 'pantheraCore');
            return 120;
        }
        
        $array = $this -> config -> getKey('cache_timing', array(
            'usersTable' => 60
        ), 'array');
        
        if (isset($array[$cacheObjectType]))
            return $array[$cacheObjectType];
        else {
            $array[$cacheObjectType] = 120;
            $this -> config -> setKey('cache_timing', $array, 'array');
            $this -> config -> save();
            return 120; // default is 120 seconds if not found
        }
    }

    /**
      * Load caching modules
      *
      * @param string $varCacheType
      * @param string $cacheType
      * @return void
      * @author Damian Kęska
      */

    public function loadCache($varCacheType, $cacheType, $sessionKey='')
    {
        // primary cache (variables cache)
        if ($dir = getContentDir('modules/cache/varCache_' .$varCacheType. '.module.php'))
        {
            include_once $dir;
        }
        
        if (class_exists('varCache_' .$varCacheType))
        {
            try {
                $n = 'varCache_' .$varCacheType;
                $this->varCache = new $n($this, $sessionKey);
                $this->logging->output('varCache initialized, using ' .$varCacheType, 'pantheraCore');
            } catch (Exception $e) {
                $this->logging->output('Disabling varCache due to exception: ' .$e->getMessage(), 'pantheraCore');
                $this->varCache = false;
            }
        }
        
        if ($cacheType != '')
        {
            // if secondary cache type is same as primary, link both
            if ($cacheType == $varCacheType)
                $this->cache = $this->varCache;
            else {
                
                $dir = null;
                
                if ($dir = getContentDir('modules/cache/varCache_' .$cacheType. '.module.php'))
                {
                    include_once $dir;
                }

                // load secondary cache
                if (class_exists('varCache_' .$cacheType))
                {
                    try {
                        $n = 'varCache_' .$cacheType;
                        $this->cache = new $n($this, $sessionKey);
                        $this->logging->output('Cache initialized, using ' .$cacheType, 'pantheraCore');
                    } catch (Exception $e) {
                        $this->logging->output('Disabling cache due to exception: ' .$e->getMessage(), 'pantheraCore');
                        $this->cache = false;
                    }
                }

            }
        }
    }

    /* ==== MODULES ==== */

    /**
	 * Import module
	 *
     * @param string $module Name
     * @param bool $constructModule Construct $moduleModule class object
     * @param bool $forceReload Reload module if already loaded
	 * @return bool|object
	 * @author Damian Kęska
	 */

    public function importModule($module, $constructModule=False, $forceReload=False)
    {
        $module = strtolower($module);

        if ($this->moduleImported($module) and $forceReload == False)
            return True;

        // load built-in phpQuery library
        if ($module == 'phpquery')
        {
            include_once PANTHERA_DIR. '/share/phpQuery.php';
            $this->logging->output('Imported "phpquery" from /lib/modules', 'pantheraCore');
            $this->modules[$module] = True;
            return True;
        }
        
        $this->logging->startTimer();
        $f = '';
        
        if (is_file(PANTHERA_DIR. '/modules/' .$module. '.module.php'))
        {
            $f = PANTHERA_DIR. '/modules/' .$module. '.module.php';
        } elseif (is_file(SITE_DIR. '/content/modules/' .$module. '.module.php')) {
            $f = SITE_DIR. '/content/modules/' .$module. '.module.php';
        }
        
        if ($f)
        {
            include_once $f;
            
            $this->logging->output('Imported "' .$module. '" from /lib/modules', 'pantheraCore');
            $this->modules[$module] = True;
        } else {
            $this->logging->output('Cannot import "' .$module. '" module', 'pantheraCore');
        }
        
        if ($constructModule)
        {
            $name = basename($module). 'Module';
            
            if (class_exists($name, true))
            {
                return new $name;
            }
        }
        
        return isset($this->modules[$module]);
    }
    
    /**
      * Simply list all modules
      *
      * @return array
      * @author Damian Kęska
      */
    
    public function listModules()
    {
        return $this->modules;
    }

    /**
	 * Check if module exists
	 *
     * @param module name
	 * @return string (path)
	 * @author Damian Kęska
	 */

    public function moduleExists($module)
    {
        if (is_file(PANTHERA_DIR. '/modules/' .$module. '.module.php'))
            return PANTHERA_DIR. '/modules/' .$module. '.module.php';

        if (is_file(SITE_DIR. '/content/modules/' .$module. '.module.php'))
            return SITE_DIR. '/content/modules/' .$module. '.module.php';

        return False;
    }

    /**
	 * Check if module was already imported
	 *
     * @param module name
	 * @return bool
	 * @author Damian Kęska
	 */

    public function moduleImported($module)
    {
        return isset($this->modules[$module]);
    }

    /**
     * Getting permissions table entry
     * 
     * @param string $name Permissions name
     * @return null|string|array
     */

    public function getPermission($name, $dontLocalize=False)
    {
        if (!$this->permissionsTable)
            $this -> permissionsTable = $this -> config -> getKey('panthera.permissions', array(), 'array', 'meta'); // load from meta overlay
        
        if (isset($this->permissionsTable[$name]))
        {
            if (!$dontLocalize and is_array($this->permissionsTable[$name]))
                return localize($this->permissionsTable[$name][0], $this->permissionsTable[$name][1]);
                      
            return $this->permissionsTable[$name];
        }
    }

    /**
     * List all cached permissions
     * 
     * @return array
     */

    public function listPermissions()
    {
        if (!$this->permissionsTable)
            $this -> permissionsTable = $this -> config -> getKey('panthera.permissions', array(), 'array', 'meta'); // load from meta overlay
        
        return $this->permissionsTable;
    }

    /**
      * Return cache type
      *
      * @param string $cacheType Cache type can be cache or varCache
      * @return string|null
      * @author Damian Kęska
      */

    public function cacheType($cache)
    {
        if ($cache == 'cache')
        {
            if ($this->cache)
                return $this->cache->type;
        } else {
            if ($this->varCache)
                return $this->varCache->type;
        }
    }

    /**
     * Get all defined hooks
     * 
     * @return array
     */
    
    public function getAllHooks()
    {
        return $this->hooks;
    }
    
    /**
     * Plug-in a function to a hook slot
     * 
     * @param string $hookName Hooking slot name
     * @param string|array $function Function address eg. "var_dump" or "array($object, 'method')"
     * @param int $priority Priority on execution (this can be used to execute function before or after other hooked function) If there is already any other hook with same priority defined it will be increased.
     */

    public function add_option($hookName, $function, $priority=null)
    {
        // create array with hooks group
        
        if (!isset($this->hooks[$hookName]))
        {
            $this->hooks[$hookName] = array();
        }

        // is this a class method or just a function?
        if(gettype($function) == "array") // here is situation when it will be a class
        {
            if(count($function) != 2)
            {
                $this->logging->output("Invalid function array specified to add_option, requires to be first argument a class type and second a function name of that class", 'pantheraCore');
                return False;
            }

        } else { // and here is just a simple function
            if(!function_exists($function))
            {
                $this->logging->output("Hooked function ".$function." does not exists.", 'pantheraCore');
                return False;
            }
        }

        if ($priority)
        {
            $priority = intval($priority);
            
            while (isset($this->hooks[$hookName][$priority]))
            {
                $priority++;
            }
            
            $this->hooks[$hookName][$priority] = $function;
            return true;
        }
        
        $this->hooks[$hookName][] = $function;
        return True;
    }
    
    /**
      * Execute all hooks without returning output
      * WARNING: To avoid problems remember one important rule - always return args you get in modified or in unmodified form
      *
      * @param string $hookName
      * @param mixed $args Args to pass to hook
      * @param mixed $additionalInfo Additional information to pass to function as a second argument
      * @return bool 
      * @author Damian Kęska
      */

    public function get_options($hookName, $args='', $additionalInfo=null)
    {
        if(!isset($this->hooks[$hookName]))
            return false;

        ksort($this->hooks[$hookName]);
        
        foreach ($this->hooks[$hookName] as $key => $hook)
        {
            if (gettype($hook) == "array")
            {
                if (!method_exists($hook[0], $hook[1]))
                    continue;

                if (is_object($hook[0]))
                    $hook[0]->$hook[1]($args, $additionalInfo);
                else
                    $hook[0]::$hook[1]($args, $additionalInfo);

            } else {
                if (!function_exists($hook))
                    continue;

                $hook($args, $additionalInfo);
            }
        }
        
        return False;
    }
    
    /**
      * Execute all hooks without returning output
      * WARNING: To avoid problems remember one important rule - always return args you get in modified or in unmodified form
      *
      * @param string $hookName
      * @param mixed $args Args to pass to hook
      * @param mixed $additionalInfo Additional information to pass to function as a second argument
      * @return bool 
      * @author Damian Kęska
      */
    
    public function get_options_ref($hookName, &$args, $additionalInfo=null)
    {
        if(!isset($this->hooks[$hookName]))
            return false;

        ksort($this->hooks[$hookName]);
        
        foreach ($this->hooks[$hookName] as $key => $hook)
        {
            if (gettype($hook) == "array")
            {
                if (!method_exists($hook[0], $hook[1]))
                    continue;

                if (is_object($hook[0]))
                    $hook[0]->$hook[1]($args, $additionalInfo);
                else
                    $hook[0]::$hook[1]($args, $additionalInfo);

            } else {
                if (!function_exists($hook))
                    continue;

                $hook($args, $additionalInfo);
            }
        }
        
        return False;
    }
    
    /**
      * Execute all hooks and return parsed data
      * WARNING: To avoid problems remember one important rule - always return args you get in modified or in unmodified form
      *
      * @param string $hookName
      * @param mixed $args Args to pass to hook
      * @param bool $fixOnFail Skip any hook that returns null or false
      * @param mixed $additionalInfo Additional information to pass to function as a second argument
      * @return mixed 
      * @author Damian Kęska
      */

    public function get_filters($hookName, $args='', $fixOnFail=False, $additionalInfo=null)
    {
        if(!isset($this->hooks[$hookName]))
            return $args;

        $backup = $args;

        ksort($this->hooks[$hookName]);
        
        foreach ($this->hooks[$hookName] as $key => $hook)
        {
            if (gettype($hook) == "array")
            {
                if (!method_exists($hook[0], $hook[1]))
                    continue;

                if (is_object($hook[0]))
                    $args = $hook[0]->$hook[1]($args, $additionalInfo);
                else
                    $args = $hook[0]::$hook[1]($args, $additionalInfo);

            } else {
                if (!function_exists($hook))
                    continue;

                $args = $hook($args, $additionalInfo);
            }
            
            if ($args)
                $backup = $args;
            
            if (!$args and $fixOnFail)
                $args = $backup;
        }
        
        return $args;
    }
    
    /*
     * Remove hooked function
     * 
     * @param string $hookName Hook name
     * @param string|array $function Function or method name
     * @return bool
     */
    
    public function remove_option($hookName, $function)
    {
        if (!$this->hooks[$hookName])
        {
            return False;
        }
        
        if ($key = array_search($function, $this->hooks[$hookName]))
        {
            unset($this->hooks[$hookName][$key]);
            return True;
        }
    }
    
    /**
      * Execute all hooks and return results from all hooks in an array
      *
      * @param string $hookName
      * @param mixed $args Args to pass to hook
      * @param mixed $additionalInfo Additional information to pass to function as a second argument
      * @return array
      * @author Damian Kęska
      */
    
    public function get_filters_array($hookName, $args='', $additionalInfo=null)
    {
        if(!isset($this->hooks[$hookName]))
            return array();

        $output = array();

        foreach ($this->hooks[$hookName] as $key => $hook)
        {
            if (gettype($hook) == "array")
            {
                if (!method_exists($hook[0], $hook[1]))
                    continue;

                if (is_object($hook[0]))
                {
                    $output[get_class($hook[0]).'___'.$hook[1]] = $hook[0]->$hook[1]($args, $additionalInfo);
                } else {
                    $output[$hook[0].'___'.$hook[1]] = $hook[0]::$hook[1]($args, $additionalInfo);
                }
                
            } else {
                if (!function_exists($hook))
                    continue;

                $output[$hook] = $hook($args, $additionalInfo);
            }
        }

        return $output;
    }

    /* ==== END OF HOOKING FUNCTIONS ==== */

    /**
	 * Toggle selected plugin (enable or disable)
	 *
     * @param string $plugin Plugin directory name
     * @param bool $value True or False
	 * @return array
	 * @author Damian Kęska
	 */

    public function switchPlugin($plugin, $value)
    {
        if ($plugin == '.' or $plugin == '..' or $plugin == '')
            return False;

        $plugins = $this->config->getKey('plugins');

        if (!$this->pluginExists($plugin))
        {
            $this->logging->output('Plugin "' .$plugin. '" does not exists, cannot change state', 'pantheraCore');
            return False;
        }

        $plugins[$plugin] = (bool)$value;
        $this->logging->output('Setting plugin "' .$plugin. '" state to "' .(int)$value. '"', 'pantheraCore');
        $this->config->setKey('plugins', $plugins, 'array');

        return True;
    }

    /**
      * Check if plugin exists
      *
      * @param string $name Plugin directory name
      * @return bool
      * @author Damian Kęska
      */

    public function pluginExists($name)
    {
        if ($name == '.' or $name == '..' or $name == '')
            return False;

        foreach ($this->pluginsDir as $dir)
        {
            if (is_dir($dir. '/' .$name. '/'))
            {
                return True;
            }
        }

        return False;
    }

    /**
	 * List all enabled or disabled plugins
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public function getPlugins()
    {
        // list of enabled plugins
        $configPlugins = $this->config->getKey('plugins', array(), 'array');

        // get all plugins from /lib and /content
        foreach ($this->pluginsDir as $dir)
        {
            if(!is_dir($dir))
                $this->logging->output('Cannot find plugins directory "'.$dir.'"!', 'pantheraCore');

            $directoryListing = scandir($dir);
            
            foreach ($directoryListing as $file)
            {
                if ($file == "." or $file == ".." or is_file($file))
                    continue;

                $enabled = False;

                if (isset($configPlugins[$file]))
                {
                    if ($configPlugins[$file] == True)
                        $enabled = True;
                }
                
                $files[$file] = array(
                    'include_path' => $dir. '/' .$file, 
                    'enabled' => $enabled, 
                    'info' => $this->plugins[$file],
                );
            }
        }

        return $files;
    }

    /**
	 * Load all enabled plugins
	 *
     * @param array $pluginsDir Optional parameter to specify alternative plugin directories
	 * @return void
	 * @author Damian Kęska
	 */

    public function loadPlugins($pluginsDir='')
    {
        $panthera = pantheraCore::getInstance();

        if ($pluginsDir == '')
            $pluginsDir = array(PANTHERA_DIR. '/plugins', SITE_DIR. '/content/plugins');

        $files = array();

        // we will scan multiple directories for plugins here
        foreach ($pluginsDir as $dir)
        {
            if(!is_dir($dir))
                $this->logging->output('Cannot find plugins directory "'.$dir.'"!', 'pantheraCore');

            $directoryListing = scandir($dir);

            foreach ($directoryListing as $file)
            {
                if ($file == "." or $file == ".." or is_file($file))
                    continue;

                $files[$file] = $dir. '/' .$file;
            }
        }

        $plugins = array();
        $configPlugins = $this->config->getKey('plugins', array(), 'array');

        foreach ($files as $key => $value)
        {
            // dont include plugins not listed in configuration file
            if (!array_key_exists($key, $configPlugins))
                continue;
            else { // disable plugins with False value in configuration file
                if($configPlugins[$key] == False)
                    continue;
            }

            $this -> logging -> output('Loading '.$value.' plugin', 'pantheraCore');
            $exp = explode('.', $value);

            if ($exp[1] == 'cgi' and PANTHERA_MODE != "CGI")
            {
                $this -> logging -> output('Skipping loading of "' .$value. '" plugin in ' .PANTHERA_MODE. ' mode', 'pantheraCore');
                continue;
            }

            // check if main file exists in pluin directory
            if(is_file($value."/plugin.php"))
            {
                //$pluginInfo = array();
                unset($pluginClassName);

                include($value."/plugin.php");
                
                if (!isset($pluginClassName))
                {
                    $pluginClassName = str_replace('.cgi', '', basename($value)). 'Plugin';
                }
                
                if (!class_exists($pluginClassName))
                {
                    $this -> logging -> output('Failed to load plugin "' .$value. '", please check if it contains "' .$pluginClassName. '" class', 'pantheraCore');
                    continue;
                }
                
                $pluginInfo = $pluginClassName::getPluginInfo();
                
                if (count($pluginInfo) > 0)
                {
                    $this->registerPlugin($pluginInfo['name'], $value."/plugin.php", $pluginInfo);
                    $pluginClassName::run();
                }
                //$plugins[] = $value."/plugin.php";
            }
        }

        return $plugins;
    }

    /**
	 * Register new plugin
	 *
     * @param string $pluginName Plugin name
     * @param string $file Direct path leading to plugin.php of plugin
     * @param array $info Informations about plugin
	 * @return bool
	 * @author Damian Kęska
	 */

    function registerPlugin($pluginName, $file, $info='')
    {
        $dir = str_replace(pantheraUrl('{$PANTHERA_DIR}/plugins/'), '', $file);
        $pInfo = pathinfo($dir);
        $dir = $pInfo['dirname'];

        if ($dir == "." or $dir == ".." or $dir == "")
            return False;

        if (is_file(SITE_DIR. '/content/plugins/' .$pluginName. '/plugin.php'))
            $type = 'normal';
        else
            $type = 'module'; // TODO: Create better module plugins support

        if (strpos($dir, '/') !== false) {
            $dir = explode("/", $dir);
            $dir = end($dir);   
        }
        
        $this->plugins[$dir] = array('name' => $pluginName, 'type' => 'module', 'file' => $file, 'meta' => $info);
        $this->logging->output("Registering plugin ".$pluginName." for file ".$file.", key=".$dir);
        return True;
    }

    /**
	 * Executes at the end of the script (save all caches etc.)
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    function finish()
    {
        $this->_savedSession = True;
        $this->get_options('session_save', False);
    }

    // in case when developer forgot to use finish() at the end of script
    function __destruct()
    {
        if($this->_savedSession == False)
            $this->finish();
    }

}

abstract class pantheraClass
{
    protected $panthera = null;
    protected static $instance = null;
    
    public function getInstance()
    {
        return self::$instance;
    }

    public function __construct()
    {
        $this->panthera = pantheraCore::getInstance();
        self::$instance = $this;
    }
}

/**
  * Class autoloader for Panthera Framework
  *
  * @package Panthera\core
  * @param string $class name
  * @return mixed 
  * @author Damian Kęska
  */

function __pantheraAutoloader($class)
{
    $panthera = pantheraCore::getInstance();

    if ($panthera)
    {
        $panthera -> logging -> output ('Requested ' .$class. ' class', 'pantheraCore');
    
        // defaults
        $cachedClasses = $panthera -> config -> getKey('autoloader');
        
        // update autoloader cache if not generated yet
        if (!$cachedClasses)
        {
            $panthera -> importModule('autoloader.tools');
            pantheraAutoloader::updateCache();
            $cachedClasses = $panthera -> config -> getKey('autoloader');
        }
        
        if (isset($cachedClasses[$class]))
        {
            $panthera -> importModule($cachedClasses[$class]);
        }
    }
}

spl_autoload_register('__pantheraAutoloader');

/**
 * Panthera data validation class. Strings, numbers, urls, ip adresses and other data can be validated here.
 *
 * @package Panthera\core
 * @author Damian Kęska
 */

class pantheraTypes extends pantheraClass
{
    // 1 means built-in type
    private $types = array('int' => 1, 'email' => 1, 'bool' => 1, 'ip' => 1, 'regexp' => 1, 'url' => 1, 'json' => 1, 'array' => 1, 'string' => 1, 'phone' => 1);

    public function _int($v) { return is_numeric($v); }
    public function _string($v) { return is_string($v); }
    public function _email($v) { return filter_var($v, FILTER_VALIDATE_EMAIL); }
    public function _ip($v) { return filter_var($v, FILTER_VALIDATE_IP); }
    public function _regexp($v) { return filter_var($v, FILTER_VALIDATE_REGEXP); }
    public function _url($v) { return filter_var($v, FILTER_VALIDATE_URL); }
    public function _json($v) { return True; } // TODO: Validate json
    public function _array($v) { return True; } // TODO: Validate arrays

    /**
      * Supports international phone number with whitespaces
      *
      * @param string $v phone number
      * @return bool
      * @author Damian Kęska
      */

    public function _phone($v) {
        $v = str_replace(' ', '', $v);
        $v = str_replace('-', '', $v);

        // with whitespaces
        if (preg_match('/^\d{3}\s\d{3}\s\d{4}\s\d{3}$/', $v))
            return True;

        // 111-222-333-444
        if (preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $v))
            return True;

        // Polish phone numbers
        if (preg_match('/^[0-9\+]{5,13}$/', $v))
            return True;

        return False;
    }

    public function _bool($v)
    {
        if (is_numeric($v) or is_int($v))
        {
            if ((int)$v >= 0)
                return True;
        }

        if (is_bool($v))
        {
            return True;
        }

        return filter_var($v, FILTER_VALIDATE_BOOLEAN);

    }

    /**
	 * Detect variable type
	 *
	 * @return string
	 * @author Damian Kęska
	 */

    public function detect($value)
    {
        foreach ($this->types as $Key => $Value)
        {
            if ($key == 'string' or $key == 'array' or $key == 'json')
                continue;

            if ($this->validate($value, $Key))
                return $test;
        }

        return False;
    }

    /**
	 * Validate variable as type
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function validate($value, $type)
    {
        if (method_exists($this, '_' .strtolower((string)$type)))
        {
            $function = '_' .$type;
            return $this->$function($value);
        }

        if (array_key_exists($types, $type))
        {
            if (!is_int($this->types[$type]))
            {
                if(is_array($this->types[$type]))
                    return $this->types[$type][0]->$this->types[$type][1]($value);
                else
                    return $this->types[$type]($value);
            }
        }

        // return true if no validator avaliable for type
        return True;
    }

    /**
	 * Get all avalible types
	 *
	 * @return array
	 * @author Damian Kęska
	 */

    public function getTypes()
    {
        $types = array();

        foreach ($this->types as $Key => $Value)
            $types[] = $Key;

        return $types;
    }

    /**
	 * Check if data type exists
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function exists($type)
    {
        if(array_key_exists($type, $this->types))
            return True;

        return False;
    }

    /**
	 * Add new validator
	 *
	 * @return bool
	 * @author Damian Kęska
	 */

    public function addValidator($type, $validator)
    {
        // if its just a function
        if (is_string($validator))
        {
            if (function_exists($validator))
            {
                $this->types[$type] = $validator;
                return True;
            }
        }

        // if its a class method
        if (is_array($validator))
        {
            // we accept only arrays like ($classobject, 'method')
            if (count($validator) != 2)
                return False;

            if (method_exists($validator[0], $validator[1]))
            {
                $this->types[$type] = $validator;
                return True;
            }
        }

        return False;
    }

    /**
      * Parse string type to real data type
      *
      * @param string $input Input data represented as string
      * @param string $type Type to convert to
      * @return mixed
      * @author Damian Kęska
      */

    public function parse($input, $type)
    {
        if ($type == 'bool')
            return (bool)$input;

        if ($type == 'int')
            return intval($input);

        if ($type == 'ip')
        {
            if (!$this->_ip($input))
                return '0.0.0.0';
        }

        return $input;
    }
}

class _arrayObject
{
    private $__data, $__changed=False;

    public function __construct($data)
    {
        if (!is_array($data))
            $data = array();

        $this->__data = $data;
    }

    public function __get($var)
    {
        if(array_key_exists($var, $this->__data))
            return $this->__data[$var];

        return False;
    }

    public function __set($var, $value)
    {
        if ($value === $this->__data[$var])
        {
            return False;
        }
        
        $this->__changed = True;
        $this->__data[$var] = $value;
    }
    
    public function get($var) { return $this->__get($var); }
    public function set($var, $value) { return $this->__set($var, $value); }
    
    /**
      * Remove a variable
      *
      * @param string $var
      * @return true|null
      * @author Damian Kęska
      */
    
    public function remove($var)
    {
        if (isset($this->__data[$var]))
        {
            unset($this->__data[$var]);
            $this->__changed = True;
            return True;
        }
    }

    public function listAll()
    {
        return $this->__data;
    }

    public function changed()
    {
        if($this->__changed == True)
            return $this->__data;

        return False;
    }
}

/**
 * Exit application returning serialized array in json format
 *
 * @param array $array
 * @hook panthera.ajax_exit
 * @return string
 * @author Damian Kęska
 */

function ajax_exit($array)
{
    $panthera = pantheraCore::getInstance();

    if ($panthera -> logging -> debug == True)
        $panthera -> logging -> output('ajax_exit: ' .json_encode($array), 'pantheraCore');
        
    $panthera -> outputControl -> flushAndFinish();
    
    // insert buffered log if avaliable to "message" element
    if (isset($array['message']))
    {
        $array['message'] = str_ireplace('{$bufferedOutput}', $panthera -> outputControl -> get(), $array['message']);
    }
    
    // allow plugins to modify output
    $array = $panthera -> get_filters('panthera.ajax_exit', $array);
    
    print(json_encode($array));
    pa_exit('', True);
}

/**
 * Ajax equivalent of var_dump
 * 
 * @package Panthera\core
 * @param mixed $mixed
 * @return null
 */

function ajax_dump($mixed, $usePrint_r=False)
{
    if (!$usePrint_r)
        $message = r_dump($mixed);
    else
        $message = print_r($mixed, true);
    
    ajax_exit(array(
        'status' => 'failed',
        'message' => $message,
    ));
}

/**
 * Finish all processes and exit application
 *
 * @param string $string Optional message
 * @return string
 * @author Damian Kęska
 */

function pa_exit($string='', $ajaxExit=False)
{
    $panthera = pantheraCore::getInstance();

    // just to be sure in logs
    $panthera -> logging -> output('Called pa_exit, goodbye.', 'pantheraCore');

    // execute all hooks to save data
    $panthera -> get_options('page_load_ends', $ajaxExit);
    $panthera -> finish();

    ob_start();

    die($string);
}

/**
 * Make simple redirection using "Location" header and exit application
 *
 * @param string $url Application internal url, eg. index.php
 * @return string
 * @author Damian Kęska
 */

function pa_redirect($url, $code=null)
{
    $panthera = pantheraCore::getInstance();
    
    if (is_int($code))
        header('Location: '.$panthera->config->getKey('url'). '/' .pantheraUrl($url, False, 'frontend'), TRUE, $code);
    else
        header('Location: '.$panthera->config->getKey('url'). '/' .pantheraUrl($url, False, 'frontend'));
    
    pa_exit();
}

/**
 * This function will safely parse meta tags from array
 *
 * @param array $tags Meta tags in an associative array
 * @return string
 * @author Damian Kęska
 */

function parseMetaTags($tags)
{
    if (count($tags) == 0 or !is_array($tags))
        return "";

    $code = '';

    foreach ($tags as $meta)
    {
        $code .= filterMetaTag($meta). ',';
    }

    return rtrim($code, ',');
}

function filterMetaTag($tag)
{
    $a = array('"', "'");
    return trim(strip_tags(str_replace($a, '', $tag)));
}

/**
 * Create SEO friendly name
 *
 * @param string $string Article title, or file name, just a string to be converted
 * @return string
 * @author Alexander <http://forum.codecall.net/topic/59486-php-create-seo-friendly-url-titles-slugs/#axzz2JCfcCHFX>
 */

function seoUrl($string) {
    //Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
    $string = strtolower($string);
    //Strip any unwanted characters
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}

/**
 * Convert Panthera special variables in urls with reverse function
 *
 * @param string $url URL to be parsed
 * @param bool $reverse Set to true if you want to convert complete URL back to Panthera internal url eg. input: http://example.com/index output: {$PANTHERA_URL}/index
 * @param string $type Convert only "frontend" or "system" variables
 * @return string
 * @author Damian Kęska
 */

function pantheraUrl($url, $reverse=False, $type='')
{
    $panthera = pantheraCore::getInstance();

    $var = array( );
    
    if (!$type or $type == 'frontend')
    {
        $var['{$AJAX_URL}'] = $panthera->config->getKey('ajax_url');
        $var['{$PANTHERA_URL}'] = $panthera->config->getKey('url');
    }
    
    if (!$type or $type == 'system')
    {
        $var['{$PANTHERA_DIR}'] = PANTHERA_DIR;
        $var['{$SITE_DIR}'] = SITE_DIR;
        $var['{$upload_dir}'] = $panthera->config->getKey('upload_dir');
    }

    if ($reverse == True)
    {
        foreach ($var as $key => $value)
            $url = str_ireplace($value, $key, $url);
    } else {
        foreach ($var as $key => $value)
            $url = str_ireplace($key, $value, $url);
    }

    return $url;
}

if (!function_exists('json_last_error'))
{
    function json_last_error() {
        return JSON_ERROR_NONE;
    }
}

/**
 * Checks if string is a valid json type
 *
 * @return bool
 * @author Damian Kęska
 */

function isJson($string) {
    @json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Is Panthera running in debugging mode?
 *
 * @return bool
 * @author Damian Kęska
 */

function isDebugging()
{
    $panthera = pantheraCore::getInstance();
    return $panthera->logging->debug;
}

/**
 * Get full path of error page template. Returns empty string if template does not exists either in content as in lib
 *
 * @param string $string Page name eg. db_error
 * @return string
 * @author Damian Kęska
 */

function getErrorPageFile($name)
{
    if (is_file(SITE_DIR. '/content/templates/' .$name. '.php'))
        return SITE_DIR. '/content/templates/' .$name. '.php';
    elseif (is_file(PANTHERA_DIR. '/templates/' .$name. '.php'))
        return PANTHERA_DIR. '/templates/' .$name. '.php';

    return '';
}

/**
 * Unify quotes in string eg. replace " to '
 *
 * @param string $string String to be parsed
 * @return string
 * @author Damian Kęska
 */

function unifyQuotes($string)
{
    return str_replace('"', "'", $string);
}

/**
 * Convert MySQL-like timestamp to formatted date and time
 *
 * @param string $timestamp MySQL-like timestamp
 * @param string $format new format eg. d.m.Y which means day.month.year - eg. 01.01.2096
 * @return string
 * @author Damian Kęska
 */

function timestampToDate($timestamp, $format)
{
    return date($format, strtotime($timestamp));
}

/**
 * Cut off string to fit in maximum length, adds "..." at the end of string
 *
 * @param string $string Input string
 * @param string $maxLen Maximum length
 * @return string
 * @author Damian Kęska
 */

function strCut($string, $maxLen)
{
    if (strlen($string) >= $maxLen)
    {
        return substr($string, 0, $maxLen). '...';
    } else
        return $string;
}

/**
  * Get numbers from a string and return as an array
  *
  * @param string $str Input string
  * @return array
  * @author Damian Kęska
  */

function strGetNumbers($str)
{
    preg_match('!\d+!', $str, $matches);
    return $matches;
}

/**
  * Generate random string
  *
  * @param int $length Default length is 10
  * @param string $characters Optional characters range
  * @return array
  * @author Stephen Watkins <http://stackoverflow.com/users/151382/stephen-watkins>
  * @author Damian Kęska
  */

function generateRandomString($length = 10, $characters='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
  * Search for file name in /content and /lib and return first match
  *
  * @param string $dir File or directory name
  * @return string
  * @author Damian Kęska
  */

function getContentDir($dir)
{
    if (file_exists(SITE_DIR.'/'.$dir))
        return SITE_DIR.'/'.$dir;

    if (file_exists(SITE_DIR. '/content/'.$dir))
        return SITE_DIR. '/content/'.$dir;

    if (file_exists(PANTHERA_DIR. '/'.$dir))
        return PANTHERA_DIR.'/'.$dir;
}

/**
  * Print object informations
  *
  * @param object $obj Input object
  * @param bool $returnAsString
  * @debug
  * @return void
  * @author Damian Kęska
  */

function object_dump($obj, $returnAsString=False)
{
    if (!is_object($obj))
        return False;

    $class = new ReflectionClass($obj);

    $data = array('class' => get_class($obj),
                  'file' => $class->getFileName(),
                  'methods' => $class->getMethods(),
                  'properties' => $class->getProperties(),
                  'constants' => $class->getConstants()
    );

    if ($returnAsString)
    {
        return r_dump($data);
    } else {
        var_dump($data);
    }
}

/**
  * Make a var_dump and return result
  *
  * @return array
  * @author Damian Kęska
  */

function r_dump()
{
    ob_start();
    $var = func_get_args(); 
    call_user_func_array('var_dump', $var);
    return ob_get_clean();
}

/**
  * List all class/object methods
  *
  * @param object|string $obj
  * @param bool $return Return as string
  * @return string|bool 
  * @author Damian Kęska
  */

function object_info($obj, $return=False)
{
    if (is_string($obj))
    {
        if(class_exists($obj))
        {
            return ReflectionClass::export($obj, $return);
        }
    }

    if (is_object($obj))
    {
        return ReflectionObject::export($obj, $return);
    }
    
    return False;
}

/**
  * Splits seconds with microseconds from microtime() output
  *
  * @param string $time Optional input time, if not specified it will be generated with microtime()
  * @return float
  * @author http://php.net
  */

function microtime_float($time='')
{
    if ($time == '')
        $time = microtime();

    list($usec, $sec) = explode(" ", $time);
    return ((float)$usec + (float)$sec);
}

/**
  * Calculate diffirences between dates and show in user friendly format
  *
  * @param int $timestamp_past
  * @param int $timestamp_future
  * @param bool $years
  * @param bool $months
  * @param bool $days
  * @param bool $hours
  * @param bool $mins
  * @param bool $secs
  * @param bool $display_output
  * @return string|array
  * @author Damian Kęska
  */

function date_calc_diff($timestamp_past, $timestamp_future, $years = true, $months = true, $days = true, $hours = true, $mins = true, $secs = true, $display_output = true)
{
    $panthera = pantheraCore::getInstance();

    if (is_int($timestamp_past))
    {
        $timestamp_past = date($panthera -> dateFormat, $timestamp_past);
    }
    
    if (is_int($timestamp_future))
    {
        $timestamp_future = date($panthera -> dateFormat, $timestamp_future);
    }

    try {
        $past = new DateTime($timestamp_past);
        $future = new DateTime($timestamp_future);
        $diff = $future->diff($past);
        
    } catch (Exception $e) {
        if ($display_output == False)
        {
            return array();
        } else {
            return "";
        }
    }
    
    $timeFormats = array(
        'years' => '%y',
        'months' => '%m',
        'days' => '%a',
        'hours' => '%H',
        'minutes' => '%i',
        'seconds' => '%s'
    );
    
    if ($years == True)
    {
        if ($diff->format('%y') > 0)
        {
            $array['years'] = $diff->format('%y');
        }
    }
    
    if ($months == True)
    {
        if ($diff->format('%m') > 0)
        {
            $array['months'] = $diff->format('%m');
        }
    }
    
    if ($days == True)
    {
        if ($diff->format('%a') > 0)
        {
            $array['days'] = $diff->format('%a');
        }
    }
    
    if ($hours == True)
    {
        if ($diff->format('%H') > 0)
        {
            $array['hours'] = $diff->format('%H');
        }
    }
    
    if ($mins == True)
    {
        if ($diff->format('%i') > 0)
        {
            $array['minutes'] = $diff->format('%i');
        }
    }
    
    if ($secs == True)
    {
        if ($diff->format('%s') > 0)
        {
            $array['seconds'] = $diff->format('%s');
        }
    }
    
    
    
    if (!$display_output)
    {
        return $array;
        
    } else {
        $output = '';
        $maxRange = 2; // we accept only max X data details eg. year, month (2 elements) or hour, minute
        $range = 0;
    
        foreach ($array as $timeRange => $value)
        {
            $range++;
            
            if ($range > $maxRange)
            {
                break;
            }
            
            $output .= $diff->format($timeFormats[$timeRange]). ' ' .localize($timeRange). ' ';
        }
        
        $output = trim($output);
        
        if (!$output)
            $output = localize('a moment');
    
        return $output;
    }
}

/**
  * Show elapsed time in human-friendly format
  *
  * @param string|int $time
  * @return string 
  * @author Damian Kęska
  */

function elapsedTime($time)
{
    return date_calc_diff(time(), $time);
}

/**
 * Filter input removing tags, quotes etc.
 *
 * @param string $input Input string
 * @param string $filtersList Separated by comma eg. quotehtml,quotes,wysiwyg
 * @return bool
 * @author Damian Kęska
 */

function filterInput($input, $filtersList)
{
    $filters = explode(',', $filtersList);
    
    if (in_array('wysiwyg', $filters))
        $input = str_replace("\n", '\n', str_replace("\r", '\r', htmlspecialchars($input, ENT_QUOTES)));

    if(in_array('quotehtml', $filters))
        $input = htmlspecialchars($input);
        
    if (in_array('strip', $filters))
        $input = strip_tags($input);

    if (in_array('quotes', $filters))
    {
        $input = str_replace('"', '', $input);
        $input = str_replace("'", '', $input);
    }

    return $input;
}

/**
  * Description of a function
  *
  * @config hashing_algorithm
  * @config salt
  * @param string $password to encode
  * @return string with hash
  * @author Damian Kęska
  */

function encodePassword($password)
{
    $panthera = pantheraCore::getInstance();

    $salted = $panthera->config->getKey('salt').$password;

    if ($panthera->hashingAlgorithm == 'blowfish')
        return password_hash($salted, PASSWORD_BCRYPT);
    elseif ($panthera->hashingAlgorithm == 'sha512')
        return hash('sha512', $salted);
    else
        return md5($salted);
}

/**
  * Verify if password matches selected hash
  *
  * @config hashing_algorithm
  * @config salt
  * @param string $password to verify
  * @param string $hash previously encoded password to verify with $password
  * @return bool
  * @author Damian Kęska
  */

function verifyPassword($password, $hash)
{
    $panthera = pantheraCore::getInstance();

    $salted = $panthera->config->getKey('salt').$password;

    if ($panthera->hashingAlgorithm == 'blowfish')
        return password_verify($salted, $hash);
    elseif ($panthera->hashingAlgorithm == 'sha512')
        return ( $hash === hash('sha512', $salted) );
    else
        return ( $hash === md5($salted) );
}

/**
  * Get query string form GET/POST or other array, supports exceptions (some arguments can be skipped)
  *
  * @param array|string $array Array of elements, or a string value "GET" or "POST"
  * @param array|string $mix Elements to add (useful if using "GET" or "POST" in first but want to add something) eg. "aaa=test&bbb=ccc" or array('aaa' => 'test', 'bbb' => 'ccc')
  * @param array|string $except List of parameters to skip eg. "display,cat" or array('display', 'cat')
  * @return string 
  * @author Damian Kęska
  */

function getQueryString($array=null, $mix=null, $except=null)
{
    if ($array === null)
        $array = $_GET;
    elseif ($array == 'GET')
        $array = $_GET;
    elseif ($array == 'POST')
        $array = $_POST;
    elseif (is_string($array)) {
        parse_str($array, $array);
    }
        
    if ($mix != null) {
        if (is_string($mix)) {
            parse_str($mix, $mix);
        }
        
        if (is_array($mix)) {
            $array = array_merge($array, $mix);
        }
    }
        
    if ($except !== null)
    {
        if (!is_array($except))
        {
            $except = explode(',', $except);
        }
        
        foreach ($except as $exception)
        {
            unset($array[trim($exception)]);
        }
    }
    
    return http_build_query($array);
}

/**
  * Strip new lines
  *
  * @param string $string
  * @return string 
  * @author Damian Kęska
  */

function stripNewLines($str)
{
    return str_replace("\r", '\\r', str_replace("\n", '\\n', $str));
}

/**
  * Capture function stdout
  *
  * @param string|function $function
  * @package Panthera\pantheraCore
  * @author Damian Kęska
  */

function captureStdout($function, $a=null, $b=null, $c=null, $d=null, $e=null, $f=null)
{
    $panthera = pantheraCore::getInstance();
    
    // capture old output if any
    $before = $panthera -> outputControl -> get();
    $handler = $panthera -> outputControl -> isEnabled();
    $panthera -> outputControl -> clean();
    
    // start new buffering
    $panthera -> outputControl -> startBuffering();
    
    // executing function
    $return = $function($a, $b, $c, $d, $e, $f);
    $contents = $panthera -> outputControl -> get();

    $panthera -> outputControl -> clean();
    $panthera -> outputControl -> flushAndFinish();
    
    if ($handler === False)
    {
        $panthera -> outputControl -> flushAndFinish();
    } else {
        $panthera -> outputControl -> startBuffering($handler);
        print($before);
    }
    
    return array('return' => $return, 'output' => $contents);
}

/**
  * A base plugins class
  *
  * @package Panthera\pantheraCore
  * @author Damian Kęska
  */

class pantheraPlugin
{
    protected static $pluginInfo = array();
    
    public static function getPluginInfo()
    {
        return static::$pluginInfo;
    }
    
    public static function run() {}
}

/**
  * Create array of defined size, filled with null values (useful for creating for loop in RainTPL)
  *
  * @param int $range Count of iterations
  * @package Panthera\pantheraCore
  * @author Damian Kęska
  */

function forRange($range=0)
{
    $arr = array();
    
    for ($i=0; $i<$range; $i++)
        $arr[] = null;
    
    return $arr;
}
