<?php
/**
  * Crontab configuration
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

// generate new key
if (!$panthera->config->getKey('crontab_key') or $_GET['action'] == 'save')
{
    $panthera -> config -> setKey('crontab_key', generateRandomString(64), 'string');
}

// show generated key and url
$panthera -> template -> push ('crontabKey', $panthera -> config -> getKey('crontab_key'));
$panthera -> template -> push ('crontabUrl', str_replace('http:/', 'http://', str_replace('//', '/', $panthera -> config -> getKey('url'). '/_crontab.php?_appkey=' .$panthera -> config -> getKey('crontab_key'))));

$installer -> enableNextStep();
$installer -> template = 'crontab';
