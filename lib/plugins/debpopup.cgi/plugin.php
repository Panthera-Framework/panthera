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
      * Rewrite array into array(key, value)
      *
      * @param array $input
      * @return array
      * @author Damian Kęska
      */
    
    public function iterateInputArray($input)
    {
        $getItems = array();
        foreach ($input as $key => $value) 
        { 
            if (is_array($value))
            {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }
        
            $getItems[] = array(htmlspecialchars($key), nl2br(htmlspecialchars($value))); 
        }
        return $getItems;
    }
    
    /**
      * Display the popup
      *
      * @returns void
      * @author Damian Kęska
      */

    public function display()
    {
        global $panthera;
        
        // user must be logged in on admin account
        if (!checkUserPermissions($panthera->user, True))
        {
            return False;
        }
    
        if ($this->displayed)
        {
            return False;
        }
        
        $tables = array();
        
        $this -> displayed = True;
        $debugMessages = nl2br($panthera -> logging -> getOutput());
        
        // parse debug log to array
        $lines = explode("\n", $debugMessages);
        $linesArray = array();
        
        foreach ($lines as $line)
        {
            if (strlen($line) < 2)
                continue;
        
            $timingPos = strpos($line, ']');
            
            if ($timingPos !== False)
            {
                $boldTimeDiff = False;
            
                $timing = explode(', ', str_replace('[', '', str_replace(']', '', substr($line, 0, $timingPos))));
                
                $categoryPos = strpos(substr($line, $timingPos+1, strlen($line)), ']'); // substr => string ' [pantheraCore] Imported "filesystem" from /lib/modules<br />' (length=61)
                $category = substr($line, $timingPos+3, $categoryPos-2);
                
                if (strpos($timing[1], 'real') !== False)
                {
                    $boldTimeDiff = true;
                }
                
                $message = substr($line, $categoryPos+$timingPos+3, -6);
                
                $linesArray[] = array($timing[0], $timing[1], $category, $message);
            } else {
                $linesArray[] = array('', '', '', $line);
            }
        }
        
        $tables['debug'] = array(
            'name' => 'Debugger log',
            'items' => $linesArray, 
            'header' => array(
                'Time', 'Diffirence', 'Category', 'Message'
            )
        );

        $tables['get'] = array(
            'name' => 'Input $_GET',
            'items' => $this->iterateInputArray($_GET),
            'header' => array(
                'key', 'value'
            )
        );
        
        $tables['post'] = array(
            'name' => 'Input $_POST',
            'items' => $this->iterateInputArray($_POST),
            'header' => array(
                'key', 'value'
            )
        );
        
        $tables['server'] = array(
            'name' => 'Input $_SERVER',
            'items' => $this->iterateInputArray($_SERVER),
            'header' => array(
                'key', 'value'
            )
        );
        
        $tables['tplvars'] = array(
            'name' => 'Template variables',
            'items' => $this->iterateInputArray($panthera->template->vars),
            'header' => array(
                'key', 'value'
            )
        );
        
        $panthera -> template -> push('debugTables', $tables);
        $panthera -> template -> push('debugMessages', $debugMessages);
        $panthera -> template -> push('debugArray', $linesArray);
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
