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

// add routes to modules provided with Panthera
$panthera -> routing -> map('GET|POST', 'contact', array('front' => 'index.php', 'GET' => array('display' => 'contact')), 'contact');
$panthera -> routing -> map('GET|POST', 'pu[n:forceNative]?/[*:url_id].html', array('front' => 'index.php', 'GET' => array('display' => 'custom')), 'custom_url_id');
$panthera -> routing -> map('GET|POST', 'pi[n:forceNative]?/[i:id].html', array('front' => 'index.php', 'GET' => array('display' => 'custom')), 'custom_id');
$panthera -> routing -> map('GET|POST', 'pq[n:forceNative]?/,[i:unique].html', array('front' => 'index.php', 'GET' => array('display' => 'custom')), 'custom_unique');
$panthera -> routing -> map('GET|POST', 'facebook/#[*:back]', array('front' => 'index.php', 'GET' => array('display' => 'facebook.connect')), 'facebook_connect');
$panthera -> routing -> map('GET|POST', 'register', array('front' => 'index.php', 'GET' => array('display' => 'register')), 'register');
$panthera -> routing -> map('GET|POST', 'login', array('front' => 'pa-login.php'), 'login');
$panthera -> routing -> map('GET|POST', 'index.[html|py|pyc|rb]', array('redirect' => '', 'code' => 301), 'index-html');


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
