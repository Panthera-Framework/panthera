<?php
/**
 * Merge serialized arrays and json files
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 * @license GNU LGPLv3
 */
 
/**
 * Merge serialized arrays and json files
 *
 * @package Panthera\core\ajaxpages
 * @author Damian Kęska
 */

class mergephpsAjaxControllerSystem extends pageController
{
    /**
     * Main function
     * 
     * @return string
     */
    
    public function display()
    {
        $a = $b = array();
        
        if (isset($_POST['aArray']) and $_POST['bArray'])
        {
            $a = $this -> getArray($_POST['aArray']);
            $b = $this -> getArray($_POST['bArray']);
        }
        
        return $this -> panthera -> template -> compile('mergephps.tpl');
    }
    
    /**
     * Unserialize/decode array
     * 
     * @param string $input Input array in any serialized format
     * @return array
     */
    
    public function getArray($input)
    {
        if (@json_decode($input))
            return json_decode($input, true);
        
        if (@unserialize($input))
            return unserialize($input);
        
        return array();
    }
}