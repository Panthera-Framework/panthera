<?php
namespace Panthera\Components\Kernel;

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