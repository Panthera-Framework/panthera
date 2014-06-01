<?php
/**
  * Navigation history page
  *
  * @package Panthera\core\adminUI\navigation_history
  * @author Damian Kęska
  * @license LGPLv3
  */

class navigation_historyAjaxControllerSystem extends pageController
{
    /**
     * Main function
     * 
     * @return string
     */
    
    public function display()
    {
        $history = array();
        $this -> panthera -> template -> push('navigation_history', navigation::getHistory());
        return $this -> panthera -> template -> compile('navigation_history.tpl');
    }
}