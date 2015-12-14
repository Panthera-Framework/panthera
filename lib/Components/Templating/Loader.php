<?php
namespace Panthera\Components\Templating;
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
    protected static $singletonPath   = 'Components/Templating/Drivers/';

    /**
     * Class suffix
     *
     * @var string
     */
    protected static $singletonClassSuffix = 'TemplatingDriver';

    /**
     * Namespace
     *
     * @var string
     */
    protected static $singletonClassNamespace = 'Panthera\\Components\\Templating\\Drivers\\';

    /**
     * Required interface
     *
     * @var string|null
     */
    protected static $singletonInterface = 'Panthera\\Components\\Templating\TemplatingInterface';

    /**
     * Default cache handler to take from configuration
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKey = 'TemplatingSystem';

    /**
     * Default value for configuration key
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKeyDefault = 'Rain';

    /**
     * Action performed right after creating a first instance of object
     *
     * @param object $object
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public static function constructInstance($object)
    {
        // dummy
    }
}
