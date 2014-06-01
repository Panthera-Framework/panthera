<?php
/**
  * User registration options
  *
  * @package Panthera\core\ajaxpages\settings_register
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * User registration options page controller
  *
  * @package Panthera\core\ajaxpages\settings_register
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */  

class settings_registerAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.settigs.register' => array('User registration settings', 'register'),
        'admin.conftool' => array('Advanced system configuration editor', 'conftool'),
    );
    
    protected $uiTitlebar = array(
        'User registration settings', 'register'
    );
    
    
    
    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński 
     * @return string
     */
     
    public function display()
    {
        $locales = $this -> panthera -> locale -> getLocales();
        $this -> panthera -> template -> push ('languages', $locales);
        $this -> panthera -> template -> push ('activeLanguage', $this -> panthera -> locale -> getFromOverride($_GET['language']));
        
        // some defaults
        $this -> panthera -> config -> getKey('register.group', 'users', 'string', 'register');
        $this -> panthera -> config -> getKey('register.avatar', '{$PANTHERA_URL}/images/default_avatar.png', 'string', 'register');
        $this -> panthera -> config -> getKey('register.confirmation.required', 1, 'bool', 'register');
        $this -> panthera -> config -> getKey('register.open', 0, 'bool', 'register');
        $this -> panthera -> config -> getKey('register.verification.message', array('english' => 'Hello {$userName}, here is a link to confirm your account '.pantheraUrl('{$this->panthera_URL}/pa-login.php?ckey=', False, 'frontend').'{$key}&login={$userName}'), 'array', 'register');
        $this -> panthera -> config -> getKey('register.verification.title', array('english' => 'Account confirmation'), 'array', 'register');
         
        // add icon to titlebar
        $this -> uiTitlebarObject -> addIcon('{$this->panthera_URL}/images/admin/menu/register.png', 'left');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('register');
        $config -> languageSelector(True);
        $config -> add('register.open', localize('Registration open', 'register'));
        $config -> setFieldType('register.open', 'bool');
        $config -> add('register.group', localize('Default group name', 'register')); // please note that "." is replaced to "_-_"
        $config -> add('register.avatar', localize('Default avatar', 'register'));
        $config -> setDescription('register.avatar', localize('Variables'). ': {$PANTHERA_URL}');
        $config -> add('register.confirmation.required', localize('Require mail confirmation', 'register'));
        
        // mail title
        $config -> add('register.verification.title', localize('Mail title', 'register'));
        $config -> setFieldSaveHandler('register.verification.title', 'uiSettingsMultilanguageField');
        $config -> setDescription('register.verification.title', localize('Variables'). ': {$key}, {$userName}, {$userID}');
        
        // mail message
        $config -> add('register.verification.message', localize('Mail message', 'register'));
        $config -> setFieldSaveHandler('register.verification.message', 'uiSettingsMultilanguageField');
        $config -> setDescription('register.verification.message', localize('Variables'). ': {$key}, {$userName}, {$userID}');
        $config -> setFieldType('register.verification.message', 'wysiwyg');
        
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
        
        elseif ($result === True)
            ajax_exit(array('status' => 'success'));

        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}