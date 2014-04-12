<?php
/**
  * Dashboard configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Dashboard configuration page controller
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class settings_dashAjaxControllerSystem extends pageController
{
        
    protected $permissions = array('can_update_config_overlay', 'can_edit_session_settings');
    
    protected $uiTitlebar = array('Dash configuration', 'settings');
    
    
    
    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('settings');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('dash');
        $config -> add('dash.enableWidgets', localize('Display dash widgets', 'dash'));
        $config -> add('dash.maxItems', localize('Maximum items on main screen', 'dash'));
        $config -> add('dash.widgets', localize('Widgets', 'dash'));
        $config -> setFieldType('dash.widgets', 'multipleboolselect');
        
        // descriptions
        //$config -> setDescription('site_title', localize('Default site title displayed on every page', 'settings'));
        
        // handlers
        $config -> setFieldSaveHandler('dash.widgets', 'uiSettingsMultipleSelectBoolField');
        
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
        
        elseif ($result === True)
            ajax_exit(array('status' => 'success'));
        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}