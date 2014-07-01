<?php
/**
 * Panthera Framework error controller
 *
 * @package Panthera\core\system\errorhandler
 * @author Damian Kęska
 * @license LGPLv3
 */

// dont load these components to make this application faster
//define('SKIP_SESSION', True);
//define('SKIP_TEMPLATE', True);

require_once 'content/app.php';
include_once getContentDir('pageController.class.php');

@set_time_limit(0);

/**
 * Panthera Framework error controller
 *
 * @package Panthera\core\system\errorhandler
 * @author Damian Kęska
 */

class _errorControllerControllerSystem extends pageController
{
    /**
     * Allowed error codes to pass in $_GET['code']
     * 
     * @var $allowedCodes
     */
    
    protected $allowedCodes = array(
        'notfound',
        'forbidden',
    );
    
    /**
     * Main function
     * 
     * @return null
     */
    
    public function display()
    {
        // if this file was called directly don't allow to view error pages
        if (basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) == '_errorController.php')
            panthera::raiseError('notfound');
        
        if (!in_array($_GET['code'], $this -> allowedCodes))
            $_GET['code'] = 'notfound';
        
        panthera::raiseError($_GET['code']);
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, '_errorControllerControllerSystem');