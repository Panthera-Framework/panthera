<?php
/**
 * Panthera Framework 2 bootstrap
 *
 * @package Panthera
 * @author Damian Kęska <damian@pantheraframework.org>
 */

require __DIR__. '/modules/pantheraCore.class.php';
spl_autoload_register('__pantheraAutoloader');

/**
 * Set the environment
 */
define('PANTHERA_MODE', (PHP_SAPI == 'cli' ? 'CLI' : 'CGI'));

if (PHP_SAPI == 'CLI')
{
    require __DIR__. '/modules/cli.class.php';
}
