<?php
/**
  * Newsletter configuration page
  *
  * @package Panthera\core\newsletter
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

  
/**
  * Newsletter configuration page controller
  *
  * @package Panthera\core\newsletter
  * @author Damian Kęska
  * @author Mateusz Warzyński
  */
  
class settings_newsletterAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.conftool',
        'admin.newsletter',
    );
    
    protected $uiTitlebar = array(
        'Newsletter settings', 'settings'
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
        $this -> panthera -> locale -> loadDomain('newsletter');
        
        // defaults
        $this->panthera->config->getKey('nletter.confirm.content', array('english' => 'Hi, {$userName}. <br>Please confirm your newsletter subscription at {$this->panthera_URL}/newsletter.php?confirm={$activateKey} <br>Your unsubscribe url: {$this->panthera_URL}/newsletter.php?unsubscribe={$unsubscribeKey}'), 'array', 'newsletter');
        $this->panthera->config->getKey('nletter.confirm.topic', array('english' => 'Please confirm your newsletter subscription'), 'array', 'newsletter');
        
        // load uiSettings with "passwordrecovery" config section
        $config = new uiSettings('newsletter');
        $config -> languageSelector(True);
        $config -> add('nletter.confirm.topic', localize('Topic', 'newsletter'));
        $config -> add('nletter.confirm.content', localize('Message content', 'newsletter'));
        
        $config -> setDescription('nletter.confirm.content', '{$userName}, {$activateKey}, {$unsubscribeKey}');
        $config -> setDescription('nletter.confirm.topic', '{$userName}, {$activateKey}, {$unsubscribeKey}');
        $config -> setFieldType('nletter.confirm.content', 'wysiwyg');
        $config -> setFieldSaveHandler('nletter.confirm.content', 'uiSettingsMultilanguageField');
        $config -> setFieldSaveHandler('nletter.confirm.topic', 'uiSettingsMultilanguageField');
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
        
        
        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}