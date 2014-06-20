<?php
/**
 * Crontab configuration
 *
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 * @license LGPLv3
 */

if (!defined('PANTHERA_INSTALLER'))
    return False;

/**
 * This step is only generating and displaying crontab key and informations
 * 
 * @package Panthera\core\components\installer
 * @author Damian Kęska
 */

class crontabInstallerControllerSystem extends installerController
{
    /**
     * Main function
     * 
     * @author Damian Kęska
     */
    
    public function display()
    {
        // generate new key
        if (!$this -> config -> getKey('crontab_key') or $_GET['action'] == 'save')
            $this -> config -> setKey('crontab_key', generateRandomString(64), 'string');
        
        // show generated key and url
        $this -> template -> push ('crontabKey', $this -> config -> getKey('crontab_key'));
        $this -> template -> push ('crontabUrl', str_replace('http:/', 'http://', str_replace('//', '/', $this -> config -> getKey('url'). '/_crontab.php?_appkey=' .$this -> config -> getKey('crontab_key'))));
        
        $this -> installer -> enableNextStep();
        $this -> installer -> template = 'crontab';
    }
}

