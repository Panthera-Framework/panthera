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
    public static $pagers;
    protected $barName = "";
    
    public function __construct ($name, $totalItems, $maxOnPage='', $defaultOnPage=16)
    {
        global $panthera;
        $this -> name = $name;
        $panthera -> importModule('pager');
        
        if ($maxOnPage === '')
        {
            $maxOnPage = $name;
        }
        
        self::$pagers[$this->name] = array(
            'links' => array(),
            'active' => 0,
            'total' => $totalItems,
            'pageMax' => $maxOnPage,
            'maxLinks' => 6,
            'linkTemplate' => getQueryString('GET', 'page={$page}', '_'),
            'onclickTemplate' => False,
            'backBtn' => False,
            'nextBtn' => False,
            'pages' => 0,
            'object' => $this,
            'defaultOnPage' => intval($defaultOnPage),
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
        
        if (stripos($link, '{$queryString}') !== False)
        {
            $link = str_ireplace('{$queryString}', getQueryString($_GET, 'page={$page}', '_'), $link);
            $link = str_replace('%7B%24', '{$', str_replace('%7D', '}', $link));
        }
    
        self::$pagers[$this->name]['linkTemplate'] = $link;
        
        if (is_string($onclick)) // cannot set true, null or integer
        {
            $onclick = str_replace('%7B%24', '{$', str_replace('%7D', '}', $onclick));
            
            if (stripos($onclick, '{$queryString}') !== False)
            {
                $onclick = str_ireplace('{$queryString}', getQueryString($_GET, 'page={$page}', '_'), $onclick);
                $onclick = str_replace('%7B%24', '{$', str_replace('%7D', '}', $onclick));
            }
            
            self::$pagers[$this->name]['onclickTemplate'] = $onclick;
        }
        
        return True;
    }
    
    /**
      * Set template links from template file configuration
      *
      * @param $templateName Template file name
      * @return bool 
      * @author Damian Kęska
      */
    
    public function setLinkTemplatesFromConfig($templateName)
    {
        global $panthera;
    
        $config = $panthera->template->getFileConfig($templateName);
        
        if (!$config)
        {
            return False;
        }
        
        $this->setLinkTemplates($config->pagerLink, $config->pagerOnClick);
        
        return True;
    }
    
    /**
      * Limit array by selected range eg. keys from range 40 to 50
      *
      * @param array $array
      * @return array
      * @author Damian Kęska
      */
    
    public function limitArray($array)
    {
        $limit = $this->getPageLimit();
        $newArray = array();
        
        $c = count($array);
        $i = 0;
        
        foreach ($array as $domainName => $domain)
        {
            foreach ($domain as $key => $value)
            {
                $i++;
                
                // rewrite only elements matching our range            
                if ($i >= $limit[0] and $i <= ($limit[0]+$limit[1]))
                {
                    $newArray[$domainName][$key] = $value;
                }
            }
        }
        return $newArray;
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
        
        if (self::$pagers[$this->name]['active'] < 0)
        {
            self::$pagers[$this->name]['active'] = 0;
        }
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
        $this -> pager = new Pager(self::$pagers[$this->name]['total'], self::$pagers[$this->name]['pageMax'], self::$pagers[$this->name]['defaultOnPage']);
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
        
        return self::$pagers[$this->name];
    }
    
    /**
      * Get SQL offset (position) and limit
      *
      * @return array With offset and limit 
      * @author Damian Kęska
      */
    
    public function getPageLimit()
    {
        if (!self::$pagers[$this->name]['links'])
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
        
        foreach (self::$pagers as $pager)
        {
            if (!$pager['links'])
            {
                $pager['object']->build();
            }
        }
        
        if ($panthera -> logging -> debug)
            $panthera -> logging -> output ('Adding ui.Pagers to template: ' .json_encode(self::$pagers), 'pantheraAdminUI');
            
        $panthera -> template -> push('uiPagers', self::$pagers);
    }
}
