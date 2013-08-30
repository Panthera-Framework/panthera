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
        'loggedIn' => False,
        'message' => '',
    );
    
    public function __construct($message='')
    {
        global $panthera;
        $this -> panthera = $panthera;
        $this->settings['message'] = $message;
        
        if ($panthera->user)
        {
            $this->settings['loggedIn'] = True;
        }
    }
    
    public function display()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' and $_SERVER['HTTP_X_REQUESTED_WITH'])
        {
            if (!$this->settings['message'])
            {
                if ($this->settings['loggedIn'])
                {
                    $this->settings['message'] = localize('No permissions to execute this action', 'login');
                } else {
                    $this->settings['message'] = localize('You\'r session propably expired, please re-sign in', 'login');
                }
            }
            
            ajax_exit(array('status' => 'failed', 'message' => $this->settings['message']));
        }
    
        $this -> panthera -> template -> push ('uiNoAccess', $this->settings); 
        $this -> panthera -> template -> display('no_access.tpl');
        pa_exit();
    }
}
