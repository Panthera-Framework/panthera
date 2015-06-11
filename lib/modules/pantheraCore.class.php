<?php
namespace Panthera;

/**
 * Abstract Panthera class with Panthera object stored in $this->panthera
 *
 * @package Panthera
 * @author Damian Kęska
 */
abstract class baseClass
{
    /**
     * @var pantheraCore
     */
    protected $app = null;

    /**
     * @var null|baseClass
     */
    protected static $instance = null;

    /**
     * Get self Singleton instance
     *
     * @return null|baseClass
     */
    public function getInstance()
    {
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @author Damian Kęska <damian@pantheraframework.org>
     */
    public function __construct()
    {
        $this->app = pantheraCore::getInstance();
        self::$instance = $this;
    }

    /**
     * Execute hooks and defined functions with name $featureName
     *
     * Example:
     *  $featureName = 'custompages.add' will execute $this->custompages_addFeature($args, $additionalInfo) and $this->panthera->get_filters($featureName, $args, $additionalInfo)
     *
     * @param string $featureName Hook and function name
     * @param mixed $args Args to pass to function and/or hook
     * @param mixed $additionalInfo Additional informations
     * @param bool $fixOnFail Don't loose arguments data if any hook will fail (return false or null)
     *
     * @return $args Mixed arguments
     */
    public function getFeature($featureName, $args='', $additionalInfo=null, $fixOnFail=True)
    {
        $f = preg_replace('/[^\da-zA-Z0-9]/i', '_', $featureName). 'Feature';

        $this->app->logging->output('Looking for this->' .$f. '(args, additionalInfo)', get_called_class());

        if (method_exists($this, $f))
            $args = $this->$f($args, $additionalInfo);

        return $this->app->signals->get($featureName, $args, $fixOnFail, $additionalInfo);
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
            if ($property->getName() == 'panthera')
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
        $this->panthera = panthera::getInstance();
    }
}

/**
 * Class autoloader for Panthera Framework
 *
 * @package Panthera
 * @param string $class name
 *
 * @author Damian Kęska
 * @return mixed
 */
function __pantheraAutoloader($class)
{

}

/**
 * Panthera Framework 2 Core Library
 *
 * @package Panthera
 * @author Damian Kęska <damian@pantheraframework.org>
 */
class pantheraCore
{
    /**
     * @var Panthera\template null
     */
    public $template = null;

    /**
     * @var Panthera\locale null
     */
    public $locale = null;

    /**
     * @var Panthera\logging null
     */
    public $logging = null;

    public function __construct()
    {

    }
}