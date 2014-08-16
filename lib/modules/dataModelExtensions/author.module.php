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

trait userModelExtension
{
    protected $__author = null;
    protected $__authorColumn = '';
    
    protected $__modificationAuthor = null;
    protected $__modificationAuthorColumn = '';
    
    /**
     * Get author
     * 
     * @author Damian Kęska
     * @return category
     */
    
    public function getAuthor()
    {
        if (!$this -> __authorColumn)
            throw new userModelExtensionException('To use getAuthor() from userModelExtension trait please set __authorColumn first', 1);
        
        if ($this -> __author === null)
            $this -> __author = new pantheraUser('id', $this -> __get($this -> __authorColumn));
        
        return $this -> __author;
    }
    
    /**
     * Get modification author
     * 
     * @author Damian Kęska
     * @return category
     */
    
    public function getModificationAuthor()
    {
        if (!$this -> __modificationAuthorColumn)
            throw new userModelExtensionException('To use getModificationAuthor() from userModelExtension trait please set __modificationAuthorColumn first', 1);
        
        if ($this -> __modificationAuthor === null)
            $this -> __modificationAuthor = new pantheraUser('id', $this -> __get($this -> __modificationAuthorColumn));
        
        return $this -> __author;
    }
}

class userModelExtensionException extends Exception {}
