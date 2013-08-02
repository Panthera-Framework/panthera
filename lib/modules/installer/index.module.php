<?php
/**
  * Index step in Panthera installer
  * 
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('PANTHERA_INSTALLER'))
    return False;
    
// we will use this ofcourse
global $panthera;
global $installer;

if (!is_dir(SITE_DIR. '/css') or !is_dir(SITE_DIR. '/js') or !is_dir(SITE_DIR. '/images'))
    $panthera -> template -> webrootMerge();

// we will check here the PHP version and required basic modules
$requiredExtensions = array('pcre', 'hash', 'fileinfo', 'json', 'session', 'Reflection', 'Phar', 'PDO', 'gd', 'pdo_mysql', 'pdo_sqlite');
$optionalExtensions = array('mcrypt', 'curl', 'memcached', 'XCache', 'apc', 'xdebug');
$requiredPHPVersion = '5.1.0';

if (strnatcmp(phpversion(),'5.1.0') < 0)
{
    print('PHP version too old');
}

try {
    $panthera -> template -> display('index.tpl');
} catch (Exception $e) {
    die('Cannot find "index.tpl" template for "index" installer step');
}
