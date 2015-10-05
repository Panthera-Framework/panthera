<?php
namespace Panthera;
require __DIR__. '/BaseExceptions.php';

/**
 * Abstract Panthera class with Panthera object stored in $this->app
 *
 * @package Panthera\modules\core
 * @author Damian Kęska
 */
abstract class BaseFrameworkClass
{
    /**
     * @var framework $app
     */
    protected $app = null;

    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * Get self Singleton instance
     *
     * @return static|BaseFrameworkClass
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$instances[get_called_class()]))
        {
            new $class;
        }

        return self::$instances[$class];
    }

    /**
     * Constructor
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct()
    {
        $this->app = framework::getInstance();
        self::$instances[get_called_class()] = $this;
    }

    /**
     * Execute hooks and defined functions with name $featureName
     *
     * Example:
     *  $featureName = 'custompages.add' will execute $this->custompages_addFeature($args, $additionalInfo) and $this->app->execute($featureName, $args, $additionalInfo)
     *
     * @param string $featureName Hook and function name
     * @param mixed|null $args Args to pass to function and/or hook
     * @param mixed $additionalInfo Additional informations
     *
     * @return $args Mixed arguments
     */
    public function getFeature($featureName, $args = null, $additionalInfo = null)
    {
        $functionName = preg_replace('/[^\da-zA-Z0-9]/i', '_', $featureName). 'Feature';

        $this->app->logging->output('Looking for this->' .$functionName. '(args, additionalInfo)', get_called_class());

        if (method_exists($this, $functionName))
            $args = $this->$functionName($args, $additionalInfo);

        return $this->app->signals->execute($featureName, $args);
    }

    /**
     * Don't allow Panthera and PDO objects to gets serialized
     *
     * @magic
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return array
     */
    public function __sleep()
    {
        $reflection = new \ReflectionClass(get_called_class());
        $properties = array();

        foreach ($reflection->getProperties() as $property)
        {
            if ($property->getName() == 'app' || $property->isStatic())
                continue;

            $properties[] = $property->getName();
        }

        return $properties;
    }

    /**
     * Restore Panthera instance after unserializing
     *
     * @magic
     * @author Damian Kęska <webnull.www@gmail.com>
     */
    public function __wakeup()
    {
        $this->app = framework::getInstance();
    }
}

/**
 * Class autoloader for Panthera Framework
 *
 * @package Panthera
 * @param string $class name
 *
 * @author Damian Kęska <webnull.www@gmail.com>
 * @return mixed
 */
function __pantheraAutoloader($class)
{
    $app = framework::getInstance();

    // use cache list of classes
    if ($app->applicationIndex)
    {
        if (isset($app->applicationIndex['autoloader']['\\' .$class]))
        {
            require str_replace('$LIB$', PANTHERA_FRAMEWORK_PATH, str_replace('$APP$', $app->appPath, $app->applicationIndex['autoloader']['\\' .$class]));
            return true;
        }
    }

    // check if namespace belongs to Panthera Framework
    if (strpos($class, 'Panthera\\') === false)
    {
        return false;
    }

    // namespace support (eg. Panthera\cli\application would be mapped to /modules/cli/application.class.php)
    if (strpos($class, '\\') !== false)
    {
        $exp = explode('\\', $class);

        if (count($exp) > 2)
        {
            unset($exp[0]);
            $class = implode('/', $exp);

        } else {
            $class = substr($class, (strpos($class, '\\') + 1), strlen($class));
        }
    }

    $path = $app->getPath('/modules/' .$class. '.class.php');

    if ($path)
    {
        require $path;
    }
}

