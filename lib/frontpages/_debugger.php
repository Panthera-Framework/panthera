<?php
/**
 * Front controller for CLI app - phpsh
 * 
 * Usage: fron shell type phpsh _phpsh.php, and your are in
 * 
 * To include your custom startup functions create "/content/phpsh.rc.php" file with your functions and classes
 * 
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @see http://www.phpsh.org/
 * @license LGPLv3
 */

# include app config and libs
define('PANTHERA_NO_STDIN_READ', true);
require_once 'content/app.php';
include_once getContentDir('pageController.class.php');

/**
 * Panthera Debugger front controller
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 */

class _debuggerControllerSystem extends pageController
{
    /**
     * Default action
     *
     * @return null
     */

    public function display()
    {
        include_once getContentDir('/share/pantheraDebugger/index.php');
        
        if (!is_dir(SITE_DIR. '/__pantheraDebugger'))
        {
            mkdir(SITE_DIR. '/__pantheraDebugger');
            filesystem::recurseCopy(pantheraDebugger::$debuggerPath. '/webroot', SITE_DIR. '/__pantheraDebugger/webroot');
        }
        
        $Debugger -> debuggerWebroot = '__pantheraDebugger';
        $Debugger -> displayOverlay();
    }
}

if (strpos(__FILE__, PANTHERA_DIR) !== FALSE)
{
    $object = new _debuggerControllerSystem();
    $object -> display();
}