<?php
/**
 * Panthera Framework 2 bootstrap
 *
 * @package Panthera\bootstrap
 * @author Damian Kęska <damian@pantheraframework.org>
 */

define('PANTHERA_FRAMEWORK_2', true);
define('PANTHERA_FRAMEWORK_PATH', __DIR__);

/**
 * Detect application path
 */
$bTrace = debug_backtrace(false, 4);

if (array_key_exists('file', $bTrace[count($bTrace)-1]))
{
    $controllerPath = $bTrace[count($bTrace)-1]['file'];
}
else
{
    $controllerPath = "";
}

// PHPUnit, change $controllerPath to application directory to make Panthera Framework 2 usable
if (strpos($controllerPath, 'vendor/phpunit/phpunit') !== false)
{
    $controllerPath = realpath(__DIR__. "/../application/");
}

// support for CLI applications ran from Panthera Framework's "/bin" directory
if (strtolower(PHP_SAPI) == 'cli' && (strpos($controllerPath, __DIR__. '/bin/') === 0 || isset($_SERVER['APP_PATH'])))
{
    $cwd = getcwd();

    if (isset($_SERVER['APP_PATH']))
    {
        $cwd = $_SERVER['APP_PATH'];
    }

    $controllerPath = getcwd(). '/index.php';
    require_once getcwd(). '/.content/app.php';
}

// in case of PHPUnit test we must change $controllerPath to appPath
elseif (stripos($_SERVER['SCRIPT_FILENAME'], 'phpunit') !== false)
{
    foreach ($bTrace as $array)
    {
        if (basename($array['file']) == 'app.php')
        {
            $controllerPath = str_replace('/.content/app.php', '/index.php', $array['file']);
            break;
        }
    }
}


require_once __DIR__. '/modules/framework.class.php';
spl_autoload_register('Panthera\__pantheraAutoloader');

/**
 * Set the environment
 */
if (!defined('PANTHERA_MODE'))
{
    define('PANTHERA_MODE', (PHP_SAPI == 'cli' ? 'CLI' : 'CGI'));
}

if (PHP_SAPI == 'CLI')
{
    require __DIR__. '/modules/cli.class.php';
}

if (!isset($defaultConfig))
{
    throw new Panthera\InvalidConfigurationException('Cannot find $defaultConfig variable', 1);
}

$app = new Panthera\framework($controllerPath);
$app->configure($defaultConfig);