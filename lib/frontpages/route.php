<?php
/**
 * Router front controller
 * Resolves SEO urls to correct front controllers
 *
 * @package Panthera\core\frontcontrollers\route
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

require_once 'content/app.php';

//$panthera -> routing -> map('GET|POST', '/', 'home#index', 'index');
//$panthera -> routing -> map('GET|POST', 'contact', array('front' => 'index.php', 'GET' => array('display' => 'contact')), 'contact');
//$panthera -> routing -> map('GET|POST', 'contact,post-[i:postid]', array('front' => 'index.php', 'GET' => array('display' => 'contact', 'post' => true)), 'contactPost');
//$panthera -> routing -> unmap('index');
//$panthera -> routing -> unmap('contactPost');

$match = $panthera -> routing -> resolve();

if ($match)
{
    // add params to $_GET
    if (isset($match['target']['GET']))
    {
        $_GET = array_merge($_GET, $match['target']['GET']);
    }
    
    // add params to $_POST
    if (isset($match['target']['POST']))
    {
        $_POST = array_merge($_POST, $match['target']['POST']);
    }
    
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
        {
            $_POST = array_merge($_POST, $match['params']);
        }
        
        if (in_array('GET', $match['methods']))
        {
            $_GET = array_merge($_GET, $match['params']);
        }
    }
    
    $panthera -> logging -> output('Including front page from path ' .SITE_DIR. '/' .$controller, 'routing');
    
    if (is_file(SITE_DIR. '/' .$controller))
    {
        include SITE_DIR. '/' .$controller;
    }
    
    pa_exit();
}

pantheraCore::raiseError('notfound');
