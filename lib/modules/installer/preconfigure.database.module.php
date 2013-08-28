<?php
/**
  * Database preconfiguration for Panthera Framework
  *
  * @package Panthera\core\installer
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

function preconfigureDatabase()
{
    global $panthera;
    $panthera -> config -> loadOverlay('*');
    $panthera -> config -> setKey('ajax_url', $_SERVER['HTTP_HOST'].str_ireplace('install.php', '_ajax.php', $_SERVER['SCRIPT_NAME']), 'string'); 
    $panthera -> config -> setKey('language_default', 'english', 'string');
    $panthera -> config -> setKey('template', 'example', 'string');
    $panthera -> config -> setKey('debug', true, 'bool');
    $panthera -> config -> setKey('site_title', 'Example title', 'string');
    $panthera -> config -> setKey('upload_max_size', 3145728, 'int');
    $panthera -> config -> setKey('salt', md5(generateRandomString(8096)), 'string');
    $panthera -> config -> setKey('template_debugging', true, 'bool');
    $panthera -> config -> setKey('template_caching', true, 'bool');
    $panthera -> config -> setKey('template_cache_lifetime', 120, 'int');
    $panthera -> config -> setKey('redirect_after_login', 'index.php');
    $panthera -> config -> setKey('languages', array('polski' => True, 'english' => True));
    $panthera -> config -> setKey('session_lifetime', 86400, 'int');
    $panthera -> config -> save();
}
