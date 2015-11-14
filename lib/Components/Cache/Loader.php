<?php
namespace Panthera\Components\Cache;
use Panthera\Components\Singleton\CoreSingleton;

/**
 * Panthera Framework 2 - cache management class
 * Loads, and validates cache handlers
 *
 * @package Panthera\Components\Cache
 * @author Damian Kęska <damian@pantheraframework.org>
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class Loader extends CoreSingleton
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
    protected static $singletonPath   = 'Components/Cache/Drivers/';

    /**
     * Class suffix
     *
     * @var string
     */
    protected static $singletonClassSuffix = 'CacheDriver';

    /**
     * Namespace
     *
     * @var string
     */
    protected static $singletonClassNamespace = 'Panthera\\Components\\Cache\\Drivers\\';

    /**
     * Required interface
     *
     * @var string|null
     */
    protected static $singletonInterface = 'Panthera\\Components\\Cache\\CacheInterface';

    /**
     * Default cache handler to take from configuration
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKey = 'cache/type';

    /**
     * Default value for configuration key
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKeyDefault = 'SQLite3';

    /**
     * Action performed right after creating a first instance of object
     *
     * @param object $object
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public static function constructInstance($object)
    {
        $object->setup();
    }
}
