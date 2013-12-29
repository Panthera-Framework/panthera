<?php
/**
  * Example module for cloned
  *
  * @package application\modules\cloned\example_module_for_cloned
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  

if (!defined('IN_PANTHERA'))
    exit;

global $panthera;

/**
  * Example specified code
  * 
  * @package application/modules/cloned
  * @author Mateusz Warzyński
  */

  /* This class will be used if user pastes link with (eg) `example_module_for_cloned.com` will be a domain */
  
class example_module_for_cloned extends cloned_images
{
    protected $parent;
    
    /**
      * Constructor
      *
      * @author Mateusz Warzyński
      */
    
    public function __construct ($state)
    {
        $this->parent = $state;
    }
    
    /**
      * Return options to cloned_images
      *
      * @return array 
      * @author Mateusz Warzyński
      */
    
    public function getOptions()
    {
        // if you want to do something other way for domain `example_module_for_cloned`, please change bool value of function
        return array('parse' => False, 'createImage' => False, 'getImages' => False, 'cropBottom' => 20);
    }
    
    /**
      * Parse link
      *
      * @author Mateusz Warzyński
      */
    
    public function parse()
    {
        /* If parse is true in returned array in getOptions, cloned_images will use this function instead of built-in cloned */
        return True;
    }
    
    // It all goes for other bools values... Enjoy!
}
?>