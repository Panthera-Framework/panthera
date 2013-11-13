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
  
$pluginInfo = array(
    'name' => 'Debpopup',
    'author' => 'Damian Kęska',
    'description' => 'Displays debugging informations in browser\'s popup window',
    'version' => PANTHERA_VERSION
);

/**
  * Debpopup main class
  *
  * @package Panthera\plugins\debpopup
  * @author Damian Kęska
  */

class debpopupPlugin
{
    protected $displayed = False;
    
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
        
        print("<script type='text/javascript'>var w = window.open('','name','height=400,width=1000'); w.document.write(htmlspecialchars_decode('".$template."')); w.document.close();</script>");
    }
}

$obj = new debpopupPlugin;
$panthera -> add_option('template.afterRender', array($obj, 'display'));