/**
 * Panthera Framework 2 Core Library
 *
 * @package Panthera\modules\core
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class framework
{
    /**
     * @var \Panthera\template $template
     */
    public $template = null;

    /**
     * @var \Panthera\locale $locale
     */
    public $locale = null;

    /**
     * @var \Panthera\logging $logging
     */
    public $logging = null;

    /**
     * @var \Panthera\Signals $signals
     */
    public $signals = null;

    /**
     * @var \Panthera\database\driver|\panthera\database\SQLite3DatabaseHandler $database
     */
    public $database = null;

    /**
     * @var \Panthera\cache\cache $cache
     */
    public $cache = null;

    /**
     * @var \Panthera\configuration $config
     */
    public $config = null;

    /**
     * @var $instance null
     */
    public static $instance = null;

    /**
     * Absolute path to application root directory
     *
     * @var string $appPath
     */
    public $appPath = '';

    /**
     * Absolute path to lib root directory
     *
     * @var string $libPath
     */
    public $libPath = '';

    /**
     * Framework path
     *
     * @var string $frameworkPath
     */
    public $frameworkPath = '';

    /**
     * Are we in debugging mode?
     *
     * @var bool $isDebugging
     */
    public $isDebugging = false;

    /**
     * List of all indexed elements eg. paths to directories that contains translation files, list of controllers
     *
     * @var array $applicationIndex
     */
    public $applicationIndex = array(

    );

    /**
     * Constructor
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function __construct()
    {
        // dummy
    }

    /**
     * Pre-builds all base objects
     *
     * @param string $controllerPath Path to controller that constructed this method
     * @param array $configuration Default configuration
     *
     * @throws InvalidConfigurationException
     * @throws PantheraFrameworkException
     */
    public function setup($controllerPath, $configuration = array())
    {
        // load composer's autoloader
        if (is_file(PANTHERA_FRAMEWORK_PATH . '/vendor/autoload.php'))
        {
            require_once PANTHERA_FRAMEWORK_PATH . '/vendor/autoload.php';
        }

        $this->appPath = pathinfo($controllerPath, PATHINFO_DIRNAME). '/';
        $this->libPath = realpath(__DIR__. '/../');
        $this->frameworkPath = realpath(__DIR__. '/../');

        // load application indexing cache
        $this->loadApplicationIndex();

        $this->signals  = new Signals;
        $this->config   = new configuration($configuration);
        $this->logging  = new logging;
        $this->cache    = cache\cache::getInstance();
        $this->database = database\driver::getInstance();
        $this->locale   = new locale;

        if (strtolower(PHP_SAPI) != 'cli')
        {
            $this->template = new template;
        }
        //$this->routing  = new \Panthera\routing;
    }

    /**
     * Load application index cache
     *
     * Application index cache contains lists of all collected useful items like paths where translation files found
     * or list of controllers in installed packages
     *
     * @throws PantheraFrameworkException
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return bool
     */
    public function loadApplicationIndex()
    {
        if (is_file($this->appPath. '/.content/cache/applicationIndex.php'))
        {
            require $this->appPath. '/.content/cache/applicationIndex.php';

            if (!isset($appIndex) || !is_array($appIndex))
            {
                throw new PantheraFrameworkException('Missing variable $appIndex or it\'s not an array in file "' .$this->appPath. '/.content/cache/applicationIndex.php"', 'FW_APP_INDEX_FILE_NOT_FOUND');
            }

            $this->applicationIndex = $appIndex;
            return true;
        }

        throw new PantheraFrameworkException('Application index cache not found, it should be updated automatically as a periodic or real time job, please investigate why cache regeneration is not running up, a file should be created at "' .$this->appPath. '/.content/cache/applicationIndex.php"', 'FW_APPLICATION_INDEX_NOT_FOUND');
    }

    /**
     * Get framework's instance
     *
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return null|framework
     */
    public static function getInstance()
    {
        if (!self::$instance)
        {
            self::$instance = new framework;
        }

        return self::$instance;
    }

    /**
     * Determine if run a shell application or not (depends on if we are including the class or running it from shell directly)
     */
    public static function runShellApplication($appName)
    {
        $appName = '\\Panthera\\cli\\' .$appName. 'Application';

        if (isset($_SERVER['argv']) && $_SERVER['argv'][0] && !defined('PHPUNIT'))
        {
            $reflection = new \ReflectionClass($appName);

            if (realpath($reflection->getFileName()) == realpath($_SERVER['argv'][0]))
            {
                $app = new $appName;

                if (method_exists($app, 'execute'))
                {
                    $app->execute();
                }

                return $app;
            }
        }
    }

    /**
     * Returns an absolute path to a resource
     *
     * @param string $path Relative path to resource
     * @param bool $packages Lookup packages too
     *
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return string|null
     */
    public function getPath($path, $packages = true)
    {
        if (!$this->applicationIndex)
        {
            throw new PantheraFrameworkException('Application index cache not found, it should be updated automatically as a periodic or real time job, please investigate why cache regeneration is not running up', 'FW_APPLICATION_INDEX_NOT_FOUND');
        }

        if (file_exists($this->appPath . '/' . $path))
        {
            return $this->appPath . '/' . $path;

        } elseif (file_exists($this->appPath. '/.content/' .$path)) {
            return $this->appPath. '/.content/' .$path;

        } elseif (file_exists($path)) {
            return $path;

        } elseif (file_exists($this->frameworkPath . '/' . $path)) {
            return $this->frameworkPath . '/' . $path;
        }

        /**
         * Support path indexed in modules
         *
         * Basically every module has it's on directory structure.
         * If the structure contains for example a "translations" folder then we could look into it when searching for a translation.
         */
        // get first "/" in the string
        $firstLevelFolderPos = strpos($path, '/', 1);

        if ($packages === true && $firstLevelFolderPos !== false)
        {
            // now we could pick root folder from this path by knowing where was first "/" occurence
            $firstLevelFolder = substr($path, 0, $firstLevelFolderPos);

            // this is the path without our root folder name
            $chrootPath = substr($path, $firstLevelFolderPos, (strlen($path) - $firstLevelFolderPos));

            // let's now look in every path in application index cache in group "path_{$rootFolderName}"
            if (isset($this->applicationIndex['path_' .$firstLevelFolder]))
            {
                foreach ($this->applicationIndex['path_' .$firstLevelFolder] as $path)
                {
                    $found = $this->getPath('/' .$path . '/' .$chrootPath, false);

                    if ($found)
                    {
                        return $found;
                    }
                }
            }
        }

        throw new FileNotFoundException('Could not find "' .$path. '" file in project\'s filesystem', 'FW_FILE_NOT_FOUND');
    }
}