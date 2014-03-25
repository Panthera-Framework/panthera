<?php
/**
  * Cache configuration
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

$panthera -> locale -> loadDomain('cache');

// Detection of APC, XCache and Memcached.
$cacheList = array('xcache' => False, 'apc' => False, 'memcached' => False);
$preffered = 'db';

if (extension_loaded('memcached'))
{
    $cacheList['memcached'] = True;
    $preffered = 'memcached';
}

// check for requirements for built-in caching methods
if (extension_loaded('xcache'))
{
    $cacheList['xcache'] = True;
    $preffered = 'xcache';
}

if (extension_loaded('apc'))
{
    $cacheList['apc'] = True;
    $preffered = 'apc';
}

$cacheList['files'] = True; // files is always available
$cacheList['db'] = True; // db is always available

if (isset($_POST['cache']) and isset($_POST['varCache']))
{
    if (isset($cacheList[$_POST['cache']]) and isset($cacheList[$_POST['varCache']]))
    {
        $panthera -> config -> setKey('cache_type', $_POST['cache'], 'string');
        $panthera -> config -> setKey('varcache_type', $_POST['varCache'], 'string');
        $installer -> enableNextStep();
        ajax_exit(array('status' => 'success'));
    } else
        ajax_exit(array('status' => 'failed'));
}

// if cache is already set - enable next step
if ($panthera -> config -> getKey('cache_type', $preffered, 'string') and $panthera -> config -> getKey('varcache_type', $preffered, 'string'))
{
    $installer -> enableNextStep();
}

$panthera -> template -> push('cache', $panthera -> config -> getKey('cache_type'));
$panthera -> template -> push('varCache', $panthera -> config -> getKey('varcache_type'));
$panthera -> template -> push('cache_list', $cacheList);
$installer -> template = 'cache';
