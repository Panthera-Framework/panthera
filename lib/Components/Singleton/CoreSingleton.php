<?php
namespace Panthera\Components\Singleton;

use Panthera\Components\Kernel\Framework;
use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;
use Panthera\Classes\BaseExceptions\FileNotFoundException;

/**
 * This class is a special core singleton for core components like cache, templating, database
 * it allows to construct a different handling class, eg. SQLite3 handler in place of database
 *
 * @package Panthera\modules\core
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class CoreSingleton extends BaseFrameworkClass
{
    /**
     * This variable stores a class instance
     *
     * @var object|null
     */
    protected static $singletonObject = null;

    /**
     * Path to handlers
     *
     * @var string
     */
    protected static $singletonPath   = 'modules/';

    /**
     * Class suffix
     *
     * @var string
     */
    protected static $singletonClassSuffix = '';

    /**
     * Namespace
     *
     * @var string
     */
    protected static $singletonClassNamespace = '\\Panthera\\';

    /**
     * Required interface
     *
     * @var string|null
     */
    protected static $singletonInterface = null;

    /**
     * Configuration key that specifies a $type (handler) if null was passed to getInstance()
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKey = null;

    /**
     * Default value for configuration key specified in $singletonTypeConfigKey
     * This will be passed to configuration::get($singletonTypeConfigKey, $singletonTypeConfigKeyDefault)
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKeyDefault = null;

    /**
     * Get core class instance
     *
     * @param string|null $type Database handler, template handler or something else in context
     * @param bool $force Force create a new instance
     *
     * @throws FileNotFoundException
     * @throws PantheraFrameworkException
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     * @return null|BaseFrameworkClass|void
     */
    public static function getInstance($type = null, $force = false)
    {
        $framework = framework::getInstance();

        if (is_object(static::$singletonObject) && !$force)
        {
            return static::$singletonObject;
        }

        if (!$type && $framework->config->get(static::$singletonTypeConfigKey, static::$singletonTypeConfigKeyDefault))
        {
            $type = $framework->config->get(static::$singletonTypeConfigKey, static::$singletonTypeConfigKeyDefault);
        }

        if (!$type)
        {
            throw new PantheraFrameworkException('$type is empty and configuration not specified', 'FW_SINGLETON_NOT_CONFIGURED');
        }

        $path = $framework->getPath(static::$singletonPath . $type. static::$singletonClassSuffix. '.php');
        $className = static::$singletonClassNamespace .$type. static::$singletonClassSuffix;

        require_once $path;

        if (static::$singletonInterface && !in_array(static::$singletonInterface, class_implements($className)))
        {
            throw new PantheraFrameworkException('"' .$className. '" have to implement "' .static::$singletonInterface. '" interface', 'FW_SINGLETON_NOT_IMPLEMENTS_INTERFACE');
        }

        static::$singletonObject = new $className;
        static::constructInstance(static::$singletonObject);

        return static::$singletonObject;
    }

    /**
     * Action performed right after creating a first instance of object
     *
     * @param object $object
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public static function constructInstance($object)
    {
        // dummy function, eg. connect to database here or something
    }
}