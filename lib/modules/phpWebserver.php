<?php
/**
 * PHP Development Server support
 * 
 * Adds static images handling and fixes site URL
 * 
 * @package Panthera\core\frontcontrollers
 * @author Damian Kęska
 * @license LGPLv3
 */
 
$url = parse_url($_SERVER['REQUEST_URI']);
$file = pathinfo($url['path']);

if (in_array($file['extension'], array('png', 'jpg', 'jpeg', 'gif', 'css', 'js', 'php')))
    $webServerFalse = true;

$schema = 'http://';

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
    $schema = 'https://';

// set site base URL to match current directory
$config = $panthera -> config -> getConfig(true);
$config['ajax_url'][1] = $schema.$_SERVER['HTTP_HOST']. '/_ajax.php';
$config['url'][1] = $schema.$_SERVER['HTTP_HOST']. '/';
$panthera -> config -> updateConfigCache($config, true);

// refresh PANTHERA_URL
$panthera -> template -> push('PANTHERA_URL', $config['url'][1]);
$panthera -> template -> push('AJAX_URL', $config['ajax_url'][1]);

// turn debugging on
$panthera -> logging -> debug = true;

/*if($file['extension'] == 'php')
{
    include $_SERVER['DOCUMENT_ROOT']. '/' .basename($url['path']);
    exit;
}*/