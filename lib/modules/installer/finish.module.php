<?php
/**
  * Final step in Panthera Installer
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

if ($panthera -> config -> getKey('requires_instalation') == True)
{
    $panthera -> importModule('appconfig');
    $app = new appConfigEditor();
    $config = (array)$app -> config;
    unset($config['requires_instalation']);
    unset($config['cache_db']);
    unset($config['preconfigured']);
    $config['disable_overlay'] = False;
    $app -> config = (object)$config;
    $app -> save();
}

$panthera -> template -> push('userLogin', $panthera -> user -> login);
$installer -> template = 'finish';
