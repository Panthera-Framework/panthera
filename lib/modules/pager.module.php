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
    public $max, $perPage, $pages, $maxLinks=4;

    /**
	 * Constructor
	 *
     * @param int $max Count of all avaliable elements
     * @param int $perPage How many elements show on one page
	 * @return void
	 * @author Damian Kęska
	 */

    public function __construct($max, $perPage='')
    {
        $this->max = $max;
        $this->perPage = intval($perPage);
        
        if (is_string($perPage))
        {
            $pager = $this->getPagerFromTable($perPage);
            $this->perPage = $pager['perPage'];
            $this->maxLinks = $pager['maxLinks'];
        }

        if (gettype($this->max) == "array")
            $this->max = 5;

        if (gettype($this->perPage) == "array")
            $this->perPage = 5;
            
        $this->pages = ceil(($this->max / $this->perPage));
    }
    
    /**
      * Get pager informations from database
      *
      * @param string $name
      * @return array
      * @author Damian Kęska
      */
    
    public function getPagerFromTable($name)
    {
        global $panthera;
        $panthera -> logging -> output ('Getting pager name "' .$name. '" from pager table', 'Pager');
        $pagerData = $panthera -> config -> getKey('pager', array(), 'array', 'ui');
        
        if (!isset($pagerData[$name]))
        {
            $pagerData[$name] = array('perPage' => 5, 'maxLinks' => 6);
            $panthera -> config -> setKey('pager', $pagerData, 'array', 'ui');
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
        $m = (($this->maxLinks/2)-1); // max links in left direction
        $left = ($currentPage-$m);

        if ($left < 0)
            $left = 0;

        $pages = array();

        for ($i=$left; $i<$currentPage; $i++)
        {
            $pages[(string)$i] = False;
        }

        $right = ($currentPage+$m+1);

        if (count($pages) < $m)
            $right += ($m-count($pages));

        $pages[(string)$currentPage] = True;

        if ($right > $this->pages)
            $right = $this->pages;

        for ($i=$currentPage+1; $i<$right; $i++)
        {
            $pages[(string)$i] = False;
        }

        return $pages;
    }
}
