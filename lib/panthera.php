<?php
/**
  * Panthera Framework main file
  * 
  * @package Panthera\core
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Exception handler
  *
  * @param object $exception
  * @return void 
  * @author Damian Kęska
  */
  
function pantheraExceptionHandler($exception)
{
    global $panthera;

    if ($panthera->config->getKey('debug'))
    {
        $panthera->logging->output('pantheraExceptionHandler::Unhandled exception, starts;');
        $panthera->logging->output($exception->getMessage());
        $panthera->logging->output($exception->getFile(). ' on line ' .$exception->getLine());

        $trace = $exception->getTrace();

        foreach ($trace as $key => $stackPoint) {
            // I'm converting arguments to their type
            // (prevents passwords from ever getting logged as anything other than 'string')
            $trace[$key]['args_content'] = json_encode($trace[$key]['args']);
            $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
            $trace[$key]['class'] = $stackPoint['class'];
        }

        $stackTrace = array();
        foreach ($trace as $key => $stackPoint) {
            $stackTrace[] = array('key' => $key, 'file' => $stackPoint['file'], 'line' => $stackPoint['line'], 'function' => $stackPoint['function'], 'args' => implode(', ', $stackPoint['args']), 'args_json' => $stackPoint['args_content'], 'class' => $stackPoint['class']);

            $function = $stackPoint['function'];

            if ($stackPoint['class'] != '')
                $function = $stackPoint['class']. ' -> ' .$stackPoint['function']. '(' .implode(', ', $stackPoint['args']). ')';

            $panthera->logging->output($key. ' => ' .$function. ' in ' .$stackPoint['file']. ' on line ' .$stackPoint['line']);
        }

        if (is_dir(SITE_DIR. '/content/templates/exception_debug.php'))
            include_once(SITE_DIR. '/content/templates/exception_debug.php');
        else
            include_once(PANTHERA_DIR. '/templates/exception_debug.php');

        $panthera->logging->toFile();
        die();
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
  * @author Damian Kęska
  */

function pantheraErrorHandler($errno=0, $errstr='unknown', $errfile='unknown', $errline='unknown')
{
    global $panthera;

    if (error_get_last() != NuLL)
    {

        $details = error_get_last();

        if ($errno == E_NOTICE)
            return True;

        if(strpos('PHP Startup', $errstr) != -1)
            return True;

        if ($panthera->config->getKey('debug'))
        {
            $panthera->logging->output('pantheraErrorHandler::Unexcepted error ' .json_encode($details));
            $panthera->logging->toFile();

            $stack = debug_backtrace( false );

            if (is_dir(SITE_DIR. '/content/templates/error_debug.php'))
                include_once(SITE_DIR. '/content/templates/error_debug.php');
            else
                include_once(PANTHERA_DIR. '/templates/error_debug.php');

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
    public $debug = False, $tofile = True, $printOutput = False;
    private $_output = "", $panthera;
    
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
        $this->panthera -> add_option('session_save', array($this, 'toFile'));
    }
    
    /**
      * Add a line to messages log
      *
      * @param string $msg Message
      * @param string $type Identifier for group of messages
      * @hook logging.output
      * @return bool 
      * @author Damian Kęska
      */

    public function output($msg, $type='')
    {
        if($this->debug == False)
            return False;
            
        if ($this->printOutput == True)
            print($msg. "\n");
            
        // plugins support eg. firebug
        $this->panthera -> get_options('logging.output', $msg);

        $this->_output .= $msg. "\n";

        return True;
    }
    
    /**
      * Get complete output
      *
      * @return string
      * @author Damian Kęska
      */

    public function getOutput()
    {
        if (MODE == 'CLI')
        {
            $defaults = "Client addr(".$_SERVER['SSH_CLIENT'].") => CLI ".$_SERVER['SCRIPT_NAME']."\n";
        } else
            $defaults = "Client addr(".$_SERVER['REMOTE_ADDR'].") => ".$_SERVER['REQUEST_METHOD']. " ".$_SERVER['REQUEST_URI']."\n";
        
        return $defaults.$this->_output;
    }
    
    /**
      * Save debug to file
      *
      * @return void 
      * @author Damian Kęska
      */

    public function toFile()
    {
        if ($this->tofile == True)
        {
            $fp = @fopen(SITE_DIR. '/content/tmp/debug.log', 'w');
            @fwrite($fp, $this->getOutput());
            @fclose($fp);
        }
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
    private $config, $panthera, $overlay = array(), $overlayChanged = False, $overlay_modified = array();

    public function __construct($panthera, $config)
    {
         // TODO: Implement an error handler when there is no $config defined
        $this->config = $config;
        $this->panthera = $panthera;
        #$this->_loadOverlay();

        // add option to save configuration on exit
        $panthera -> add_option('session_save', array($this, 'save'));
    }

    public function getKey($key, $default='__none', $type='__none')
    {
        if(array_key_exists($key, $this->config))
            return $this->config[$key];

        if(array_key_exists($key, $this->overlay))
            return $this->panthera->types->parse($this->overlay[$key][1], $this->overlay[$key][0]);

        // create new key with default value
        if (!array_key_exists($key, $this->overlay) and !array_key_exists($key, $this->config) and $default != '__none')
            $this->setKey($key, $default, $type);

        if($default == '__none')
            return Null;
        
        return $default;
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

    public function setKey($key, $value='__none', $type='__none')
    {
        if($key == NuLL and $value == '__none')
            return False;
            
        if (array_key_exists((string)$key, $this->overlay))
        {
            if ($this -> getKey($key) != $value)
                $this->overlayChanged = True;
            else
                return True;
        } else {
            $this->overlayChanged = True;
            $this->overlay[(string)$key] = array(0 => 'string'); // default type
        }

        if($type != '__none')
        {
            if ($this->panthera->types->exists($type))
            {
                $this->panthera->logging->output('pantheraConfig::setKey( ' .$key. ', ' .str_replace("\n", " ", print_r($value, True)). ', ' .$type. ' )');
                $this->overlay[(string)$key][0] = $type;
            }
        }

        if ($this->panthera->types->validate($value, $this->overlay[(string)$key][0]))
        {
            $this->overlay[(string)$key][1] = $value;
            $this->overlay_modified[(string)$key] = True;
            return True;
        }

        return False;
    }

    public function getKeyType($key)
    {
        if (array_key_exists($this->overlay, $key))
        {
            return $this->overlay[$key][0];
        } else
            return 'string';
    }

    // SQL Based Overlay
    public function loadOverlay()
    {
        $SQL = $this->panthera->db->query('SELECT `key`, `value`, `type` FROM `{$db_prefix}config_overlay`');

        if ($SQL -> rowCount() > 0)
        {
            $array = $SQL -> fetchAll();

            foreach ($array as $key => $value)
            {
                if ($value['type'] == 'array')
                    $value['value'] = @unserialize($value['value']);

                if ($value['type'] == 'json')
                    $value['value'] = @json_decode($value['value']);

                $this->overlay[$value['key']] = array($value['type'], $value['value']);
            }
        }

        if (!array_key_exists('debug', $this->overlay))
        {
            $this->overlay['debug'] = array('bool', False);
            $this->overlay_modified['debug'] = True;
        }
    }

    /*private function _loadOverlay()
    {
        if(is_file(PANTHERA_DIR. '/content/config-overlay.phpson'))
        {
            $c = file_get_contents(PANTHERA_DIR. '/content/config-overlay.phpson');

            if (empty($c))
                $array = array();
            else
                $array = @unserialize($c);

            if(!is_array($array))
                return False;

            //$this->config = array_merge($this->config, $array);
            $this->overlay = $array;
        } else {
            $this -> panthera -> logging -> output ('pantheraConfig::Creating new config-overlay.phpson');
            $fp = @fopen(PANTHERA_DIR. '/content/config-overlay.phpson', 'w');
            @fwrite($fp, serialize(array()));
            @fclose($fp);
        }
    }*/

    

    public function save()
    {
        if ($this -> overlayChanged == True)
        {
            $this->panthera->logging->output('pantheraConfig::Saving config overlay to SQL');

            $values = array();

            foreach ($this->overlay as $key => $value)
            {
                if (!array_key_exists($key, $this->overlay_modified))
                    continue;

                if ($value[0] == 'json' and is_array($value[1]))
                    $value[1] = json_encode($value[1]);
                else {
                    if(is_array($value[1]))
                        $value[1] = serialize($value[1]);
                }

                $this->panthera->logging->output('pantheraConfig::Update attempt of ' .$key. ' variable');
                $q = $this->panthera->db->query('UPDATE `{$db_prefix}config_overlay` SET `value` = :value, `type` = :type WHERE `key` = :key ', array('value' => $value[1], 'key' => $key, 'type' => $value[0]));

                if ($q -> rowCount() == 0)
                {
                    $this->panthera->logging->output('pantheraConfig::Inserting ' .$key. ' variable (' .$value[0]. ')');

                    try {
                        $q = $this->panthera->db->query('INSERT INTO `{$db_prefix}config_overlay` (`id`, `key`, `value`, `type`) VALUES (NULL, :key, :value, :type);', array('key' => $key, 'value' => $value[1], 'type' => $value[0]));
                    } catch (Exception $e) { 
                        $this->panthera->logging->output('Cannot insert new key, SQL error: ' .print_r($e->getMessage()), 'pantheraConfig');
                    }
                }
            }
            
            // doing a multiple query
            //$this->panthera->db->query('UPDATE `{$db_prefix}config_overlay` SET `value` = :value WHERE `key` = :key;', $values, True);
            /*$this -> panthera -> logging -> output ('pantheraConfig::Saving config-overlay.phpson');
            $fp = @fopen(PANTHERA_DIR. '/content/config-overlay.phpson', 'w');
            @fwrite($fp, serialize($this->overlay));
            @fclose($fp);*/
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getOverlay()
    {
        return $this->overlay;
    }
}

// here will be our plugin system etc.
class pantheraCore
{
    private $hooks = array(), $plugins, $_savedSession, $permissionsTable = array(), $modules = array();
    public $config, $db, $user, $template, $session, $pluginsDir, $varCache=False, $cache=False;
    // public $qSerialize = 'serialize';

    // exit right after all plugins are loaded
    public $quitAfterPlugins = False;

    /**
	 * Panthera core class constructor
	 *
	 * @return void
	 * @author Damian Kęska
	 */

    public function __construct($config) {

        if (!array_key_exists('SITE_DIR', $config))
        {
            // try to find site root directory
            if (!defined('SITE_DIR'))
            {
                if (MODE == 'CLI')
                    $path = $_SERVER['PWD'];
                else {
                    $pathInfo = pathinfo($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
                    $path = $pathInfo['dirname'];
                }
                
                // if the main script is not executing in main directory but in any other directory on higher level in the tree eg. /pages/make_thumbnail.php (level = 1), /other/scripts/show_users.php (level = 2)
                // so, the script should look back in parent directories and check if content/config.php exists at this level
                if (!is_file($path. '/content/app.php'))
                {
                    $deep = 0;
                    while (True)
                    {
                        $deep++;

                        $path = dirname($path); // going to parent directory eg. /test/other-dir/ => /test/

                        if (is_file($path. '/content/app.php'))
                            break;

                        if ($deep == 8)
                            break;
                    }
                }    

                define('SITE_DIR', $path);
            }
        } else
            define('SITE_DIR', $config['SITE_DIR']); // get SITE_DIR from configuration if avaliable
        
        if (!is_file(SITE_DIR. '/content/app.php'))
            throw new Exception('Cannot find /content/app.php, looking in SITE_DIR=' .SITE_DIR);
            
        // best performance provides binary serializing
//        if (function_exists('igbinary_serialize'))
//            $this->qSerialize = 'binary';

        $this->types = new pantheraTypes($this); // data types
        $this->logging = new pantheraLogging($this);
        $this->config = new pantheraConfig($this, $config);    
        $this->db = new pantheraDB($this);  
        $this->config->loadOverlay();
        
        /** CACHE SYSTEM **/
        
        if (!defined('SKIP_CACHE'))
        {
            // load variable cache system
            $varCacheType = $this->config->getKey('varcache_type', 'db', 'string');
            $cacheType = $this->config->getKey('cache_type', '', 'string');
            
            // primary cache (variables cache)
            if (class_exists('varCache_' .$varCacheType))
            {
                try {
                    $n = 'varCache_' .$varCacheType;
                    $this->varCache = new $n($this);
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
                
                    // load secondary cache
                    if (class_exists('varCache_' .$cacheType))
                    {
                        try {
                            $n = 'varCache_' .$cacheType;
                            $this->cache = new $n($this);
                        } catch (Exception $e) {
                            $this->logging->output('Disabling cache due to exception: ' .$e->getMessage(), 'pantheraCore');
                            $this->cache = false;
                        }
                    }
                
                }
            }
        }
        /** END OF CACHE SYSTEM **/
        
        if (class_exists('pantheraLocale'))
            $this->locale = new pantheraLocale($this);
            
        if (class_exists('pantheraSession'))
            $this->session = new pantheraSession($this);

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

        $this->pluginsDir = array(PANTHERA_DIR. '/plugins', SITE_DIR. '/content/plugins');
    }

    /* ==== MODULES ==== */

    /**
	 * Import module
	 *
     * @param module name
	 * @return void
	 * @author Damian Kęska
	 */

    public function importModule($module)
    {
        $module = strtolower($module);

        if ($this->moduleImported($module))
            return True;

        // load built-in phpQuery library
        if ($module == 'phpquery')
        {
            include_once(PANTHERA_DIR. '/share/phpQuery.php');
            $this->modules[$module] = True;
        }

        if(is_file(PANTHERA_DIR. '/modules/' .$module. '.module.php'))
        {
            $this->logging->output('pantheraCore::Importing "' .$module. '" from /lib/modules');
            include_once(PANTHERA_DIR. '/modules/' .$module. '.module.php');

            $this->modules[$module] = True;
        } elseif (is_file(SITE_DIR. '/content/modules/' .$module. '.module.php')) {
            $this->logging->output('pantheraCore::Importing "' .$module. '" from /content/modules');
            include_once(SITE_DIR. '/content/modules/' .$module. '.module.php');

            $this->modules[$module] = True;
        } else {
            $this->logging->output('pantheraCore::Cannot import "' .$module. '" module');
        }
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
        return array_key_exists($module, $this->modules);
    }


    /* ==== PERMISSIONS TABLE ==== */

    public function addPermission($name, $description, $plugin='')
    {
        $this->permissionsTable[$name] = array('desc' => $description, 'plugin' => $plugin);

        return True;
    }

    public function removePermission($name)
    {
        unset($this->permissionsTable[$name]);
        return True;
    }

    public function getPermission($name)
    {
        if (array_has_key($name, $this->permissionsTable))
            return $this->permissionsTable[$name];
    }

    public function listPermissions()
    {
        return $this->permissionsTable;
    }

    /* ==== PLUGINS CONTEXT ==== */

    /*public function get_context($plugin)
    {
        $u = $this->config->getKey('pluginsContext');

        if (@array_key_exists($plugin, $u))
            return $u[$plugin];

        return False;
    }

    public function append_context($plugin, $file)
    {
        $context = $this->config->getKey('pluginsContext');

        $old = @in_array($file, @$context[$plugin]);

        if(!array_key_exists($plugin, $context))
        {
            $this -> logging -> output ('panthera::Creating new context table for ' .$plugin);
            $context[$plugin] = array($file);
        } else {
            $this -> logging -> output ('panthera::Adding '.$file. ' to ' .$plugin. ' context');
            $context[$plugin][] = $file;        
        }

        if(!$old)
            $this->config->setKey('pluginsContext', $context);            

        return True;
    }

    public function remove_context($plugin, $file)
    {
        $context = $this->config->getKey('pluginsContext');
        $search = array_search($context[$plugin], $file);

        if(in_array($context[$plugin], $file))
        {
            unset($context[$plugin][$search]);
            $this->config->setKey('pluginsContext', $context);
            return True;
        }
    }*/
    
    /**
      * Return cache type
      *
      * @param string $cacheType Cache type can be cache or varCache
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function cacheType($cache)
    {
        if ($cache == 'cache')
        {
            if ($this->cache != False)
                return $this->cache->type;
        } else {
            if ($this->varCache != False )
                return $this->varCache->type;
        }
    }


    /* ==== HOOKING FUNCTIONS ==== */
    public function getAllHooks()
    {
        return $this->hooks;
    }

    public function add_option($hookName, $function)
    {
        // create array with hooks group
        if(!array_key_exists($hookName, $this->hooks))
        {
            $this->hooks[$hookName] = array();
        }

        // is this a class method or just a function?
        if(gettype($function) == "array") // here is situation when it will be a class
        {
            if(count($function) != 2)
            {
                $this->logging->output("panthera::Invalid function array specified to add_option, requires to be first argument a class type and second a function name of that class");
                return False; 
            }

            if (!is_object($function[0]) and !class_exists($function[0]))
            {
                $this->logging->output("panthera::add_option::Class '".$function[0]."' does not exists");
                return False; 
            }

            if (!method_exists($function[0], $function[1]))
            {
                $this->logging->output("panthera::add_option::Method '".$function[1]."' of '".$function[0]."' class does not exists");
                return False; 
            }

        } else { // and here is just a simple function
            if(!function_exists($function))
            {
                $this->logging->output("panthera::Hooked function ".$function." does not exists.");
                return False;
            }        
        }
        
        $this->hooks[$hookName][] = $function;
    }

    public function get_options($hookName, $args='')
    {
        if(!array_key_exists($hookName, $this->hooks))
            return False;

        foreach ($this->hooks[$hookName] as $key => $hook)
        {
            if (gettype($hook) == "array")
            {
                if(!is_object($hook[0]) and !class_exists($hook[0]))
                    continue;

                if (!method_exists($hook[0], $hook[1]))
                    continue;      

                if (is_object($hook[0]))
                    $hook[0]->$hook[1]($args);
                else
                    $hook[0]::$hook[1]($args);
                    
            } else {
                if (!function_exists($hook))
                    continue;

                $hook($args);
            }
        }

        return False;
    }

    public function get_filters($hookName, $args='')
    {
        if(!array_key_exists($hookName, $this->hooks))
            return $args;
            
        foreach ($this->hooks[$hookName] as $key => $hook)
        {
            if (gettype($hook) == "array")
            {
                if(!is_object($hook[0]) and !class_exists($hook[0]))
                    continue;

                if (!method_exists($hook[0], $hook[1]))
                    continue;                

                if (is_object($hook[0]))
                    $args = $hook[0]->$hook[1]($args);
                else
                    $args = $hook[0]::$hook[1]($args);
                    
            } else {
                if (!function_exists($hook))
                    continue;

                $args = $hook($args);
            }
        }

        return $args;
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

                if (array_key_exists($file, $configPlugins))
                {
                    if ($configPlugins[$file] == True)
                        $enabled = True;
                }
                
                $files[$file] = array('include_path' => $dir. '/' .$file, 'enabled' => $enabled, 'info' => $this->plugins[$file]);
            }
        }

        return $files;
    }
    
    /**
      * Check plugin's PHP syntax (if there is access to shell)
      * Returns True if test passed or if there is no access to shell to make a test, and returns string with error if not passed
      *
      * @param string $plugin Plugin's directory name
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function checkPluginSyntax($plugin)
    {
        if ($this->pluginExists($plugin))
        {
            $this->importModule('filesystem');
            $plugins = $this->getPlugins();
            $dir = scandirDeeply($plugins[$plugin]['include_path']);
            
            try {
                foreach ($dir as $file)
                {
                    $pathinfo = pathinfo($file);
                    
                    if ($pathinfo['extension'] == 'php')
                    {
                        $test = shell_exec('php -l ' .$file);
                        
                        if (!stristr($test, 'No syntax errors detected'))
                        {
                            return $test;
                        }
                    }
                }
            } catch (Exception $e) {
                return True; // we dont have rights to use shell commands, so the test must return True
            }
        }

        return True;
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
        global $panthera, $user, $template;

        if ($pluginsDir == '')
            $pluginsDir = array(PANTHERA_DIR. '/plugins', SITE_DIR. '/content/plugins');

        /*if(!is_dir($pluginsDir))
        {
            $this->logging->output('Cannot find plugins directory "'.$pluginsDir.'"!', 'pantheraCore');
            return False;
        }*/

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
        $c = str_replace(PANTHERA_WEBROOT, '{$root}', $_SERVER['SCRIPT_NAME']);

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

            /*$context = $this->get_context($value);

            if ($context != False)
            {
                if (!in_array($c, $context))
                {
                    $this -> logging -> output('panthera::Plugin '.$value.' skipped because of context mismatch');
                    continue;
                }
            } else
                $this -> logging -> output('panthera::No context for plugin '.$value);*/
                
            $exp = explode('.', $value);
            
            if ($exp[1] == 'cgi' and PANTHERA_MODE != "CGI")
            {
                $this -> logging -> output('Skipping loading of "' .$value. '" plugin in ' .PANTHERA_MODE. ' mode', 'pantheraCore');
                continue;
            }

            // check if main file exists in pluin directory
            if(is_file($value."/plugin.php"))
            {
                $pluginInfo = array();

                include($value."/plugin.php");

                if (count($pluginInfo) > 0)
                {
                    $this->registerPlugin($pluginInfo['name'], $value."/plugin.php", $pluginInfo);
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
        $this->get_options('session_save');
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
    protected $panthera;

    public function __construct()
    {
        global $panthera;
        $this->panthera = $panthera;
    }
}

/**
 * Panthera data validation class. Strings, numbers, urls, ip adresses and other data can be validated here.
 *
 * @package Panthera\core
 * @author Damian Kęska
 */

class pantheraTypes extends pantheraClass
{
    // 1 means built-in type
    private $types = array('int' => 1, 'email' => 1, 'bool' => 1, 'ip' => 1, 'regexp' => 1, 'url' => 1, 'json' => 1, 'array' => 1, 'string' => 1, 'phone' => 1, 'pesel' => 1, 'nip' => 1, 'nrb' => 1, 'regon' => 1);

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
    
    /**
      * Polish PESEL validation
      *
      * @param string $str Number
      * @return bool 
      * @author PHPedia.pl <http://phpedia.pl/wiki/Walidacja_numeru_PESEL>
      */
    
    function _pesel($str)
    {
	    if (!preg_match('/^[0-9]{11}$/',$str)) //sprawdzamy czy ciąg ma 11 cyfr
		    return False;
     
	    $arrSteps = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3); // tablica z odpowiednimi wagami
	    $intSum = 0;
	    
	    for ($i = 0; $i < 10; $i++)
		    $intSum += $arrSteps[$i] * $str[$i]; //mnożymy każdy ze znaków przez wagć i sumujemy wszystko
	    
	    $int = 10 - $intSum % 10; //obliczamy sumę kontrolną
	    $intControlNr = ($int == 10)?0:$int;
	    
	    if ($intControlNr == $str[10]) //sprawdzamy czy taka sama suma kontrolna jest w ciągu
		    return True;
		    
	    return False;
    } 
    
     /**
      * Polish NIP validation
      *
      * @param string $str Number
      * @return bool 
      * @author PHPedia.pl <http://phpedia.pl/wiki/Walidacja_numeru_NIP>
      */
    
    function _nip($str)
    {
	    $str = preg_replace("/[^0-9]+/","",$str);
	    
	    if (strlen($str) != 10)
		    return false;
     
	    $arrSteps = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
	    $intSum=0;
	    
	    for ($i = 0; $i < 9; $i++)
		    $intSum += $arrSteps[$i] * $str[$i];
		    
	    $int = $intSum % 11;
     
	    $intControlNr=($int == 10)?0:$int;
	    if ($intControlNr == $str[9])
		    return true;
		    
	    return false;
    }
    
    /**
      * NRB validation
      *
      * @param string $p_iNRB Number
      * @return bool 
      * @author PHPedia.pl <http://phpedia.pl/wiki/Walidacja_numeru_NRB>
      */
    
    function _nrb($p_iNRB)
    {
      // Usuniecie spacji
      $iNRB = str_replace(' ', '', $p_iNRB);
      // Sprawdzenie czy przekazany numer zawiera 26 znaków
      if(strlen($iNRB) != 26)
        return false;
     
      // Zdefiniowanie tablicy z wagami poszczególnych cyfr				
      $aWagiCyfr = array(1, 10, 3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38, 89, 17, 73, 51, 25, 56, 75, 71, 31, 19, 93, 57);
     
      // Dodanie kodu kraju (w tym przypadku dodajemy kod PL)		
      $iNRB = $iNRB.'2521';
      $iNRB = substr($iNRB, 2).substr($iNRB, 0, 2); 
     
      // Wyzerowanie zmiennej
      $iSumaCyfr = 0;
     
      // Pętla obliczająca sumć cyfr w numerze konta
      for($i = 0; $i < 30; $i++) 
        $iSumaCyfr += $iNRB[29-$i] * $aWagiCyfr[$i];
     
      // Sprawdzenie czy modulo z sumy wag poszczegolnych cyfr jest rowne 1
      return ($iSumaCyfr % 97 == 1);
    }
    
    /**
      * Polish regon validation
      *
      * @param string $str Number
      * @return bool 
      * @author PHPedia.pl <http://phpedia.pl/wiki/Walidacja_numeru_REGON>
      */
    
    function _regon($str)
    {
	    if (strlen($str) != 9)
		    return False;
     
	    $arrSteps = array(8, 9, 2, 3, 4, 5, 6, 7);
	    $intSum=0;
	    
	    for ($i = 0; $i < 8; $i++)
		    $intSum += $arrSteps[$i] * $str[$i];
		    
	    $int = $intSum % 11;
	    $intControlNr=($int == 10)?0:$int;
	    
	    if ($intControlNr == $str[8]) 
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
        $this->__changed = True;
        $this->__data[$var] = $value;
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
 * @return string
 * @author Damian Kęska
 */

function ajax_exit($array)
{
    global $panthera;
    
    if ($panthera -> logging -> debug == True)
        $panthera -> logging -> output('ajax_exit: ' .json_encode($array), 'pantheraCore');
    
    print(json_encode($array));
    pa_exit('', True);
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
    global $panthera;
    
    if ($ajaxExit == False and PANTHERA_MODE == "CGI")
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $pathinfo = pathinfo($url['path']);
        navigation::appendHistory($pathinfo['filename']. '?' .$url['query']);
        navigation::save();
    }

    // just to be sure in logs
    $panthera -> logging -> output('Called pa_exit, goodbye.', 'pantheraCore');

    // execute all hooks to save data
    $panthera -> get_options('page_load_ends');
    $panthera->finish();

    die($string);
}

/**
 * Make simple redirection using "Location" header and exit application
 *
 * @param string $url Application internal url, eg. index.php
 * @return string
 * @author Damian Kęska
 */

function pa_redirect($url)
{
    global $panthera;
    header('Location: '.$panthera->config->getKey('url'). '/' .$url);
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
  * Universal pager for all purporses
  *
  * @author Damian Kęska
  */

class Pager
{
    public $max, $perPage, $pages, $maxLinks=4;

    /**
	 * Constructor
	 *
     * @param int $max Count of all avaliable elements
     * @param int $perPage How many elements show on one page
	 * @return void
	 * @author Damian Kęska
	 */

    public function __construct($max, $perPage)
    {
        $this->max = $max;
        $this->perPage = intval($perPage);

        if (gettype($this->max) == "array")
            $this->max = 5;

        if (gettype($this->perPage) == "array")
            $this->perPage = 5;

        $this->pages = ceil(($this->max / $this->perPage));
    }

    /**
	 * Get limit for SQL query eg. array(10, 5) => LIMIT 5,10. Returns False or array.
	 *
     * @param int $page Number of page we want to get limit for
	 * @return array|bool
	 * @author Damian Kęska
	 */

    public function getPageLimit($page)
    {
        if ($page <= $this->pages)
        {
            return array(($page * $this->perPage), $this->perPage);
        }

        return False;
    }

    /**
	 * Get array with all pages, this array can be passed to template manager to display links or buttons
	 *
     * @param int $currentPage Current page we are on
	 * @return array|bool
	 * @author Damian Kęska
	 */

    public function getPages($currentPage)
    {
        $m = (($this->maxLinks/2)-1); // max links in left direction
        $left = ($currentPage-$m);

        if ($left < 0)
            $left = 0;

        $pages = array();
        
        for ($i=$left; $i<$currentPage; $i++)
        {
            $pages[(string)$i] = False;
        }

        $right = ($currentPage+$m+1);

        if (count($pages) < $m)
            $right += ($m-count($pages));

        $pages[(string)$currentPage] = True;
        
        if ($right > $this->pages)
            $right = $this->pages;

        for ($i=$currentPage+1; $i<$right; $i++)
        {
            $pages[(string)$i] = False;
        }

        return $pages;
    }
}

/**
 * Filter input removing tags, quotes etc.
 *
 * @param string $input Input string
 * @param string $filtersList Separated by comma eg. quotehtml,quotes
 * @return bool
 * @author Damian Kęska
 */

function filterInput($input, $filtersList)
{
    $filters = explode(',', $filtersList);

    if(in_array('quotehtml', $filters))
        $input = htmlspecialchars($input);

    if (in_array('quotes', $filters))
    {
        $input = str_replace('"', '', $input);
        $input = str_replace("'", '', $input);
    }

    return $input;
}

/**
 * Convert Panthera special variables in urls with reverse function
 *
 * @param string $url URL to be parsed
 * @param bool $reverse Set to true if you want to convert complete URL back to Panthera internal url eg. input: http://example.com/index output: {$PANTHERA_URL}/index
 * @return string
 * @author Damian Kęska
 */

function pantheraUrl($url, $reverse=False) 
{
    global $panthera;

    $var = array('{$AJAX_URL}' => $panthera->config->getKey('ajax_url'), '{$PANTHERA_DIR}' => PANTHERA_DIR, '{$SITE_DIR}' => SITE_DIR, '{$PANTHERA_URL}' => $panthera->config->getKey('url'), '{$upload_dir}' => $panthera->config->getKey('upload_dir'));
    
    if (!defined('SKIP_LOCALE'))
        $var['{$language}'] = $panthera->locale->getActive();

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
    global $panthera;
    return $panthera->logging->debug;
}

/**
 * Sort multidimensional array by value inside of array
 *
 * @param array $array Input array
 * @param string $key Key in array to sort by
 * @return void
 * @author Lohoris <http://stackoverflow.com/questions/2699086/sort-multidimensional-array-by-value-2>
 */

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
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
  * Closes all unclosed tags
  *
  * @param string $string HTML code
  * @return string
  * @author Damian Kęska
  */

function closeHTMLTags($html)
{
    if (class_exists('DOMDocument'))
    {
        $doc = new DOMDocument();
        $doc -> loadHTML('<?xml encoding="UTF-8">' .$html);
        return $doc->saveHTML();
    } else
        return __HTML__closetags($html);
}

/**
 * close all open xhtml tags at the end of the string (use closeHTMLTags() instead of this function)
 *
 * @param string $html
 * @return string
 * @author Milian Wolff <mail@milianw.de>
 */

function __HTML__closetags($html) {
      #put all opened tags into an array
      preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
      $openedtags = $result[1];
     
      #put all closed tags into an array
      preg_match_all('#</([a-z]+)>#iU', $html, $result);
      $closedtags = $result[1];
      $len_opened = count($openedtags);
      # all tags are closed
      if (count($closedtags) == $len_opened) {
        return $html;
      }
      $openedtags = array_reverse($openedtags);
      # close tags
      for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)){
          $html .= '</'.$openedtags[$i].'>';
        } else {
          unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
      }
      return $html;
}

/**
  * Reset array keys (example of input: 5 => 'first', 6 => 'second', example of output: 0 => 'first', 1 => 'second')
  *
  * @param array $array Input array
  * @return array
  * @author Damian Kęska
  */

function array_reset_keys($array)
{
    $newArray = array();

    foreach ($array as $value)
        $newArray[] = $value;

    return $newArray;
}

/**
  * Check if given string is an URL adress (if you want precise check use $panthera->types instead)
  *
  * @param string $url
  * @return bool
  * @author Damian Kęska
  */

if (!function_exists('is_url'))
{
    function is_url($url)
    {
        $url = strtolower($url);

        if (substr($url, 0, 7) == 'http://' or substr($url, 0, 3) == "www" or substr($url, 0, 8) == "https://")
            return True;

        return False;
    }
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
    if (file_exists(SITE_DIR.$dir))
        return SITE_DIR.$dir;
        
    if (file_exists(SITE_DIR. '/content/'.$dir))
        return SITE_DIR. '/content/'.$dir;

    if (file_exists(PANTHERA_DIR.$dir))
        return PANTHERA_DIR.$dir;
}

/**
  * Create an empty directory with unique name in /content/tmp/ dir
  *
  * @return string 
  * @author Damian Kęska
  */

function maketmp()
{
    global $panthera;

    $seed = $panthera->config->getKey('session_key'). '_' .substr(md5(rand(999999,99999999)), 0, 6);

    // generate unique dir    
    while (is_dir(SITE_DIR. '/content/tmp/_' .$seed))
        $seed = $panthera->config->getKey('session_key'). '_' .substr(md5(rand(999999,99999999)), 0, 6);
        
    @mkdir(SITE_DIR. '/content/tmp/_' .$seed);
    
    return SITE_DIR. '/content/tmp/_' .$seed;
}

/**
  * Print object informations
  *
  * @param object $obj Input object
  * @debug
  * @return void 
  * @author Damian Kęska
  */

function object_dump($obj)
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
                   
   var_dump($data);
}

/**
  * Data serialization using method with best performance
  *
  * @param mixed $data
  * @return string 
  * @author Damian Kęska
  */

/*function quickSerialize($data)
{
    global $panthera;
    
    if ($panthera->qSerialize == 'binary')
        return igbinary_serialize($data);
    else    
        return serialize($data);
}*/

/**
  * Data unserialization using method with best performance
  *
  * @param mixed $data
  * @return string 
  * @author Damian Kęska
  */

/*function quickUnserialize($data)
{
    global $panthera;
    
    if ($panthera->qSerialize == 'binary')
        return igbinary_unserialize($data);
    else    
        return unserialize($data);
}*/
?>
