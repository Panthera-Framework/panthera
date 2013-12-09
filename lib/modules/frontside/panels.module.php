<?php
/**
  * Frontside panels management
  *
  * @package Panthera\modules\frontside\panels
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
global $panthera;
  
/**
  * Panels data model
  * 
  * @package Panthera/modules/notes
  * @author Mateusz Warzyński
  */

class panel extends pantheraFetchDB
{
    protected $_tableName = 'panels';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
    
    
    /**
      * Get panels
      *
      * @return array 
      * @author Mateusz Warzyński
      */
    
    public static function getPanels($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        return $panthera->db->getRows('panels', $by, $limit, $limitFrom, 'panel', $orderBy, $order);
    }
    
    /**
      * Create new panel
      *
      * @param string $placement 
      * @param int $order
      * @param string $module
      * @param string $template
      * @param string $title
      * @param bool $enabled
      * @param string $storage 
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function createPanel($placement, $order, $module, $template, $title, $enabled, $storage)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $array = array('placement' => $placement, 'order' => $order, 'module' => $module, 'template' => $template, 'title' => $title, 'enabled' => $enabled, 'storage' => htmlspecialchars($storage));

        if (!$panthera->db->query('INSERT INTO `{$db_prefix}panels` (`id`, `placement`, `order`, `module`, `template`, `title`, `enabled`, `storage`) VALUES (NULL, :placement, :order, :module, :template, :title, :enabled, :storage);', $array))
            return False;
        
        return True;
    }
    
    /**
      * Remove panel
      *
      * @return bool 
      * @author Mateusz Warzyński
      */
      
    public function remove()
    {
        if (!$panthera->user)
            return False;
        
        if (!$this->panthera->db->query('DELETE FROM `{$db_prefix}panels` WHERE `id` = :id', array('id' => $this->id)))
            return False;
        
        return True;
    }
    
}

/**
  * Placement data model
  * 
  * @package Panthera/modules/notes
  * @author Mateusz Warzyński
  */

class panelsPlacement extends pantheraFetchDB
{
    protected $_tableName = 'panels_placements';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
    
    
    /**
      * Get panel placements
      *
      * @return array 
      * @author Mateusz Warzyński
      */
    
    public static function getPlacements($by, $limit=0, $limitFrom=0, $orderBy='placementid', $order='DESC')
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        return $panthera->db->getRows('panels_placements', $by, $limit, $limitFrom, 'placement', $orderBy, $order);
    }
    
    /**
      * Create new panel placement
      *
      * @param string $name 
      * @param string $title
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function createPlacement($name, $title)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $array = array('name' => $name, 'title' => $title);

        if (!$panthera->db->query('INSERT INTO `{$db_prefix}panels_placements` (`placementid`, `name`, `title`) VALUES (NULL, :name, :title);', $array))
            return False;
        
        return True;
    }
    
    /**
      * Remove panel placement
      *
      * @return bool 
      * @author Mateusz Warzyński
      */
      
    public function remove()
    {
        if (!$panthera->user)
            return False;
        
        if (!$this->panthera->db->query('DELETE FROM `{$db_prefix}panels_placements` WHERE `placementid` = :placementid', array('placementid' => $this->placementid)))
            return False;
        
        return True;
    }
    
}
  
  
/**
  * Menu panel main class
  *
  * @package Panthera\modules\frontside\panels
  * @author Damian Kęska
  */

class frontsidePanels
{
    protected static $panels = array();
    protected static $init = False;
    protected static $displayed = False;
    
    /**
      * Initialize frontside panels module - it's required to work
      *
      * @param bool $loadModules
      * @return void 
      * @author Damian Kęska
      */
    
    public static function init($loadModules=True)
    {
        global $panthera;
    
        if (!self::$init)
        {
            $panthera -> add_option('template.display', array('frontsidePanels', 'display'));
            
            if ($loadModules)
                self::loadModules();
        }
    
        self::$init = True;
    }

    /**
      * Get all panels
      *
      * @param string $placement Search only for panels placed in selected placement
      * @param bool $enabled List only enabled panels
      * @return bool|array 
      * @author Damian Kęska
      */

