<?php
/**
  * FirePHP extension for Panthera
  * Integrates with pantheraLogging
  *
  * @package Panthera\plugins\firebug
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

$pluginInfo = array('name' => 'Firebug integration', 'author' => 'Damian Kęska', 'description' => 'Integration with FirePHP', 'version' => PANTHERA_VERSION, 'configuration' => '?display=firebugSettings');

include(PANTHERA_DIR. '/share/firephp-core/lib/FirePHPCore/FirePHP.class.php');

/**
  * Actions executed at the end of script
  *
  * @package Panthera\plugins\firebug
  * @return void
  * @author Damian Kęska
  */

function firebugStop()
{
    global $panthera;
    $firephp = FirePHP::getInstance(true);
    $firephp -> fb($_SERVER, 'SERVER');
    $firephp -> fb($_POST, 'POST');
    $firephp -> fb($_GET, 'GET');
    $firephp -> fb($panthera->session->getAll(), 'Panthera session');
    $firephp -> fb($panthera->session->cookies->getAll(), 'Panthera cookies');

    ob_end_flush();
}

/**
  * Execute on every $panthera->logging->output
  *
  * @param string $msg
  * @return void
  * @author Damian Kęska
  */

function firebugLog($msg)
{
    $firephp = FirePHP::getInstance(true);
    $firephp->fb($msg);
}

/**
  * Add warning to dash
  *
  * @param array $list Input list
  * @return array
  * @author Damian Kęska
  */

function firebugWarning($list)
{
    $list[] = array('type' => 'warning', 'message' => localize('Firebug is enabled, it\'s not recommended to use it in production environment because everybody on your website is able to read sensitive informations such as database communication, and site passwords'));
    return $list;
}

/**
  * Display information about ip whitelist
  *
  * @param array $list
  * @return array
  * @author Damian Kęska
  */

function firebugInfo($list)
{
    global $panthera;

    $panthera -> locale -> loadDomain('firebug');
    $list[] = array('type' => 'info', 'message' => slocalize('Firebug is currently working only for those IP addresses: %s', 'firebug', $panthera -> config -> getKey('firebug_whitelist')));
    return $list;
}

/**
  * Add Firebug settings to debugging center
  *
  * @param array $list
  * @return array
  * @author Damian Kęska
  */

function firebugAsDebugTool($list)
{
    global $panthera;

    $list[] = array('link' => '?display=firebugSettings', 'name' => localize('Firebug settings'));
    return $list;
}

/**
  * Firebug settings page
  *
  * @return void
  * @author Damian Kęska
  */

function firebugAjaxpage()
{
    if ($_GET['display'] == 'firebugSettings')
    {
        $dir = str_replace('plugin.php', '', __FILE__);
        include($dir.'/settings.php');
    }
}

// requirements: we are using session core module and we are not in CLI mode
if (!defined('SKIP_SESSION') and PANTHERA_MODE != 'CLI')
{
    // start output buffering
    ob_start();

    // ip whitelists
    $whitelist = str_replace(' ', '', $panthera -> config -> getKey('firebug_whitelist'));
    $list = explode(',', $whitelist);
    $enabled = True;

    // if the whitelist is enabled
    if (count($list) > 0 and $list[0] != '')
    {
        // if ip address is not in list
        if (!in_array($_SERVER['REMOTE_ADDR'], $list))
            $enabled = False;

        $panthera -> add_option('ajaxpages.dash.msg', 'firebugInfo');
    } else
        $panthera -> add_option('ajaxpages.dash.msg', 'firebugWarning');

    // enable firephp
    if ($enabled == True)
    {
        FirePHP::setEnabled(true);

        // output old messages
        $firephp = FirePHP::getInstance(true);
        $firephp->fb($panthera->logging->getOutput());

        $panthera -> add_option('page_load_ends', 'firebugStop');
        $panthera -> add_option('logging.output', 'firebugLog');
    }

    $panthera -> add_option('ajax_page', 'firebugAjaxpage');
    $panthera -> add_option('ajaxpages.debug.tools', 'firebugAsDebugTool');
}
