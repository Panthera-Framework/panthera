<?php
/**
 * Base, simple configuration module
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

if (!defined('PANTHERA_INSTALLER'))
    return False;

/**
 * Base, simple configuration module
 * Configure default application options
 * 
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 */

class simpleconfigurationInstallerControllerSystem extends installerController
{
    /**
     * Main function
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function display()
    {
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
                'cookie_encrypt' => 'bool',
            );
        
            $config = (array)$this -> appConfig -> config;
        
            foreach ($keys as $key => $type)
            {
                if (isset($_POST[$key]))
                {
                    $value = $_POST[$key];
        
                    if ($type == 'bool')
                    {
                        if (!$value)
                            $value = False;
                        else
                            $value = True;
                    }
        
                    if ($type == 'int')
                        $value = intval($value);
        
                    if (isset($config[$key]))
                        unset($config[$key]);
        
                    $this -> panthera -> config -> setKey($key, $value, $type);
                }
            }
        
            $this -> appConfig -> config = (object)$config;
            $this -> appConfig -> save();
        
            // generate random session key
            $this -> panthera -> config -> setKey('session_key', substr(md5(rand(99999, 999999)), 0, 6), 'string');
            $this -> panthera -> config -> setKey('salt', md5(rand(99999, 999999)), 'string');
            $this -> panthera -> config -> setKey('ajax_url', $this -> panthera -> config -> getKey('url'). '/_ajax.php', 'string');
            $this -> panthera -> config -> save();
        
            $this -> installer -> enableNextStep();
            
            ajax_exit(array(
                'status' => 'success',
            ));
        }
        
        $this -> installer -> template = 'simpleconfiguration';
    }
}