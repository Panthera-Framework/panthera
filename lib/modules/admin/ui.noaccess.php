<?php
/**
  * Admin UI: Title bar/toolbar
  * 
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Admin UI: Title bar/toolbar
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
