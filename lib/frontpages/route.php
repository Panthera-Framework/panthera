<?php
/**
 * Router front controller
 * Resolves SEO urls to correct front controllers
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

require_once 'content/app.php';
include_once PANTHERA_DIR. '/pageController.class.php';

//$this -> panthera -> routing -> map('GET|POST', '/', 'home#index', 'index');
//$this -> panthera -> routing -> map('GET|POST', 'contact', array('front' => 'index.php', 'GET' => array('display' => 'contact')), 'contact');
//$this -> panthera -> routing -> map('GET|POST', 'contact,post-[i:postid]', array('front' => 'index.php', 'GET' => array('display' => 'contact', 'post' => true)), 'contactPost');
//$this -> panthera -> routing -> unmap('index');
//$this -> panthera -> routing -> unmap('contactPost');

/**
 * Router front controller
 * Resolves SEO urls to correct front controllers
 *
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

class routeFrontControllerSystem extends pageController
{
    /**
     * Main function
     * 
     * @feature fc.route.params array $params Modify array($_GET, $_POST) params
     * @author Damian Kęska
     * @return null
     */
    
    public function display()
    {
        $match = $this -> panthera -> routing -> resolve();

        if ($match)
        {
            // add params to $_GET
            if (isset($match['target']['GET']))
                $_GET = array_merge($_GET, $match['target']['GET']);
        
            // add params to $_POST
            if (isset($match['target']['POST']))
                $_POST = array_merge($_POST, $match['target']['POST']);
            
            list($_GET, $_POST) = $this -> getFeature('fc.route.params', array($_GET, $_POST));
        
            // support for redirections
            if (isset($match['target']['redirect']))
            {
                $http = substr($match['target']['redirect'], 0, 7);
        
                if (!isset($match['target']['code']))
                    $match['target']['code'] = 302;
        
                if (count($_GET))
                {
                    if (parse_url($match['target']['redirect'], PHP_URL_QUERY))
                    {
                        $match['target']['redirect'] .= '&' .http_build_query($_GET);
                    } else {
                        $match['target']['redirect'] .= '?' .http_build_query($_GET);
                    }
                }
        
        
                if ($http == 'http://' or $http == 'https:/')
                {
                    header('Location: ' .$match['target']['redirect'], TRUE, $match['target']['code']);
                    pa_exit();
                }
                
                pa_redirect($match['target']['redirect'], $match['target']['code']);
                pa_exit();
            }
        
            $controller = $match['target']['front'];
        
            // merge all parameters from URL
            if ($match['params'])
            {
                if (in_array('POST', $match['methods']))
                    $_POST = array_merge($_POST, $match['params']);
        
                if (in_array('GET', $match['methods']))
                    $_GET = array_merge($_GET, $match['params']);
            }
        
            $this -> panthera -> logging -> output('Including front page from path ' .SITE_DIR. '/' .$controller, 'routing');
        
            if (is_file(SITE_DIR. '/' .$controller))
                include SITE_DIR. '/' .$controller;
        
            pa_exit();
        }
        
        pantheraCore::raiseError('notfound');
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, 'routeFrontControllerSystem');