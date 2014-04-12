<?php
/**
  * Password recovery settings
  *
  * @package Panthera\core\users\passwordrecovery
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
  
/**
  * Password recovery settings page controller
  *
  * @package Panthera\core\users\passwordrecovery
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */

class settings_passwordrecoveryAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.conftool',
        'admin.settings.register.passwordrecovery',
    );
    
    protected $uiTitlebar = array(
        'Password recovery settings', 'passwordrecovery'
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
        $this -> panthera -> config -> getKey('recovery.mail.title', array (
            'english' => 'Password recovery'
        ), 'array', 'passwordrecovery');
        $this -> panthera -> config -> getKey('recovery.passwd.length', 12, 'int', 'passwordrecovery');
        $this -> panthera -> config -> getKey('recovery.key.length', 32, 'int', 'passwordrecovery');
        $this -> panthera -> config -> getKey('recovery.mail.content', array(
            'english' => 'You requested a new password. If you want to change your current password to "{$recovery_passwd}" please visit this url: {$PANTHERA_URL}/pa-login.php?key={$recovery_key}'
        ), 'array', 'passwordrecovery');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('passwordrecovery');
        $config -> add('recovery.passwd.length', localize('New password length', 'passwordrecovery'), new integerRange(4, 32)); // please note that "." is replaced to "_-_"
        $config -> add('recovery.key.length', localize('Recovery id length', 'passwordrecovery'), new integerRange(4, 32));
        $config -> add('recovery.mail.content', localize('Mail content', 'passwordrecovery'));
        $config -> add('recovery.mail.title', localize('Mail message title', 'passwordrecovery'));
        $config -> setFieldSaveHandler('recovery.mail.content', 'uiSettingsMultilanguageField');
        $config -> setFieldSaveHandler('recovery.mail.title', 'uiSettingsMultilanguageField');
        $result = $config -> handleInput($_POST);
        
        if (is_array($result))
            ajax_exit(array(
                'status' => 'failed',
                'message' => $result['message'][1], 'field' => $result['field'],
            ));
        
        elseif ($result === True)
            ajax_exit(array(
                'status' => 'success',
            ));
            
        
        return $this -> panthera -> template -> compile('settings.passwordRecovery.tpl');
    }
}