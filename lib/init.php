<?php
/**
 * Panthera Framework 2 bootstrap
 *
 * @package Panthera
 * @author Damian Kęska <damian@pantheraframework.org>
 */

$bTrace = debug_backtrace(false, 4);
$controllerPath = $bTrace[count($bTrace)-1]['file'];

require_once __DIR__. '/modules/framework.class.php';
spl_autoload_register('Panthera\__pantheraAutoloader');

/**
 * Set the environment
 */
define('PANTHERA_MODE', (PHP_SAPI == 'cli' ? 'CLI' : 'CGI'));

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