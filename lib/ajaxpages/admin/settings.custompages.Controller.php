<?php
/**
  * Custom pages configuration
  *
  * @package Panthera\core\ajaxpages\settings_customPages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */


/**
  * Custom pages configuration page controller
  *
  * @package Panthera\core\ajaxpages\settings_customPages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
class settings_custompagesAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.conftool',
        'admin.custompages.settings',
    );
    
    protected $uiTitlebar = array(
        'Static pages configuration', 'settings'
    );
        
        
    
    /**
     * Filter pager and cache timing variables to show only custom pages related entries
     *
     * @package Panthera\core\ajaxpages\settings_customPages
     * @param string $input
     * @author Damian Kęska
     * @return array
     */
    
    public function filterUiSettingsAdd($input)
    {
        // input = $_a, $fKey, $setting, $label, $validator, $value
        
        if (strpos($input[1], 'w_') === 0)
        {
            $input[0] = False; // return false
            return $input;
        }
        
        if (strpos($input[1], '__p_pager') !== False)
        {
            if (!stripos($input[1], 'custompage') !== False)
            {
                $input[0] = False; // return false
                return $input;
            }
        }
        
        if (strpos($input[1], '__p_cache_timing') !== False)
        {
            if (!stripos($input[1], 'custompage') !== False)
            {
                $input[0] = False; // return false
                return $input;
            }
        }
        
        return $input;
    }
    
    
    
    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $this -> panthera -> locale -> loadDomain('settings');
        $this -> panthera -> locale -> loadDomain('custompages');
        
        $this -> panthera -> add_option('ui.settings.add', array($this, 'filterUiSettingsAdd'));
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('*');
        $config -> add('custompage', localize('Custom pages SEO urls configuration', 'custompages'));
        $config -> setFieldType('custompage', 'packaged');
        $config -> setDescription('custompage', localize('{$id} tag will be replaced to represent selected element', 'custompages'));
        
        // add pager configuration
        $config -> add('pager', localize('Admin Panel pager settings', 'settings'));
        $config -> setFieldType('pager', 'packaged');
        
        // cache timing
        $config -> add('cache_timing', localize('Cache life time', 'custompages'));
        $config -> setFieldType('cache_timing', 'packaged');
        
        $result = $config -> handleInput($_POST);

        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
            
        elseif ($result === True)
            ajax_exit(array('status' => 'success'));
        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}