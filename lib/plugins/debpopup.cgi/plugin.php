<?php
/**
  * This extensions is creating a debugging popup in web browser
  *
  * @package Panthera\plugins\debpopup
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
    
$pluginClassName = 'debpopupPlugin';
  
/**
  * Debpopup main class
  *
  * @package Panthera\plugins\debpopup
  * @author Damian Kęska
  */

class debpopupPlugin extends pantheraPlugin
{
    protected $displayed = False;
    protected static $pluginInfo = array(
        'name' => 'Debpopup',
        'author' => 'Damian Kęska',
        'description' => 'Displays debugging informations in browser\'s popup window',
        'version' => PANTHERA_VERSION
    );
    
    /**
      * Display the popup
      *
      * @returns void
      * @author Damian Kęska
      */

    public function display()
    {
        global $panthera;
    
        if ($this->displayed)
        {
            return False;
        }
        
        $this -> displayed = True;
        $debugMessages = nl2br($panthera -> logging -> getOutput());
        $panthera -> template -> push('debugMessages', $debugMessages);
        $template = filterInput($panthera -> template -> display('debpopup.tpl', True, True, '', '_system'), 'wysiwyg');
        
        print("<script type='text/javascript' src='js/panthera.js'></script>");
        print("<script type='text/javascript' src='js/admin/pantheraUI.js'></script>");
        print("<script type='text/javascript'>var w = window.open('','name','height=400,width=1000'); w.document.write(htmlspecialchars_decode('".$template."')); w.document.close();</script>");
    }
    
    /**
      * Run plugin code on application startup
      *
      * @returns void
      * @author Damian Kęska
      */
    
    public static function run()
    {
        global $panthera;
        $obj = new debpopupPlugin;
        $panthera -> add_option('template.afterRender', array($obj, 'display'));
    }
}
