<?php
/**
  * Admin UI: No access dialog
  * 
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Admin UI: No access dialog
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */
  
class uiNoAccess
{
    protected $panthera;
    protected $settings = array(
        'loggedIn' => False
    );
    
    public function __construct()
    {
        global $panthera;
        $this -> panthera = $panthera;
        
        if ($panthera->user)
        {
            $this->settings['loggedIn'] = True;
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' and $_SERVER['HTTP_X_REQUESTED_WITH'])
        {
            if ($panthera -> user)
            {
                ajax_exit(array('status' => 'failed', 'message' => localize('No permissions to execute this action', 'login')));
            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('You\'r session propably expired, please re-sign in', 'login')));
            }
        }
        
        $panthera -> add_option('template.display', array($this, 'applyToTemplate'));
    }
    
    /**
      * Apply everything to template
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function applyToTemplate()
    {
        $this->panthera->template->push('uiNoAccess', $this->settings);
    }
}
