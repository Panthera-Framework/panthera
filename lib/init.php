<?php
/**
 * Panthera Framework 2 bootstrap
 *
 * @package Panthera
 * @author Damian Kęska <damian@pantheraframework.org>
 */

$bTrace = debug_backtrace(false, 4);
if (array_key_exists('file', $bTrace[count($bTrace)-1]))
{
    $controllerPath = $bTrace[count($bTrace)-1]['file'];
}

// in case of PHPUnit test we must change $controllerPath to appPath
if (stripos($_SERVER['SCRIPT_FILENAME'], 'phpunit') !== false)
{
    foreach ($bTrace as $array)
    {
        if (basename($array['file']) == 'app.php')
        {
            $controllerPath = str_replace('/app.php', '', $array['file']);
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