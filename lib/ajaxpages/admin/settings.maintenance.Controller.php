<?php
/**
 * General site configuration page
 *
 * @package Panthera\core\adminUI\settings
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

  
/**
 * General site configuration page
 *
 * @package Panthera\core\adminUI\settings
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class settings_maintenanceAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.settings.maintenance' => array('Service maintenance', 'settings'),
        'admin.conftool' => array('Advanced system configuration editor', 'conftool'),
    );
    
    protected $uiTitlebar = array(
        'Service maintenance', 'settings'
    );
    
    
    
    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $this -> panthera -> config -> loadOverlay('site.maintenance');
        
        $this -> panthera -> config -> getKey('site.maintenance', 0, 'bool');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('site.maintenance');
        $config -> languageSelector(True);
        $config -> add('site.maintenance', localize('Site maintenance', 'settings'), new integerRange(0, 1));
        $config -> setFieldType('site.maintenance', 'bool');
        $config -> add('site.maintenance.title', localize('Maintenance title', 'settings'));
        $config -> add('site.maintenance.message', localize('Maintenance message', 'settings'));
        $config -> setFieldType('site.maintenance.message', 'wysiwyg');
        
        // handlers
        $config -> setFieldSaveHandler('site.maintenance.title', 'uiSettingsMultilanguageField');
        $config -> setFieldSaveHandler('site.maintenance.message', 'uiSettingsMultilanguageField');
        
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
        
        elseif ($result === True)
            ajax_exit(array('status' => 'success'));
        
        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}
