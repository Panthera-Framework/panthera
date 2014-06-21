<?php
/**
 * Displays static templates
 *
 * @package Panthera\core\adminUI\static-tpl
 * @author Damian Kęska
 * @license LGPLv3
 */

// TODO: Create list of templates, add to menu etc.

if (!defined('IN_PANTHERA'))
    exit;

class static_tplAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin',
    );
    
    /**
     * Main function
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function display()
    {
        // escape string to avoid selecting parent directories
        $name = str_replace(array('../', '..'), '', $_GET['name']);
        
        return $this -> template -> compile('static/' .$name. '.tpl');
    }
}