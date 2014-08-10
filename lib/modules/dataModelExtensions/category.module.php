<?php
/**
 * Category module extension for pantheraFetchDB based classes
 * 
 * @package Panthera\core\components\category
 * @author Damian Kęska
 * @license LGPLv3
 */
 
 /**
 * Category module extension for pantheraFetchDB based classes
 * 
 * @package Panthera\core\components\category
 * @author Damian Kęska
 */

trait categoryModelExtension
{
    protected $__category = null;
    protected $__categoryColumn = 'categoryid';
    
    /**
     * Get category object and store in cache
     * 
     * @author Damian Kęska
     * @return category
     */
    
    public function getCategory()
    {
        if ($this -> __category === null)
            $this -> __category = new category('categoryid', $this -> __get($this -> __categoryColumn));
        
        return $this -> __category;
    }
}
