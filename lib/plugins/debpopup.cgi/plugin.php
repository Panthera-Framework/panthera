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
        'version' => PANTHERA_VERSION,
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

            if (is_object($value))
            {
                continue;
            }

            $getItems[] = array(htmlspecialchars($key), nl2br(htmlspecialchars($value)));
        }
        return $getItems;
    }

    /**
     * Display the popup
     *
     * @param string $content
     * @return string
     * @author Damian Kęska
     */

    public function display($content)
    {
        global $panthera;

        // user must be logged in on admin account or have can_use_popup_debugger set to true
        if (!getUserRightAttribute($panthera->user, 'can_use_popup_debugger'))
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

				$memoryPos = strpos(substr($line, $timingPos+1, strlen($line)), ']'); // substr => string ' [pantheraCore] Imported "filesystem" from /lib/modules<br />' (length=61)
                $memory = substr($line, $timingPos+3, $memoryPos-2);

                $categoryPos = substr($line, ($timingPos+3)+($memoryPos-2)+3, strlen($line));
				$category = substr($categoryPos, 0, strpos($categoryPos, ']'));

                if (strpos($timing[1], 'real') !== False)
                {
                    $boldTimeDiff = true;
                }

                $message = substr($categoryPos, strpos($categoryPos, ']')+2, strlen($categoryPos));

                $linesArray[] = array($timing[0], $timing[1], $memory, $category, $message);
            } else {
                $linesArray[] = array('', '', '', '', $line);
            }
        }

        $tables['debug'] = array(
            'name' => 'Debugger log',
            'items' => $linesArray,
            'header' => array(
                'Time', 'Diffirence', 'Memory', 'Category', 'Message'
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

        $tables['usermeta'] = array(
            'name' => 'User meta',
            'items' => $this->iterateInputArray($panthera->user->acl->listAll()),
            'header' => array(
                'key', 'value'
            )
        );

        $tables['modules'] = $this->getModulesList();

        /*$tables['autoloader'] = array(
            'name' => 'Autoloader cache',
            'items' => $this->iterateInputArray($panthera -> config -> getKey('autoloader')),
            'header' => array(
                'Class', 'Module'
            )
        );*/

        /*$tables['pantheraconfig'] = array(
            'name' => 'Loaded config',
            'items' => $this->iterateInputArray($panthera -> config -> getConfig(true)),
            'header' => array(
                'Key', 'Value'
            )
        );*/

        $tables['hooks'] = $this->getHooks();

        // allow other plugins to modify this list
        $tables = $panthera -> executeFilters('debpopup.tables', $tables);

        $panthera -> template -> push('debugTables', $tables);
        $panthera -> template -> push('debugMessages', $debugMessages);
        $panthera -> template -> push('debugArray', $linesArray);

        $output = "<script type='text/javascript' src='js/admin/panthera.js'></script>";
        $output .= "<script type='text/javascript' src='js/admin/pantheraUI.js'></script>";
        $template = filterInput($panthera -> template -> display('debpopup.tpl', True, True, '', '_system'), 'wysiwyg');

        if (extension_loaded('zlib') and !defined('_DEBPOPUP_DISABLE_COMPRESS_') and $panthera -> config -> getKey('debpopup.zlib', 1, 'bool', 'debpopup'))
        {
            $template = base64_encode(gzcompress(str_replace('\n', "\n", $template), 9));
            $output .= "<script type='text/javascript' src='js/admin/jsxcompressor.min.js'></script>";
            $output .= "<script type='text/javascript'>var compressed = '".$template."';\nvar w = window.open('','name','height=400,width=1000'); \nw.document.write(htmlspecialchars_decode(JXG.decompress(compressed))); \nw.document.close();</script>";
        } else {
            $output .= "<script type='text/javascript'>var w = window.open('','name','height=400,width=1000'); \nw.document.write(htmlspecialchars_decode('".$template."')); \nw.document.close();</script>";
        }

        return $content.$output;
    }

    /**
     * A hook for ajax_exit function
     *
     * @param array $array
     * @return $array
     */

    public function displayAjaxExit($array)
    {
        $array['appendHTML'] = $this->display(True);
        return $array;
    }

    /*
     * Get all hooks to display
     *
     * @return array
     */

    protected function getHooks()
    {
        global $panthera;

        $hooks = $panthera -> getAllHooks();
        $array = array();

        foreach ($hooks as $hookName => $elements)
        {
            $t = '';

            foreach ($elements as $element)
            {
                if (is_array($element))
                {
                    if (is_object($element[0]))
                    {
                        $t .= get_class($element[0]). ' -> ' .$element[1]. '( )<br />';
                        continue;
                    }

                    $t .= $element[0]. '::' .$element[1]. '( )<br />';
                    continue;
                }

                $t .= $element. '( )<br />';
            }

            $array[] = array($hookName, $t);
        }

        $item = array(
            'name' => 'Hooks',
            'items' => $array,
            'header' => array(
                'Hook name', 'Hooked functions'
            )
        );

        return $item;
    }

    /**
     * Generating modules list with list of classes
     *
     * @return array
     * @author Damian Kęska
     */

    protected function getModulesList()
    {
        global $panthera;

        $array = array();
        $autoloader = $panthera -> config -> getKey('autoloader');
        $classList = array();

        foreach ($autoloader as $class => $module)
        {
            $classList[$module][] = $class;
        }

        foreach ($panthera->listModules() as $module => $enabled)
        {
            if (is_array($classList[$module]))
                $array[] = array($module, implode(', ', $classList[$module]));
        }

        $item = array(
            'name' => 'Modules',
            'items' => $array,
            'header' => array(
                'Module', 'Included classes'
            )
        );

        return $item;
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
        $panthera -> addOption('template.display.rendered', array($obj, 'display'), 4);
        $panthera -> addOption('panthera.ajax_exit', array($obj, 'displayAjaxExit'), 4);
    }
}