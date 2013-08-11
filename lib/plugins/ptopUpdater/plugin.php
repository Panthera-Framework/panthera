<?php
/**
  * This plugin is collecting data for ptop shell command
  * @package Panthera\plugins\ptopUpdater
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
// register plugin
$pluginInfo = array('name' => 'ptop traffic updater', 'author' => 'Damian Kęska', 'description' => 'This plugin should not be enabled manually', 'version' => PANTHERA_VERSION);

/**
  * Main functions
  *
  * @package Panthera\plugins\ptopUpdater
  * @author Damian Kęska
  */

class ptopUpdater
{
    protected static $time, $rid;
    
    /**
      * Turn on the timer to count page load time
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public static function startDigging()
    {
        global $panthera;
        
        $user = 'guest';
        
        if ($panthera -> user)
            $user = $panthera -> user -> login;
    
        self::$time = microtime_float();
        self::$rid = run::openSocket('page', intval(getmypid()), array('client' => $_SERVER['REMOTE_ADDR'], 'method' => $_SERVER['REQUEST_METHOD'], 'url' => $_SERVER['REQUEST_URI'], 'user' => $user));
    }
    
    /**
      * Take all collected data and send to database
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public static function finish()
    {
        global $panthera;
        $page = microtime_float()-self::$time;
        $overall = microtime_float()-$_SERVER['REQUEST_TIME_FLOAT'];
        
        if (self::$rid != False)
        {
            $run = new run('rid', self::$rid);
            $t = $run -> data;
            $t['time'] = array('overall' => $overall, 'page' => $page, 'template' => $panthera->template->timer);
            $run -> data = $t;
            $run -> save();
            
            // close socket using `rid`
            run::closeSocket('page', '', self::$rid);
            $panthera -> logging -> output('Finished loading page, timing: ' .json_encode($t['time']), 'ptop');
        }
        
    }
}

$panthera -> add_option('page_load_starts', array('ptopUpdater', 'startDigging'));
$panthera -> add_option('page_load_ends', array('ptopUpdater', 'finish'));
