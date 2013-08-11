<?php
/**
  * Panthera bootstrap 
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
if (!defined('IN_PANTHERA'))
    exit;
  
// in CLI we have specific environment eg. ctrl+c catching
if (php_sapi_name() == 'cli') {
    define('MODE', 'CLI'); // deprecated
    define('PANTHERA_MODE', 'CLI');
} else {
    define('MODE', 'CGI'); // deprecated
    define('PANTHERA_MODE', 'CGI');
}

if (PANTHERA_MODE == 'CLI') {
    // CLI mode

    if (defined('DEBUG'))
        error_reporting(E_ALL);
    else
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        
} else {
    // CGI mode

    // we will use sessions and cookies
    @session_start();

    ini_set("display_errors", 1);
    error_reporting(E_ERROR | E_PARSE | E_WARNING);
}

// Strip magic quotes
if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

// panthera main directory
define('PANTHERA_DIR', realpath($config['lib']));
define('PANTHERA_VERSION', '1.3.4-DEV');
define('IN_PANTHERA', True);

// include core functions
include(PANTHERA_DIR. '/panthera.php');
include(PANTHERA_DIR. '/database.class.php');

// panthera.min mode support
if (!defined('SKIP_CACHE'))
    include(PANTHERA_DIR. '/cache.class.php');

if (!defined('SKIP_TEMPLATE'))
    include(PANTHERA_DIR. '/templates.class.php');

if (!defined('SKIP_USER'))
    include(PANTHERA_DIR. '/user.class.php');
    
if (!defined('SKIP_LOCALE'))
    include(PANTHERA_DIR. '/locale.class.php');
    
if (!defined('SKIP_SESSION'))
    include(PANTHERA_DIR. '/session.class.php');

// core elements
$panthera = new pantheraCore($config);
$t = str_replace(SITE_DIR, '', str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']));


if ($t[0] == "/")
    $t = substr($t, 1, strlen($t));

define('PANTHERA_FRONTCONTROLLER', '/' .$t); // detect front controller

// for cli we have set of functions for managing application behavor
if (PANTHERA_MODE == 'CLI') {
    include(PANTHERA_DIR. '/cli.class.php');
    $panthera -> cli = new pantheraCli();
} else {
    // set error handler from panthera.php only in CGI mode
    set_exception_handler('pantheraExceptionHandler');
    set_error_handler("pantheraErrorHandler");
    register_shutdown_function("pantheraErrorHandler");

    // navigation is avaliable only in CGI mode
    if (!defined('SKIP_SESSION'))
        navigation::loadHistoryFromSession();
}

// debugging
if ($panthera->config->getKey('debug', False, 'bool') == True)
{
    $panthera -> logging -> debug = True;
    $panthera -> add_option('debug_msg', array($panthera->logging, 'getOutput'));
    $panthera -> logging -> tofile = True;
}

date_default_timezone_set($panthera->config->getKey('timezone', 'Europe/Warsaw'));
$sql = $panthera->db;

// localisations
if (!defined('SKIP_LOCALE'))
{
    $locale = $panthera->locale;
    $panthera -> locale -> fromHeader(); // detect language from Accept-Language HTTP header
}

// template system
if (!defined('SKIP_TEMPLATE'))
{
    $template = new pantheraTemplate($panthera);
    $panthera -> template = $template;
    $template -> push('PANTHERA_URL', $panthera->config->getKey('url'));
    $template -> push('AJAX_URL', $panthera->config->getKey('ajax_url'));
    $template -> push('site_template_css', $panthera->config->getKey('main_css'));
    $template -> push('PANTHERA_VERSION', PANTHERA_VERSION);
}

if (!defined('SKIP_USER') and !defined('SKIP_SESSION'))
{
    // get current user
    $panthera -> user = getCurrentUser();
    $user = $panthera -> user; // will return false if not logged in

    // test
    //$user = new pantheraUser('id', 1);

    // if user is logged in, then customize page
    if ($user != False)
    {
        if ($user -> exists() and !$panthera->session->exists('language'))
        {
            // localisations
            
            if (!defined('SKIP_TEMPLATE'))
                $template -> push('language', $user->language);
                
            if (!defined('SKIP_LOCALE'))
                $locale -> setLocale($user->language);
                
            if (!defined('SKIP_SESSION'))
                $panthera->session->set('language', $user->language);
        }
    }
}

// getting locale from current session
if (!defined('SKIP_LOCALE') and !defined('SKIP_SESSION'))
    $locale -> fromSession();

// customized startup for each website (config.php)
if (function_exists('userStartup'))
    userStartup($panthera);

// load plugins after all core elements
$plugins = $panthera -> loadPlugins();

$panthera -> get_options('page_load_starts');

// if site requires installation, redirect then to installer page
if ($panthera->config->getKey('requires_instalation') and PANTHERA_FRONTCONTROLLER != '/install.php' and PANTHERA_MODE == 'CGI')
    $panthera -> importModule('boot/installer');
