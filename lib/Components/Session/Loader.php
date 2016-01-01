<?php
namespace Panthera\Components\Session;

use Panthera\Components\Singleton\CoreSingleton;

/**
 * Panthera Framework 2
 * --------------------
 * Session Loader
 *
 * @package Panthera\Components\Session
 * @author Damian Kęska <damian@pantheraframework.org>
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
    protected static $singletonPath   = 'Components/Session/Drivers/';

    /**
     * Class suffix
     *
     * @var string
     */
    protected static $singletonClassSuffix = 'SessionDriver';

    /**
     * Namespace
     *
     * @var string
     */
    protected static $singletonClassNamespace = 'Panthera\\Components\\Session\\Drivers\\';

    /**
     * Required interface
     *
     * @var string|null
     */
    protected static $singletonInterface = 'Panthera\\Components\\Session\\SessionDriverInterface';

    /**
     * Default cache handler to take from configuration
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKey = 'SessionDriver';

    /**
     * Default value for configuration key
     *
     * @var string|null
     */
    protected static $singletonTypeConfigKeyDefault = 'PHP';

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
