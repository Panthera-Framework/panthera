<?php
namespace Panthera\Components\Database;

use Panthera\Components\Singleton\CoreSingleton;
use Panthera\Components\Database\Column;
use Panthera\Components\Kernel\Framework;
use Panthera\Components\Kernel\BaseFrameworkClass;
use Panthera\Classes\BaseExceptions\PantheraFrameworkException;

/**
 * Abstract driver class
 *
 * Note: It's not PHP's 'abstract class' because of a singleton
 *
 * @package Panthera\database
 */
class DatabaseDriverLoader extends CoreSingleton
{
    /**
     * Directory where drivers/handlers are stored
     *
     * @var string
     */
    protected static $singletonPath   = 'Components/Database/Drivers/';

    /**
     * Class name suffix
     *
     * @var string
     */
    protected static $singletonClassSuffix = 'Driver';

    /**
     * Namespace
     *
     * @var string
     */
    protected static $singletonClassNamespace = '\\Panthera\\Components\\Database\\Drivers\\';

    /**
     * Required interface
     *
     * @var string|null
     */
    protected static $singletonInterface = 'Panthera\\Components\\Database\\DatabaseDriverInterface';

    /**
     * Configuration key that specifies default choice
     *
     * @var string
     */
    protected static $singletonTypeConfigKey = 'database/type';

    /**
     * Default configuration value
     *
     * @var string
     */
    protected static $singletonTypeConfigKeyDefault = 'SQLite3';

    /**
     * SQL functions mapped into universal names for translation
     *
     * @var array
     */
    public $functions = array(

    );

    /**
     * Action performed right after creating a first instance of object
     *
     * @param DatabaseDriverInterface|CommonPDODriver $object
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public static function constructInstance($object)
    {
        $object->app->database = $object;
        $object->connect();
        $object->app->signals->execute('framework.database.connected');
    }
}