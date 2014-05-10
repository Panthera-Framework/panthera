<?php
/**
 * Debug tools and debug.log
 *
 * @package Panthera\admin\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @hook ajaxpages.debug.tools
 * @license GNU Affero General Public License 3, see license.txt
 */

include getContentDir('ajaxpages/admin/settings.Controller.php');

/**
 * Debug tools and debug.log
 *
 * @package Panthera\admin\ajaxpages
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */
 
class debugAjaxControllerSystem extends settingsAjaxControllerSystem
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
        $this -> checkPermissions('admin.debug');
        
        $value = intval(!(bool)$this->panthera->config->getKey('debug', 0, 'bool'));
        
        if (array_key_exists('debug', $this -> panthera -> config -> getConfig()))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => slocalize('Cannot modify configuration, please remove key "%s" from app.php to contiune', 'debug', 'debug'),
            ));
        }
        
        if ($this -> panthera -> config -> setKey('debug', $value, 'bool'))
            ajax_exit(array(
                'status' => 'success',
                'state' => $value,
            ));
        
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
        $this -> checkPermissions('admin.debug');
        
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
        
        ajax_exit(array(
            'status' => 'success',
        ));
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
        $this -> checkPermissions('admin.debug');
    
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
    
        ajax_exit(array(
            'status' => 'success',
            'filter' => implode(', ', $filtersTpl),
        ));
    }
    
    /**
     * Overwriting system_info action from settings controller
     * 
     * @return null
     */
     
    public function system_infoAction() { exit; }
    
    /**
     * Overwriting popupateSystemDefaults from settings controller
     * 
     * @return null
     */
    
    public function populateSystemDefaults(&$defaults) {}
    
    /**
     * Main action
     * 
     * @return null
     */
    
    public function mainAction()
    {
        $toFile = $this -> panthera -> logging -> tofile;
        $this -> panthera -> logging -> tofile = False;
        
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
        
        
        $this -> panthera -> template -> push(array(
            'debuggingButtons' => true,
            'filterList' => implode(', ', $filtersTpl),
            'logHandlers' => implode(', ', $logHandlers),
            'current_log' => explode("\n", $this -> panthera -> logging -> getOutput()),
            'debug' => $this -> panthera -> config -> getKey('debug'),
            'strictDebugging' => $this -> panthera -> config -> getKey('debug.strict'),
        ));
        
        parent::mainAction();
    }
    
    /**
     * Add all debugging links
     * 
     * @param int &$defaults Array with links
     * @return null
     */
    
    public function populateContentDefaults(&$defaults)
    {
        /** Application developing **/
        
        $defaults['Application Developing'] = array();
        
        $defaults['Application Developing']['system_info'] = array(
            'link' => '?display=settings&cat=admin&action=system_info',
            'name' => localize('Panthera system informations'),
            'description' => localize('Informations about system and session', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/system_info.png'
        );
        
        $defaults['Application Developing']['permissionsList'] = array(
            'link' => '?display=permissionsList&cat=admin',
            'name' => localize('Permissions list', 'acl'),
            'description' => localize('List of all indexed controllers permissions', 'acl'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png',
            'linkType' => 'ajax',
        );
        
        $defaults['Application Developing']['ajaxpages'] = array(
            'link' => '?display=ajaxpages&cat=admin',
            'name' => localize('Controllers list', 'acl'),
            'description' => localize('Complete list of all ajax avaliable subpages', 'ajaxpages'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/ajaxpages.png',
            'linkType' => 'ajax',
        );
        
        $defaults['Application Developing']['database'] = array(
            'link' => '?display=database&cat=admin',
            'name' => localize('Database'),
            'description' => localize('Database management'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/db.png',
            'linkType' => 'ajax',
        );
        
        $defaults['Application Developing']['langtool'] = array(
            'link' => '?display=langtool&cat=admin',
            'name' => ucfirst(localize('translates', 'dash')),
            'description' => localize('Manage system translations', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/langtool.png',
            'linkType' => 'ajax'
        );
        
        $defaults['Application Developing']['conftool'] = array(
            'link' => '?display=conftool&cat=admin', 
            'name' => localize('Configuration editor', 'dash'), 
            'icon' => '{$PANTHERA_URL}/images/admin/menu/config.png', 
            'linkType' => 'ajax'
        );
        
        $defaults['Application Developing']['routing'] = array(
            'link' => '?display=routing&cat=admin',
            'name' => localize('SEO links management', 'routing'),
            'description' => localize('Front-end urls rewriting', 'settings'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/routing.png',
            'linkType' => 'ajax'
        );
        
        $defaults['Application Developing']['syschecksum'] = array(
            'link' => '?display=syschecksum&cat=admin',
            'name' => localize('Application checksum', 'debug'),
            'description' => localize('Useful for comparing eg. production with test environment', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/syschecksum.png'
        );
        
        $defaults['Application Developing']['autoloader'] = array(
            'link' => '?display=autoloader&cat=admin',
            'name' => localize('Autoloader'),
            'description' => localize('List of all indexed classes', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/autoloader.png'
        );
        
        $defaults['Application Developing']['errorpages'] = array(
            'link' => '?display=errorpages&cat=admin',
            'name' => localize('Errorpages'),
            'description' => localize('Test system error pages in one place'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/errorpages.png'
        );
        
        $defaults['Application Developing']['debhook'] = array(
            'link' => '?display=debhook&cat=admin',
            'name' => localize('Debhook'),
            'description' => localize('Plugins debugger'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/debhook.png'
        );
        
        $defaults['Application Developing']['includes'] = array(
            'link' => '?display=includes&cat=admin',
            'name' => localize('Includes'),
            'description' => localize('List of all included files in current code execution'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/includes.png'
        );
        
        /** Tools **/
        
        $defaults['Tools'] = array();
        
        $defaults['Tools']['_popup_jsonedit'] = array(
            'link' => '?display=_popup_jsonedit&cat=admin',
            'name' => localize('Array editor', 'debug'),
            'description' => localize('JSON, serialize, var_dump, print_r', 'debug'), 
            'icon' => '{$PANTHERA_URL}/images/admin/menu/array_editor.png'
        );
        
        $defaults['Tools']['mergephps'] = array(
            'link' => '?display=mergephps&cat=admin',
            'name' => localize('Merge phps'),
            'description' => ucfirst(localize('merge phps and json arrays', 'dash')),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/mergephps.png'
        );
        
        $defaults['Tools']['generate_password'] = array(
            'link' => '?display=generate_password&cat=admin',
            'name' => localize('Password'),
            'description' => localize('Generate password', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/generate_password.png',
        );
        
        $defaults['Tools']['shellutils'] = array(
            'link' => '?display=shellutils&cat=admin',
            'name' => localize('Shell'),
            'description' => localize('Shell utils'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/shell.png'
        );
        
        $defaults['Tools']['phpinfo'] = array(
            'link' => '?display=phpinfo&cat=admin',
            'name' => localize('PHP informations', 'debug'),
            'description' => 'phpinfo()',
            'icon' => '{$PANTHERA_URL}/images/admin/menu/blank.png'
        );
        
        $defaults['Tools']['dumpinput'] = array(
            'link' => '?display=dumpinput&cat=admin',
            'name' => localize('Input', 'debug'),
            'description' => localize('Input dumping', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/input.png'
        );
        
        $defaults['Tools']['accessparser'] = array(
            'link' => '?display=accessparser&cat=admin',
            'name' => localize('Site traffic browser', 'accessparser'),
            'description' => localize('Shows parsed server logs', 'accessparser'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/blank.png'
        );
        
        $defaults['Tools']['googlepr'] = array(
            'link' => '?display=googlepr&cat=admin',
            'name' => localize('Page rank checker', 'debug'),
            'description' => localize('Shows page rank of given url', 'debug'),
            'icon' => '{$PANTHERA_URL}/images/admin/menu/google.png'
        );
    }
}