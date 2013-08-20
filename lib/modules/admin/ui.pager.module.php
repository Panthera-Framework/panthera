<?php
/**
  * Admin UI: Pager
  * 
  * @package Panthera\adminUI
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Admin UI: Pager
  *
  * @package Panthera\adminUI
  * @author Damian Kęska
  */
  
class uiPager
{
    protected static $pagers;
    protected $barName = "";
    
    public function __construct ($name, $totalItems, $maxOnPage)
    {
        global $panthera;
        $this -> panthera = $panthera;
        $this -> name = $name;
        self::$pagers[$this->name] = array(
            'links' => array(),
            'active' => 1,
            'total' => $totalItems,
            'pageMax' => $maxOnPage,
            'maxLinks' => 6,
            'linkTemplate' => '?page={$page}',
            'onclickTemplate' => False,
            'backBtn' => False,
            'nextBtn' => False,
            'pages' => 0
        );
        
        $panthera -> add_option('template.display', array($this, 'applyToTemplate'));
    }
    
    /**
      * Set link templates
      *
      * @param string $link eg. ?display=users&page={$page}
      * @param string $onclick 
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function setLinkTemplates($link, $onclick=False)
    {
        $link = str_replace('%7B%24', '{$', str_replace('%7D', '}', $link));
    
        self::$pagers[$this->name]['linkTemplate'] = $link;
        
        if (is_string($onclick)) // cannot set true, null or integer
        {
            $onclick = str_replace('%7B%24', '{$', str_replace('%7D', '}', $onclick));
            self::$pagers[$this->name]['onclickTemplate'] = $onclick;
        }
    }
    
    /**
      * Set active page
      *
      * @param int $pageID
      * @return void 
      * @author Damian Kęska
      */
    
    public function setActive($pageID)
    {
        self::$pagers[$this->name]['active'] = intval($pageID);
    }
    
    /**
      * Set total items count
      *
      * @param int $count
      * @return void 
      * @author Damian Kęska
      */
    
    public function setTotalItemsCount($count)
    {
        self::$pagers[$this->name]['total'] = intval($count);
    }
    
    /**
      * Set maximum count of items showed on single page
      *
      * @param int $count
      * @return void 
      * @author Damian Kęska
      */
    
    public function setPageMax($count)
    {
        self::$pagers[$this->name]['pageMax'] = intval($count);
    }
    
    /**
      * Generate all data
      *
      * @author Damian Kęska
      */
    
    protected function build()
    {
        $this -> pager = new Pager(self::$pagers[$this->name]['total'], self::$pagers[$this->name]['pageMax']);
        $this -> pager -> maxLinks = self::$pagers[$this->name]['maxLinks'];
        self::$pagers[$this->name]['pageLimit'] = $this -> pager -> getPageLimit(self::$pagers[$this->name]['active']);
        
        $links = array();
        $pages = $this -> pager -> getPages(self::$pagers[$this->name]['active']);
        end($pages);
        $lastKey = key($pages);
        reset($pages);
        
        foreach ($pages as $num => $active)
        {
            $links[$num] = array(
                'id' => $num+1,
                'active' => $active,
                'link' => str_ireplace('{$page}', $num, self::$pagers[$this->name]['linkTemplate']),
                'onclick' => str_ireplace('{$page}', $num, self::$pagers[$this->name]['onclickTemplate'])
            );
            
            if ($num == $lastKey)
            {
                $links[$num]['last'] = True;
            }
        }
        
        self::$pagers[$this->name]['links'] = $links;
        self::$pagers[$this->name]['pages'] = count($links);
        
        if (self::$pagers[$this->name]['active'] > 0)
        {
            self::$pagers[$this->name]['backBtn'] = str_ireplace('{$page}', (self::$pagers[$this->name]['active']-1), self::$pagers[$this->name]['linkTemplate']);
        }
        
        // back button
        if (self::$pagers[$this->name]['active'] > 0)
        {
            self::$pagers[$this->name]['backBtn'] = array(
                'link' => str_ireplace('{$page}', (self::$pagers[$this->name]['active']-1), self::$pagers[$this->name]['linkTemplate']), 
                'onclick' => str_ireplace('{$page}', (self::$pagers[$this->name]['active']-1), self::$pagers[$this->name]['onclickTemplate'])
            );
        }
        
        // next button
        if (self::$pagers[$this->name]['active'] < $num)
        {
            self::$pagers[$this->name]['nextBtn'] = array(
                'link' => str_ireplace('{$page}', (self::$pagers[$this->name]['active']+1), self::$pagers[$this->name]['linkTemplate']), 
                'onclick' => str_ireplace('{$page}', (self::$pagers[$this->name]['active']+1), self::$pagers[$this->name]['onclickTemplate'])
            );
        }
    }
    
    /**
      * Get SQL offset (position) and limit
      *
      * @return array With offset and limit 
      * @author Damian Kęska
      */
    
    public function getPageLimit()
    {
        if (count(self::$pagers[$this->name]['links']) == 0)
        {
            $this->build();
        }
        
        return self::$pagers[$this->name]['pageLimit'];
    }
    
    /**
      * Send all search bars to template
      *
      * @return void
      * @author Damian Kęska
      */
    
    public static function applyToTemplate()
    {
        global $panthera;
        
        if ($panthera -> logging -> debug)
            $panthera -> logging -> output ('Adding ui.Pagers to template: ' .json_encode(self::$pagers), 'pantheraAdminUI');
            
        $panthera -> template -> push('uiPagers', self::$pagers);
    }
}
