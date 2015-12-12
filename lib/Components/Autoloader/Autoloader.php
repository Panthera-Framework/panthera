<?php
namespace Panthera\Components\Autoloader;
use Panthera\Components\Kernel\Framework;

/**
 * Class autoloader for Panthera Framework
 *
 * @package Panthera\Components\Autoloader
 * @author Damian Kęska <webnull.www@gmail.com>
 */
class Autoloader
{
    /**
     * Get list of indexed classes
     *
     * @param bool $namespaces With or without namespaces?
     * @return array
     */
    public static function getIndexedClasses($namespaces = true)
    {
        $app = framework::getInstance();

        if (!$app->applicationIndex)
        {
            return [];
        }


        if (!$namespaces)
        {
            $entries = [];

            foreach ($app->applicationIndex['autoloader'] as $entry => $path)
            {
                $exp = explode("\\", $entry);
                $entries[end($exp)] = $entry;
            }

            return $entries;
        }

        return array_keys($app->applicationIndex['autoloader']);
    }

    /**
     * @param string $class
     * @author Damian Kęska <webnull.www@gmail.com>
     */
    public static function loadClass($class)
    {
        $app = framework::getInstance();

        // use cache list of classes
        if ($app->applicationIndex)
        {
            if (isset($app->applicationIndex['autoloader']['\\' .$class]))
            {
                require str_replace('$LIB$', PANTHERA_FRAMEWORK_PATH, str_replace('$APP$', $app->appPath, $app->applicationIndex['autoloader']['\\' .$class]));
                return true;
            }
        }

        // check if namespace belongs to application or framework
        if (strpos($class, 'Panthera\\') === false && strpos($class, $app->getNamespace()) === false)
        {
            return false;
        }

        /**
         * Namespaces support
         */
        if (strpos($class, '\\') !== false)
        {
            $path = self::getForNamespace($class);

            if ($path)
            {
                ($app->logging) ? $app->logging->output('[Autoload] getForNameSpace returned ' . $path) : null;
                require_once $path;
                return $path;
            }
            else
            {
                $parts = explode('\\', $class);

                if ($parts[key($parts)] === 'Panthera' || $parts[key($parts)] === $app->getNamespace())
                {
                    unset($parts[key($parts)]);
                }

                $class = '/' .implode('/', $parts);
                ($app->logging) ? $app->logging->output('[Autoload] Manually cut namespace into ' . $class) : null;
            }
        }

        ($app->logging) ? $app->logging->output('[Autoload] require ' . $class . '.php') : null;
        require_once $app->getPath($class . '.php');
    }

    /**
     * Return path to file basing on namespace
     *
     * @param string $namespace
     * @return null|string
     */
    public static function getForNamespace($namespace)
    {
        $app = Framework::getInstance();
        $parts = array_filter(explode('\\', $namespace));

        // application's namespace or framework's namespace
        if ($parts[key($parts)] !== 'Panthera' && $parts[key($parts)] !== $app->getNamespace())
        {
            return false;
        }

        $forceFrameworkPath = ($parts[key($parts)] === 'Panthera');
        unset($parts[key($parts)]);

        // build a fs path
        $path = implode('/', $parts);

        $searchPaths = [
            /** Application's directory */
            $app->appPath . '/.content/' . $path . '.php',

            /** Framework's directory */
            $app->frameworkPath . '/' . $path . '.php',
        ];

        if ($forceFrameworkPath)
        {
            unset($searchPaths[0]);
        }

        // additional check if last element is not a PHP file
        end($parts);
        unset($parts[key($parts)]);
        $path = implode('/', $parts);

        $searchPaths = array_merge($searchPaths, [
            $app->appPath . '/.content/' . $path . '.php',
            $app->frameworkPath . '/' . $path . '.php',
        ]);

        if ($forceFrameworkPath)
        {
            unset($searchPaths[1]);
        }

        foreach ($searchPaths as $path)
        {
            if (is_file($path))
            {
                return $path;
            }
        }

        return null;
    }
}
