<?php
/**
  * Panthera bootstrap 
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
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
    session_start();

    ini_set("display_errors", 1);
    error_reporting(E_ALL);
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
if (PHP_OS == "WINNT")
{
    // Windows specific stuff
    define('PANTHERA_DIR', str_ireplace("\\boot.php", '', __FILE__));
} else {
    // unix
    define('PANTHERA_DIR', str_ireplace('/boot.php', '', __FILE__));
}

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
define('PANTHERA_FRONTCONTROLLER', '/' .str_replace(SITE_DIR, '', $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'])); // detect front controller
define('PANTHERA_WEBROOT', $config['webroot']);

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
    $locale = $panthera->locale;

// template system
if (!defined('SKIP_TEMPLATE'))
{
    $template = new pantheraTemplate($panthera);
    $template -> setTemplate($panthera->config->getKey('template'));
    $template -> push('PANTHERA_URL', $panthera->config->getKey('url'));
    $template -> push('AJAX_URL', $panthera->config->getKey('ajax_url'));
    $template -> push('site_template_css', $panthera->config->getKey('main_css'));
    $panthera -> template = $template;
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
if (!defined('SKIP_LOCALE'))
    $locale -> fromSession();

// customized startup for each website (config.php)
if (function_exists('userStartup'))
    userStartup($panthera);

// load plugins after all core elements
$plugins = $panthera -> loadPlugins();

/*if(count($plugins) > 0 and is_array($plugins))
{
    foreach ($plugins as $key => $value)
    {
        include($value);
    }
}*/

$panthera -> get_options('page_load_starts');

if ($panthera -> quitAfterPlugins == True)
{
    $panthera -> get_options('quitAfterPlugins');
    pa_exit();
}
