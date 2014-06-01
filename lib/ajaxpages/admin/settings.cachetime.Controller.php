<?php
/**
  * Cache life time configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */


/**
  * Cache life time configuration controller
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

class settings_cachetimeAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.conftool', 
        'admin.system.cache'
    );
    
    protected $uiTitlebar = array('Cache life time settings', 'cache');
    
    
    /**
     * Display page
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('cache');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('*');
        $config -> add('cache_timing', localize('Cache life time for selected elements', 'cache'));
        $config -> setFieldType('cache_timing', 'packaged');
        
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
        
        elseif ($result === True)
            ajax_exit(array('status' => 'success'));
        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}