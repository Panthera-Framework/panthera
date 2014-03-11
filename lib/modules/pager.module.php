<?php
/**
  * Universal pager module for all purporses
  *
  * @package Panthera\modules\pager
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

/**
  * Universal pager for all purporses
  *
  * @package Panthera\modules\pager
  * @author Damian Kęska
  */

class Pager
{
    public $max, $perPage, $pages, $maxLinks=16;

    /**
	 * Constructor
	 *
     * @param int $max Count of all avaliable elements
     * @param int $perPage How many elements show on one page
	 * @return void
	 * @author Damian Kęska
	 */

    public function __construct($max, $perPage='', $defaultOnPage=16)
    {
        $this->max = $max;
        $this->perPage = intval($perPage);
        
        if (is_string($perPage))
        {
            $pager = self::getPagerFromTable($perPage, intval($defaultOnPage));
            $this->perPage = $pager['perPage'];
            $this->maxLinks = $pager['maxLinks'];
        }
        
        if (gettype($this->max) == "array")
            $this->max = 24;

        if (gettype($this->perPage) == "array")
            $this->perPage = 24;
            
        $this->pages = (int)ceil(($this->max / $this->perPage));
    }
    
    /**
      * Get pager informations from database
      *
      * @param string $name
      * @return array
      * @author Damian Kęska
      */
    
    public static function getPagerFromTable($name, $defaultOnPage=24)
    {
        global $panthera;
        $panthera -> logging -> output ('Getting pager name "' .$name. '" from pager table', 'Pager');
        $pagerData = $panthera -> config -> getKey('pager', array(), 'array', 'ui');
        
        if (!isset($pagerData[$name]))
        {
            if (intval($defaultOnPage) < 1)
            {
                $defaultOnPage = 24;
            }
        
            $pagerData[$name] = array('perPage' => $defaultOnPage, 'maxLinks' => 8);
            $panthera -> config -> setKey('pager', $pagerData, 'array', 'ui');
            $panthera -> config -> save(); // just in case...
        }
        
        return $pagerData[$name];
    }

    /**
	 * Get limit for SQL query eg. array(10, 5) => LIMIT 5,10. Returns False or array.
	 *
     * @param int $page Number of page we want to get limit for
	 * @return array|bool
	 * @author Damian Kęska
	 */

    public function getPageLimit($page)
    {
        if ($page <= $this->pages)
        {
            return array(($page * $this->perPage), $this->perPage);
        }

        return False;
    }

    /**
	 * Get array with all pages, this array can be passed to template manager to display links or buttons
	 *
     * @param int $currentPage Current page we are on
	 * @return array|bool
	 * @author Damian Kęska
	 */

    public function getPages($currentPage)
    {
        // don't allow currentpage to be higher than pages max count  
        if ($currentPage > $this->pages)
        {
            $currentPage = $this->pages;
        }
        
        $m = (($this->maxLinks/2)-1); // max links in left direction
        $left = ($currentPage-$m);
        
        if ($left < 0)
            $left = 1;

        $pages = array();

        for ($i=$left; $i<$currentPage; $i++)
        {
            if (($i+1) > $this->pages)
            {
                continue;
            }
        
            $pages[(string)$i] = False;
        }

        $right = ($currentPage+$m+1);

        if (count($pages) < $m)
            $right += ($m-count($pages));

        // set current page as active
        $pages[(string)$currentPage] = True;
        
        if ($right > $this->pages)
            $right = $this->pages;

        for ($i=$currentPage+1; $i<$right; $i++)
        {
            if (($i+1) > $this->pages)
            {
                continue;
            }
        
            $pages[(string)$i] = False;
        }

        return $pages;
    }
}
