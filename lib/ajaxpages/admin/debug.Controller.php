<?php
/**
  * Debug tools and debug.log
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @hook ajaxpages.debug.tools
  * @license GNU Affero General Public License 3, see license.txt
  */


/**
  * Debug tools and debug.log
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class debugAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'Debugging center', 'debug'
    );
    
    protected $permissions = array(
        'admin.developertools' => array('Developer tools', 'debug'),
    );

    
    /**
     * Change debug value
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
    
    public function toggleDebugValueAction()
    {
        $this -> checkPermissions('can_manage_debug');
        
        $value = intval(!(bool)$this->panthera->config->getKey('debug', 0, 'bool'));
        
        if ($this -> panthera -> config -> setKey('debug', $value, 'bool'))
            ajax_exit(array('status' => 'success', 'state' => $value));
        
        ajax_exit(array('status' => 'failed'));
    }
    
    
    
    /**
     * Toggle strict debugging
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
 
    public function toggleStrictDebuggingAction()
    {
        $this -> checkPermissions('can_manage_debug');
        
        $value = intval(!(bool)$this->panthera->config->getKey('debug.strict', 0, 'bool'));
        
        if ($this -> panthera -> config -> setKey('debug.strict', $value, 'bool'))
            ajax_exit(array('status' => 'success', 'state' => $value));
        
        ajax_exit(array('status' => 'failed'));
    }
    
    
    
    /**
     * Set messages filtering mode
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
      
    public function setMessagesFilterAction()
    {
        $this -> checkPermissions('can_manage_debug');
        
        switch ($_POST['value'])
        {
            case 'whitelist':
                $this -> panthera -> session -> set('debug.filter.mode', 'whitelist');
                break;
            
            case 'blacklist':
                $this -> panthera -> session -> set('debug.filter.mode', 'blacklist');
                break;
            
            default:
                $this -> panthera -> session -> remove('debug.filter.mode');
                break;
        }
        
        ajax_exit(array('status' => 'success'));
    }

    
    
    /**
     * Add or remove filter
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */
  
    public function manageFilterListAction()
    {
        $this -> checkPermissions('can_manage_debug');
    
        $filters = $this -> panthera -> session -> get('debug.filter');
        $filterName = $_POST['filter'];
        
        if ($filterName == '' or !ctype_alpha($filterName))
            ajax_exit(array('status' => 'failed'));
        
        if (!is_array($filters))
            $filters = array();
    
        if (!array_key_exists($filterName, $filters))
            $filters[$filterName] = True;
        else
            unset($filters[$filterName]);
            
        // save filter list
        $this -> panthera -> session -> set('debug.filter', $filters);    
        
        $filtersTpl = array();
        
        foreach ($filters as $filter => $enabled)
            $filtersTpl[] = $filter;
    
        ajax_exit(array('status' => 'success', 'filter' => implode(', ', $filtersTpl)));
    }
    
    
    
    /**
     * Create list with debug items, allows hooking
     *
     * @feature ajaxpages.debug.tools $array List of items to display on page
     * 
     * @author Mateusz Warzyński
     * @return array
     */
     
    protected function getDebugItems()
    {
        // list of links (editable via @hook ajaxpages.debug.tools)
        $tools = array();
        
        $tools[] = array(
            'link' => '?display=settings&cat=admin&action=system_info',
            'name' => localize('System'), 'description' => localize('Informations about system and session'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/system_info.png'
        );
        
        $tools[] = array(
            'link' => '?display=debhook&cat=admin',
            'name' => localize('Debhook'),
            'description' => localize('Plugins debugger'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/debhook.png'
        );
        
        $tools[] = array(
            'link' => '?display=includes&cat=admin',
            'name' => localize('Includes'),
            'description' => localize('List of all included files in current code execution'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/includes.png'
        );
        
        $tools[] = array(
            'link' => '?display=errorpages&cat=admin',
            'name' => localize('Errorpages'),
            'description' => localize('Test system error pages in one place'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/errorpages.png'
        );
        
        $tools[] = array(
            'link' => '?display=syschecksum&cat=admin',
            'name' => localize('Checksum'),
            'description' => localize('Checksum of system files'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/syschecksum.png'
        );
        
        $tools[] = array(
            'link' => '?display=shellutils&cat=admin',
            'name' => localize('Shell'),
            'description' => localize('Shell utils'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/shell.png'
        );
        
        $tools[] = array(
            'link' => '?display=phpinfo&cat=admin',
            'name' => localize('PHP'),
            'description' => localize('phpinfo'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/blank.png'
        );
        
        $tools[] = array(
            'link' => '?display=database&cat=admin',
            'name' => localize('Database'),
            'description' => localize('Database management'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/db.png'
        );
        
        $tools[] = array(
            'link' => '?display=dumpinput&cat=admin',
            'name' => localize('Input'),
            'description' => localize('DumpInput'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/input.png'
        );
        
        $tools[] = array(
            'link' => '?display=mergephps&cat=admin',
            'name' => localize('Merge phps'),
            'description' => ucfirst(localize('merge phps and json arrays', 'dash')),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/mergephps.png'
        );
        
        $tools[] = array(
            'link' => '?display=ajaxpages&cat=admin',
            'name' => localize('Ajaxpages'),
            'description' => localize('Complete list of all ajax avaliable subpages', 'ajaxpages'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/ajaxpages.png'
        );
        
        $tools[] = array(
            'link' => '?display=permissionsList&cat=admin',
            'name' => localize('Permissions list', 'acl'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png'
        );
        
        $tools[] = array(
            'link' => '?display=_popup_jsonedit&cat=admin',
            'name' => localize('JSON popup'),
            'description' => localize('Array editor', 'debug'), 
            'icon' => '{$PANTHERA_URL}/images/admin/menu/array_editor.png'
        );
        
        $tools[] = array(
            'link' => '?display=autoloader&cat=admin',
            'name' => localize('Autoloader'),
            'description' => localize('Autoloader cache', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/autoloader.png'
        );
        
        $tools[] = array(
            'link' => '?display=generate_password&cat=admin',
            'name' => localize('Password'),
            'description' => localize('Generate password', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/generate_password.png'
        );
        
        $tools[] = array(
            'link' => '?display=accessparser&cat=admin',
            'name' => localize('Log parser'),
            'description' => localize('Shows parsed server log', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/blank.png'
        );
        
        $tools[] = array(
            'link' => '?display=googlepr&cat=admin',
            'name' => localize('Ranking of pages'),
            'description' => localize('Shows page rank of given url', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/google.png'
        );
        
        $tools = $this -> getFeature('ajaxpages.debug.tools', $tools);
        
        return $tools;
    }
  
  
    
    /**
     * Main, display function
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return string
     */
    
    public function display()
    {
        // get translates
        $this -> panthera -> locale -> loadDomain('debug');
        $this -> panthera -> locale -> loadDomain('dash');
        $this -> panthera -> locale -> loadDomain('ajaxpages');
        
        $toFile = $this -> panthera -> logging -> tofile;
        $this -> panthera -> logging -> tofile = False;
        
        $this -> dispatchAction();
        
        // get debug items
        $tools = $this -> getDebugItems();
        
        // Displaying main debug site
        if (is_file(SITE_DIR. '/content/tmp/debug.log'))
        {
              $log = explode("\n", $this -> panthera -> logging -> readSavedLog());
              $this -> panthera -> template -> push('debug_log', $log);
        }
        
        // message filter type
        if ($this -> panthera -> session -> get('debug.filter.mode'))
            $this -> panthera -> template -> push('messageFilterType', $this->panthera->session->get('debug.filter.mode'));
        else
            $this -> panthera -> template -> push('messageFilterType', '');
        
        // example filters
        $exampleFilters = array('pantheraCore', 'pantheraUser', 'pantheraGroup', 'pantheraTemplate', 'pantheraLogging', 'pantheraLocale', 'pantheraFetchDB', 'pantheraDB', 'leopard', 'metaAttributes', 'scm');
        
        foreach ($this->panthera->logging->getOutput(True) as $line)
        {
            if (!in_array($line[1], $exampleFilters))
                $exampleFilters[] = $line[1];
        }
        
        $this -> panthera -> template -> push('exampleFilters', $exampleFilters);
        
        // list of all defined filters
        $filtersTpl = array();
        
        if (is_array($this -> panthera -> session -> get('debug.filter')))
        {
            foreach ($this -> panthera -> session -> get('debug.filter') as $filter => $enabled)
                $filtersTpl[] = $filter;
        }   
        
        // debug.log save handlers
        $logHandlers = array();
        
        if ($this -> panthera -> logging -> toVarCache)
            $logHandlers[] = 'varCache';
            
        if ($toFile)
            $logHandlers[] = 'file';
        
        $this -> panthera -> template -> push ('filterList', implode(', ', $filtersTpl));
        $this -> panthera -> template -> push ('logHandlers', implode(', ', $logHandlers));
        $this -> panthera -> template -> push ('current_log', explode("\n", $this -> panthera -> logging -> getOutput()));
        $this -> panthera -> template -> push ('debug', $this -> panthera -> config -> getKey('debug'));
        $this -> panthera -> template -> push ('strictDebugging', $this -> panthera -> config -> getKey('debug.strict'));
        $this -> panthera -> template -> push ('tools', $tools);
        
        return $this -> panthera -> template -> compile('debug.tpl');
    }
   
}
