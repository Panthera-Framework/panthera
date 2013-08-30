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
  
class uiTitlebar
{
    protected $settings = array(
        'title' => '', 
        'backButton' => True, 
        'icons' => array ('left' => array(), 'right' => array())
    );
    
    protected $panthera;
    
    public function __construct($title='')
    {
        global $panthera;
        $this -> panthera = $panthera;
        
        $this->settings['title'] = $title;
        $panthera -> add_option('template.display', array($this, 'applyToTemplate'));
    }
    
    /**
      * Set toolbar title
      *
      * @param string $title
      * @return void 
      * @author Damian Kęska
      */

    public function setTitle($title)
    {
        $this->settings['title'] = $title;
    }
    
    /**
      * Enable or disable back button
      *
      * @param bool $value
      * @return void 
      * @author Damian Kęska
      */
    
    public function backButton($value)
    {
        $this->settings['backButton'] = (bool)$value;
    }
    
    /**
      * Add icons to toolbar
      *
      * @param string $icon Link to image
      * @param string $alignment Left or right
      * @param string $href Optional link
      * @param string $onclick Optional onclick attribute
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function addIcon($icon, $alignment='right', $href='', $onclick='')
    {
        $this->settings['icons'][$alignment][] = array(
            'image' => pantheraUrl($icon), 
            'link' => pantheraUrl($href), 
            'onclick' => pantheraUrl($onclick)
        );
    }
    
    /**
      * Apply everything to template
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public function applyToTemplate()
    {
        $this->panthera->template->push('uiTitlebar', $this->settings);
    }
}
