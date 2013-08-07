<?php
/**
  * Database configuration
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

if (isset($_GET['save']))
{
    $keys = array(
            'debug' => 'bool',
            'hashing_algorithm' => 'string', 
            'header_maskphp' => 'bool',
            'header_framing' => 'string',
            'header_xssprot' => 'bool',
            'header_nosniff' => 'bool',
            'mailing_use_php' => 'bool',
            'mailing_server' => 'string',
            'mailing_server_port' => 'int',
            'mailing_from' => 'string',
            'mailing_user' => 'string',
            'mailing_password' => 'string',
            'mailing_smtp_ssl' => 'bool',
            'session_useragent' => 'bool',
            'session_lifetime' => 'int',
            'cookie_encrypt' => 'bool'
            );
            
    $panthera -> importModule('appconfig');
    $app = new appConfigEditor();
    $config = (array)$app->config;
    
    foreach ($keys as $key => $type)
    {
        if (isset($_POST[$key]))
        {
            $value = $_POST[$key];
            
            if ($type == 'bool')
            {
                if ($value == '')
                    $value = False;
                else
                    $value = True;
            }
            
            if ($type == 'int')
            {
                $value = intval($type);
            }
            
            if (isset($config[$key]))
                unset($config[$key]);
            
            $panthera -> config -> setKey($key, $value, $type);
        }
    }
    
    $app -> config = (object)$config;
    $app -> save();

    // generate random session key
    $panthera -> config -> setKey('session_key', substr(md5(rand(99999, 999999)), 0, 6), 'string');
    $panthera -> config -> setKey('salt', md5(rand(99999, 999999)), 'string');
    $panthera -> config -> setKey('ajax_url', $panthera -> config -> getKey('url'). '/_ajax.php', 'string');
    $panthera -> config -> save();
    ajax_exit(array('status' => 'success'));
}

$installer -> template = 'simpleconfiguration';
