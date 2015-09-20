<?php
namespace Panthera\cache;
use Panthera\coreSingleton;

/**
 * Panthera Framework 2 - cache management class
 * Loads, and validates cache handlers
 *
 * @package Panthera\cache
 * @author Damian Kęska <damian@pantheraframework.org>
 * @author Mateusz Warzyński <lxnmen@gmail.com>
 */
class cache extends coreSingleton
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
    protected static $singletonPath   = 'modules/cache/';

    /**
     * Class suffix
     *
     * @var string
     */
    protected static $singletonClassSuffix = 'Cache';

    /**
     * Namespace
     *
     * @var string
     */
    protected static $singletonClassNamespace = '\\Panthera\\cache\\';

    /**
     * Required interface
     *
     * @var string|null
     */
    protected static $singletonInterface = 'Panthera\\cache\\cacheInterface';

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

/**
 * Interface for cache handlers
 *
 * @package Panthera\cache
 * @author Damian Kęska <damian@pantheraframework.org>
 * @author Mateusz Warzyńśki <lxnmen@gmail.com>
 */
interface cacheInterface
{
    public function get($variable);
    public function set($variable, $value, $expirationTime = 60);
    public function delete($variable);
    public function exists($variable);
    public function clear($maxTime = 86400);
    public function setup();
}