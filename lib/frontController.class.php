<?php
/**
  * Front controllers related functions
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
 * Abstract interface for front controllers
 * 
 * @package Panthera\core
 * @author Damian Kęska
 */


abstract class frontController {
    
    // list of required modules
    protected $requirements = array();
    protected $panthera = null;
    
    // information for dispatcher to look for this controller name (full class name)
    public static $searchFrontControllerName = '';
    
    /**
     * Initialize front controller
     * 
     * @return object
     */
    
    public function __construct ()
    {
        $this -> panthera = pantheraCore::getInstance();
        
        foreach ($this -> requirements as $module)
        {
            $this -> panthera -> importModule($module);
        }
    }
    
    /**
     * Dummy function
     * 
     * @return null
     */
    
    public function display()
    {
        return '';
    }
}
