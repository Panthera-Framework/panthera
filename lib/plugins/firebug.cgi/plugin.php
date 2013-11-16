<?php
/**
  * FirePHP extension for Panthera
  * Integrates with pantheraLogging
  *
  * @package Panthera\plugins\firebug
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
include(PANTHERA_DIR. '/share/firephp-core/lib/FirePHPCore/FirePHP.class.php');

$pluginClassName = 'pantheraFirePHP';

/**
  * Firebug integration (FirePHP extension)
  *
  * @package Panthera\plugins\firebug
  * @author Damian Kęska
  */

class pantheraFirePHP extends pantheraPlugin
{
    public static $pluginInfo = array(
        'name' => 'Firebug integration',
        'author' => 'Damian Kęska',
        'description' => 'Integration with FirePHP',
        'version' => PANTHERA_VERSION, 'configuration' =>
        '?display=firebugSettings&cat=admin'
    );

    /**
      * Actions executed at the end of script
      *
      * @package Panthera\plugins\firebug
      * @return void
      * @author Damian Kęska
      */

    public function stop()
    {
        global $panthera;
        $firephp = FirePHP::getInstance(true);
        $firephp -> fb($_SERVER, 'SERVER');
        $firephp -> fb($_POST, 'POST');
        $firephp -> fb($_GET, 'GET');
        $firephp -> fb($panthera->session->getAll(), 'Panthera session');
        $firephp -> fb($panthera->session->cookies->getAll(), 'Panthera cookies');
        $panthera -> outputControl -> flushAndFinish();
    }
    
    /**
      * Execute on every $panthera->logging->output
      *
      * @param string $msg
      * @return void
      * @author Damian Kęska
      */

    public function log($msg)
    {
        $firephp = FirePHP::getInstance(true);
        
        try {
            $firephp->fb($msg);
        } catch (Exception $e) {
            // pass
        }
    }
    
    /**
      * Add warning to dash
      *
      * @param array $list Input list
      * @return array
      * @author Damian Kęska
      */

    public function warning($list)
    {
        $list[] = array(
            'type' => 'warning',
            'message' => localize('Firebug is enabled, it\'s not recommended to use it in production environment because everybody on your website is able to read sensitive informations such as database communication, and site passwords')
        );
        return $list;
    }
    
    /**
      * Display information about ip whitelist
      *
      * @param array $list
      * @return array
      * @author Damian Kęska
      */

    public function info($list)
    {
        global $panthera;

        $list[] = array(
            'type' => 'info',
            'message' => slocalize('Firebug is currently working only for those IP addresses: %s', 'firebug', $panthera -> config -> getKey('firebug.whitelist', '', 'string', 'firebug'))
        );
        
        return $list;
    }
    
    /**
      * Add Firebug settings to debugging center
      *
      * @param array $list
      * @return array
      * @author Damian Kęska
      */

    public function addToDebuggingCenter($list)
    {
        global $panthera;

        $list[] = array(
            'link' => '?display=firebugSettings&cat=admin',
            'name' => 'Firebug',
            'description' => localize('Firebug settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/firebug.png'
        );
        
        return $list;
    }
    
    /**
      * Firebug settings page
      *
      * @return void
      * @author Damian Kęska
      */

    public function displaySettings()
    {
        if ($_GET['display'] == 'firebugSettings')
        {
            $dir = str_replace('plugin.php', '', __FILE__);
            include($dir.'/settings.php');
        }
    }
    
    /**
      * Run extension
      *
      * @return void
      * @author Damian Kęska
      */
    
    public static function run()
    {
        global $panthera;
        
        // requirements: we are using session core module and we are not in CLI mode
        if (!defined('SKIP_SESSION') and PANTHERA_MODE != 'CLI')
        {
            // start output buffering
            $panthera -> outputControl -> startBuffering();
            
            $obj = new pantheraFirePHP;
            
            // ip whitelists
            $whitelist = str_replace(' ', '', $panthera -> config -> getKey('firebug.whitelist', '', 'string', 'firebug'));
            $list = explode(',', $whitelist);
            $enabled = True;
            
            // if the whitelist is enabled
            if (count($list) > 0 and $list[0] != '')
            {
                // if ip address is not in list
                if (!in_array($_SERVER['REMOTE_ADDR'], $list))
                    $enabled = False;

                $panthera -> add_option('ajaxpages.dash.msg', array($obj, 'info'));
            } else
                $panthera -> add_option('ajaxpages.dash.msg', array($obj, 'warning'));

            // enable firephp
            if ($enabled == True)
            {
                // output old messages
                $firephp = FirePHP::getInstance(true);
                $firephp->fb($panthera->logging->getOutput());

                $panthera -> add_option('template.display', array($obj, 'stop'));
                $panthera -> add_option('logging.output', array($obj, 'log'));
            }

            $panthera -> add_option('ajax_page', array($obj, 'displaySettings'));
            $panthera -> add_option('ajaxpages.debug.tools', array($obj, 'addToDebuggingCenter'));
        }
    }
}