    public static function fetchAll($placement='', $enabled=True)
    {
        global $panthera;
        
        // TODO: Cache support for SQL Query
        
        $clause = '';
        $whereClause = new whereClause;
        
        if ($placement)
            $whereClause -> add('AND', 'placement', '=', $placement);
            
        if ($enabled)
            $whereClause -> add('AND', 'enabled', '=', 1);
            
        $data = $whereClause->show();
        
        if (count($data[1]))
        {
            $clause = 'WHERE ' .$data[0];
        }
            
        $SQL = $panthera -> db -> query('SELECT * FROM `{$db_prefix}panels` ' .$clause. ' ORDER BY `order`', $data[1]);
        
        if ($SQL -> rowCount() > 0)
        {
            $panels = array();
            foreach ($SQL->fetchAll(PDO::FETCH_ASSOC) as $panel)
            {
                if ($panel['storage'])
                {
                    $panel['storage'] = unserialize($panel['storage']);
                }
                
                $panels[$panel['id']] = $panel;
            }
            
            return $panels;
        }
        
        return False;
    }
    
    /**
      * Load all panel modules
      *
      * @param string $placement Optional placement
      * @return int Count of loaded panels 
      * @author Damian Kęska
      */
    
    public static function loadModules($placement='')
    {
        global $panthera;
    
        $panels = self::fetchAll($placement);
        $i = 0;
        
        foreach ($panels as $panel)
        {
            $obj = False;
            $tpl = False;
            
            $placement = $panel['placement'];
        
            // if its a module
            if ($panel['module'])
            {
                $panthera -> importModule('frontside/panels/' .$panel['module']);
                
                if (!isset(self::$panels[$placement]))
                {
                    self::$panels[$placement] = array();
                }
                
                $className = 'frontsidePanel_' .$panel['module'];
                
                if (!class_exists($className))
                {
                    $panthera -> logging -> output ('Cannot find class "' .$className. '" for panel module "' .$panel['module']. '"', 'frontsidePanels');
                    continue;
                }
                
                try {
                    $obj = new $className;
                } catch (Exception $e) {
                    $panthera -> logging -> output ('Got an exception while trying to load panel module, details: ' .print_r($e->getMessage(), True), 'frontsidePanels');
                }
            }
            
            if ($panel['template'])
            {
                $panthera -> logging -> output ('Assigning "' .$panel['template']. '" to panel id=' .$panel['id'], 'frontsidePanels');
                $tpl = $panel['template'];
            }
            
            $i++;
            self::$panels[$placement][(string)$panel['id']] = array('info' => $panel, 'object' => $obj, 'template' => $tpl, 'view' => '');
        }
        
        return $i;
    }
    
    /**
      * Invoke display method of every panel to get HTML code to pass to main template. This method is executed on template.display hook.
      *
      * @return void 
      * @author Damian Kęska
      */
    
    public static function display()
    {
        global $panthera;
        
        if (self::$displayed)
        {
            return False;
        }
        
        if (!self::$init)
        {
            throw new Exception('Frontside panels needs to be initialized first and used by template.display hook, not directly');
        }
        
        self::$displayed = True;
    
        foreach (self::$panels as $placementName => $placement)
        {
            foreach ($placement as $panelID => $panel)
            {
                if ($panel['object'])
                {
                    if (!method_exists($panel['object'], 'display'))
                    {
                        $panthera -> logging -> output ('Cannot find method "display" for in "' .$panel['info']['module']. '" that is used by panel id=' .$panelID, 'frontsidePanels');
                        continue;
                    }
                
                    $panthera -> logging -> output('Calling display for panel id=' .$panelID, 'frontsidePanels');
                    
                    try {
                        $panel['view'] = $panel['object']->display($panel['info']);
                    } catch (Exception $e) {
                        $panthera -> logging -> output('Got exception trying to execute display on "' .$panel['info']['module']. '" module, panel id=' .$panelID, 'frontsidePanels');
                    }
                }
                
                if ($panel['template'] and !$panel['object'])
                {
                    $panthera -> logging -> output('Displaying template for panel id=' .$panelID, 'frontsidePanels');
                
                    // the template will have access to $panel['data']
                    $panel['view'] = $panthera -> template -> display($panel['template'], True);
                }
                
                self::$panels[$placementName][$panelID] = $panel;
            }
        }
        
        $panthera -> template -> push('frontsidePanels', self::$panels);
    }
}
