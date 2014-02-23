<?php
/**
 * Router front controller
 * Resolves SEO urls to correct front controllers
 *
 * @package Panthera\core
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
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
    $controller = $match['target']['front'];
    
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
    
    $panthera -> logging -> output('Including front controller from path ' .SITE_DIR. '/' .$controller, 'routing');
    
    if (is_file(SITE_DIR. '/' .$controller))
    {
        include SITE_DIR. '/' .$controller;
    }
    
    pa_exit();
}

//throw new Exception('aaa');
pa_redirect($panthera -> config -> getKey('err404.url', '?404', 'string', 'errors'));