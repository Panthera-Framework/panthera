<?php
/**
 * Front controller that loads pages from /lib/pages and /content/pages.
 *
 * To disable example pages from /lib/pages just override it in /content/pages with 0 bytes size.
 *
 * Creating new pages:
 * When creating a new page remember to use pageController class and right name convention eg. contact.php -> contactController and put your code in display() function as its executed right after construct.
 *
 * Permissions:
 * Use $permissions and $actionPermissions to define user permissions to selected actions.
 *
 * Titlebar:
 * ui.Titlebar is a module that allows setting title for every page. Use $uiTitlebar to set title eg. array('My title', 'my-translation-domain-name') or just 'My title in English'
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @license LGPLv3
 */

if (!is_file('content/app.php'))
{
    header('Location: install.php');
    exit;
}

require_once 'content/app.php';

// front controllers utils
include_once PANTHERA_DIR. '/pageController.class.php';

// enable frontside panels
//frontsidePanels::init(); // commented - please include this line in your front.php if you want to use frontisdePanels

/**
 * Front controller that loads pages from /lib/pages and /content/pages. 
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 */

class indexFrontControllerSystem extends pageController
{
    /**
     * Run front controller controller
     * 
     * @feature fc.index.path string $path Allows modifing page controller path
     * @feature fc.index.controller object $pageController Modify constructed page controller instance
     * @feature fc.index.notfound object $thisFrontController Execute when page controller not found, before raising an error
     * @author Damian Kęska
     * @return null
     */
    
    public function display()
    {
        $panthera = pantheraCore::getInstance();
        
        // include custom functions to default front controller
        if (is_file('content/front.php'))
            require 'content/front.php';
        
        // a small posibility to change "?display" to other param name
        $displayVar = 'display';
        
        if (isset($config['indexDisplayVar']))
            $displayVar = $config['indexDisplayVar'];
        
        $display = str_replace('/', '', addslashes($_GET[$displayVar]));
        $this -> template -> setTemplate($this -> panthera -> config -> getKey('template')); 
        $path = False;
        
        // default page if empty
        if (!$display)
            $display = 'index';
        
        if (!defined('PAGES_DISABLE_LIB') and !$this -> panthera -> config -> getKey('front.index.disablelib', 0, 'bool', 'frontindex'))
        {
            $path = getContentDir('/pages/' .$display. '.Controller.php'); if (!$path) { $path = getContentDir('/pages/' .$display. '.php'); }
        
            // disabled pages can be empty pages
            if (filesize($path) < 8)
            {
                $path = false;
            }
        
        } else {
            if (is_dir(SITE_DIR. '/content/pages/' .$display. '.php'))
                $path = SITE_DIR. '/content/pages/' .$display. '.php';
        }
        $this -> getFeatureRef('fc.index.path', $path);
        
        // here we will include site pages
        if ($path)
        {
            include $path;
        
            $controller = pageController::getController($display);
            $this -> getFeatureRef('fc.index.controller', $controller);
        
            if ($controller)
            {
                print($controller -> run());
                pa_exit();
            }
        
            pa_exit();
        } else {
            $this -> getFeatureRef('fc.index.notfound', $this);
            pantheraCore::raiseError('notfound');
        }
        
        $template -> display();
        pa_exit();
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, 'indexFrontControllerSystem');