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
define('PANTHERA_VERSION', '1.4-DEV');
define('IN_PANTHERA', True);

/**
 * Core functions and classes
 */

// include core functions
include_once PANTHERA_DIR. '/panthera.php';
include_once PANTHERA_DIR. '/database.class.php';

// panthera.min mode support - BE CAREFUL WHEN USING THIS MODE!
if (!defined('SKIP_TEMPLATE'))
    include_once PANTHERA_DIR. '/templates.class.php';

if (!defined('SKIP_USER'))
    include_once PANTHERA_DIR. '/user.class.php';
    
if (!defined('SKIP_LOCALE'))
    include_once PANTHERA_DIR. '/locale.class.php';
    
if (!defined('SKIP_SESSION'))
    include_once PANTHERA_DIR. '/session.class.php';

if (!defined('_PANTHERA_CORE_'))
    define('_PANTHERA_CORE_', 'pantheraCore');

if (!defined('_PANTHERA_CORE_CLI'))
    define('_PANTHERA_CORE_CLI_', 'pantheraCli');

if (!defined('_PANTHERA_CORE_SESSION_'))
    define('_PANTHERA_CORE_SESSION_', 'pantheraSession');
        
if (!defined('_PANTHERA_CORE_DB_'))
    define('_PANTHERA_CORE_DB_', 'pantheraDB');
        
if (!defined('_PANTHERA_CORE_CONFIG_'))
    define('_PANTHERA_CORE_CONFIG_', 'pantheraConfig');
        
if (!defined('_PANTHERA_CORE_LOCALE_'))
    define('_PANTHERA_CORE_LOCALE_', 'pantheraLocale');
        
if (!defined('_PANTHERA_CORE_LOGGING_'))
    define('_PANTHERA_CORE_LOGGING_', 'pantheraLogging');
        
if (!defined('_PANTHERA_CORE_OUTPUT_CONTROL_'))
    define('_PANTHERA_CORE_OUTPUT_CONTROL_', 'outputControl');
        
if (!defined('_PANTHERA_CORE_TYPES_'))
    define('_PANTHERA_CORE_TYPES_', 'pantheraTypes');
    
if (!defined('_PANTHERA_CORE_TEMPLATE_'))
    define('_PANTHERA_CORE_TEMPLATE_', 'pantheraTemplate');

if (!defined('_PANTHERA_CORE_ROUTER_'))
    define('_PANTHERA_CORE_ROUTER_', 'routing');

// core elements
$c = _PANTHERA_CORE_;
$panthera = new $c($config);
$t = str_replace(SITE_DIR, '', str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']));


if ($t[0] == "/")
    $t = substr($t, 1, strlen($t));

define('PANTHERA_FRONTCONTROLLER', '/' .$t); // detect front controller

/*
 * Error handler and CLI functions
 */

// for cli we have set of functions for managing application behavor
if (PANTHERA_MODE == 'CLI') {
    include(PANTHERA_DIR. '/cli.class.php');
    $c = _PANTHERA_CORE_CLI_;
    $panthera -> cli = new $c;
} else {
    // set error handler from panthera.php only in CGI mode
    set_exception_handler('pantheraExceptionHandler');
    set_error_handler("pantheraErrorHandler");
    register_shutdown_function("pantheraErrorHandler");

    // navigation is avaliable only in CGI mode
    if (!defined('SKIP_SESSION'))
        navigation::loadHistoryFromSession();
}


/**
 * Debugging mode
 */

// debugging
if ($panthera->config->getKey('debug', False, 'bool') == True)
{
    $panthera -> logging -> debug = True;
    $panthera -> add_option('debug_msg', array($panthera->logging, 'getOutput'));
    $panthera -> logging -> tofile = True;
}

/**
 * Locale and timezone
 */

date_default_timezone_set($panthera->config->getKey('timezone', 'Europe/Warsaw'));

// localisations
if (!defined('SKIP_LOCALE'))
{
    $locale = $panthera->locale;
    $panthera -> locale -> fromHeader(); // detect language from Accept-Language HTTP header
}

/*
 * Templates
 */

// template system
if (!defined('SKIP_TEMPLATE'))
{
    $c = _PANTHERA_CORE_TEMPLATE_;
    $template = new $c($panthera);
    $panthera -> template = $template;
    $template -> push('PANTHERA_URL', $panthera->config->getKey('url'));
    $template -> push('AJAX_URL', $panthera->config->getKey('ajax_url'));
    $template -> push('site_template_css', $panthera->config->getKey('main_css'));
    $template -> push('PANTHERA_VERSION', PANTHERA_VERSION);
}

/**
 * User and session
 */

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

// customized startup for each website (app.php)
if (function_exists('userStartup'))
    userStartup($panthera);

// load plugins after all core elements
$plugins = $panthera -> loadPlugins();

$panthera -> get_options('page_load_starts', False);

if ($panthera -> config -> getKey('site.maintenance', 0, 'bool'))
    $panthera -> importModule('boot/maintenance', true);

// if site requires installation, redirect then to installer page
if ($panthera->config->getKey('requires_instalation') and PANTHERA_FRONTCONTROLLER != '/install.php' and PANTHERA_MODE == 'CGI')
    $panthera -> importModule('boot/installer');

if (isset($_GET['__print']))
{
    $panthera -> importModule('boot/print');
    printingModule::render();
}