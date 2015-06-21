<?php
namespace Panthera;

/**
 * Panthera Framework 2 - cache management class
 * Loads, and validates cache handlers
 *
 * @package Panthera\cache
 */
class cache
{
    public static $cacheInstance = null;

    /**
     * Get cache singleton instance
     *
     * @throws FileNotFoundException
     * @throws InvalidConfigurationException
     * @throws PantheraFrameworkException
     *
     * @return object
     */
    public static function getCache()
    {
        // don't duplicate instances
        if (static::$cacheInstance)
        {
            return static::$cacheInstance;
        }

        $framework = framework::getInstance();
        $cacheName = $framework->config->get('cache.type', 'SQLite3');
        $className = '\\Panthera\\' .$cacheName. 'Cache';
        $path = $framework->getPath('modules/cache/' .$cacheName. 'Cache.class.php');

        // validate if our cache class file exists
        if (!$path)
        {
            throw new InvalidConfigurationException('Cache class handler "' .$className. '" cannot be found, please add it or change cache.type configuration option in app.php', 'FW_CACHE_NO_HANDLER');
        }

        // include and create an instance
        require $path;

        if (!class_exists($className, false))
        {
            throw new InvalidConfigurationException('Cache class handler "' .$className. '" cannot be found, but it\'s file exists, please make sure it\'s implemented correctly', 'FW_CACHE_NO_CLASS');
        }

        // verify if it's implementing a cacheInterface
        if (!in_array('Panthera\cacheInterface', class_implements($className)))
        {
            throw new PantheraFrameworkException('Cache class handler "' .$className. '" is not implementing a "cacheInterface" interface', 'FW_CACHE_NO_INTERFACE');
        }

        self::$cacheInstance = new $className;
        self::$cacheInstance->setup();
        return self::$cacheInstance;
    }
}

/**
 * Interface for cache handlers
 *
 * @package Panthera
 */
interface cacheInterface
{
    public function get($variable);
    public function set($variable, $value, $expirationTime = 60);
    public function delete($variable);
    public function exists($vairalbe);
    public function clear($maxTime = 86400);
    public function setup();
}