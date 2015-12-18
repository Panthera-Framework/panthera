<?php
namespace Panthera\Components\Kernel;

use Panthera\Classes\BaseExceptions\FileNotFoundException;
use Panthera\Classes\BaseExceptions\InvalidConfigurationException;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

use Panthera\Components\Cache\Loader as CacheLoader;
use Panthera\Components\Locale\Locale;
use Panthera\Components\PackageManagement\PackageManager;
use Panthera\Components\Templating\Loader as TemplatingLoader;
use Panthera\Components\Templating\TemplatingInterface;
use Panthera\Components\Configuration\Configuration;
use Panthera\Components\Database\DatabaseDriverLoader;
use Panthera\Components\Logging\Logger;
use Panthera\Components\Signals\SignalsHandler;
use Panthera\Components\Session\Loader as SessionLoader;
use Panthera\Components\Versioning\Version;

require __DIR__ . '/../../Classes/BaseExceptions.php';

/**
 * Panthera Framework 2 Core Library
 *
 * @package Panthera\Components\Kernel
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class Framework
{
    /**
     * @var TemplatingInterface $template
     */
    public $template = null;

    /**
     * @var Locale $locale
     */
    public $locale = null;

    /**
     * @var \Panthera\Components\Logging\Logger $logging
     */
    public $logging = null;

    /**
     * @var \Panthera\Components\Signals\SignalsHandler $signals
     */
    public $signals;

    /**
     * @var \Panthera\Components\Database\Drivers\CommonPDODriver|\Panthera\Components\Database\DatabaseDriverInterface $database
     */
    public $database;

    /**
     * @var \Panthera\Components\Cache\CacheInterface $cache
     */
    public $cache;

    /**
     * @var \Panthera\Components\Configuration\Configuration $config
     */
    public $config;

    /**
     * @var \Panthera\Components\Session\SessionDriverInterface
     */
    public $session;

    /**
     * @var PackageManager
     */
    public $packageManager = null;


    /**
     * @var \Panthera\Components\StartupComponent\StartupComponent
     */
    public $component = null;

    /**
     * @var \Panthera\Components\Versioning\Version $version
     */
    protected $version;

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
    public $applicationIndex = [

    ];

    /**
     * Constructor
     *
     * @param string $controllerPath
     * @author Damian Kęska <webnull.www@gmail.com>
     * @author Mateusz Warzyński <lxnmen@gmail.com>
     */
    public function __construct($controllerPath)
    {
        $this->appPath = pathinfo($controllerPath, PATHINFO_DIRNAME). '/';
        $this->libPath = realpath(__DIR__. '/../../');
        $this->frameworkPath = realpath(__DIR__. '/../../');
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
    public function setup($configuration = array())
    {
        // load composer's autoloader
        if (is_file(__VENDOR_PATH__ . '/autoload.php'))
        {
            require_once __VENDOR_PATH__ . '/autoload.php';
        }

        // load application indexing cache
        $this->loadApplicationIndex();
        $this->isDebugging = isset($configuration['developerMode']) && $configuration['developerMode'];

        $this->signals  = new SignalsHandler();
        $this->config   = new Configuration($configuration);
        $this->logging  = new Logger();
        $this->cache    = CacheLoader::getInstance();
        $this->database = DatabaseDriverLoader::getInstance();
        $this->locale   = new Locale();
        $this->session  = SessionLoader::getInstance();
        $this->packageManager = new PackageManager();
        $this->component = $this->getClassInstance('Components\\StartupComponent\\StartupComponent');
        $this->template = TemplatingLoader::getInstance();

        $this->component->afterFrameworkSetup();
    }

    /**
     * Get class name eg. Components\StartupComponent\StartupComponent
     * could be resolved to:
     * \Panthera\Components\StartupComponents if your application has not overwrite it
     * else it will be resolved to eg.
     * \YourAppName\Components\StartupComponents
     *
     * @param string $className
     * @return string
     */
    public function getClassName($className)
    {
        $parts = explode('\\', $className);

        if (isset($parts[1]) && ($parts[1] === 'Panthera' || $parts[1] === $this->getNamespace()))
        {
            unset($parts[1]);
        }

        $className = join('\\', $parts);

        if (class_exists('\\' . $this->getNamespace() . '\\' . $className))
        {
            return '\\' . $this->getNamespace() . '\\' . $className;
        }

        elseif (class_exists('\\Panthera\\' . $className))
        {
            return '\\Panthera\\' . $className;
        }

        throw new \InvalidArgumentException('"' . $className . '" is not a valid class name');
    }

    /**
     * Get class instance
     *
     * @see getClassName
     *
     * @param string $className
     * @param array $args
     * @param bool $singleton
     *
     * @return mixed
     */
    public function getClassInstance($className, $args = [], $singleton = false)
    {
        $className = $this->getClassName($className);

        if ($singleton)
        {
            return $className::getInstance(...$args);
        }
        else
        {
            return new $className(...$args);
        }
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
     * @param string $controllerPath
     * @author Damian Kęska <webnull.www@gmail.com>
     * @return null|framework
     */
    public static function getInstance($controllerPath = null)
    {
        if (!$controllerPath)
        {
            $controllerPath = isset($_SERVER['APP_PATH']) ? $_SERVER['APP_PATH'] . '/index.php' : null;
        }

        if (!self::$instance)
        {
            self::$instance = new self($controllerPath);
        }

        return self::$instance;
    }

    /**
     * Determine if run a shell application or not (depends on if we are including the class or running it from shell directly)
     */
    public static function runShellApplication($appName)
    {
        $appName = '\\Panthera\\Binaries\\' .$appName. 'Application';

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
         * Basically every module has it's own directory structure.
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

        throw new \Panthera\Classes\BaseExceptions\FileNotFoundException('Could not find "' .$path. '" file in project\'s filesystem', 'FW_FILE_NOT_FOUND');
    }

    /**
     * Get application's name, defaults to "PFApplication"
     *
     * @System Core.Config(key="AppName")
     * @param bool $stripped
     * @return string
     */
    public function getName($stripped = false)
    {
        $name = null;

        if ($this->config instanceof Configuration)
        {
            $name = $this->config->get('AppName');
        }

        if ($stripped)
        {
            $name = preg_replace('/[^\da-z]/i', '', $name);
        }

        if (!$name)
        {
            $name = "PFApplication";
        }

        return $name;
    }

    /**
     * @System Core.Constant(key="PF2_NAMESPACE")
     * @const PF2_NAMESPACE
     * @return string
     */
    public function getNamespace()
    {
        return defined('PF2_NAMESPACE') ? PF2_NAMESPACE : 'PFApplication';
    }

    /**
     * @return Version
     */
    public function getVersionInformation()
    {
        if (!$this->version instanceof Version)
        {
            $this->version = new Version();
        }

        return $this->version;
    }
}