<?php
/**
  * Pager configuration page
  *
  * @package Panthera\core\ui\pager
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Pager configuration page controller
  *
  * @package Panthera\core\ui\pager
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class settings_pagerAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.conftool', 'admin.settings.pager',
    );
    
    protected $uiTitlebar = array(
        'Pager settings', 'settings'
    );
    
    
    
    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('settings');
        
        // defaults
        $this -> panthera -> config -> getKey('pager', array(), 'array', 'ui');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('ui');
        $config -> add('pager', localize('Pager settings per element', 'settings'));
        $config -> setFieldType('pager', 'packaged');
        
        // handlers
        $config -> setFieldSaveHandler('pager', 'uiSettingsMultipleSelectBoolField');
        
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => $result['message'][1],
                'field' => $result['field'],
            ));
        } elseif ($result === True) {
            ajax_exit(array(
                'status' => 'success',
            ));
        }
        
        return $this -> panthera -> template -> display('settings.genericTemplate.tpl');
    }
}