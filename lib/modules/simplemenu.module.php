<?php
/**
  * Simple menu module allows generating lists of menus, storing them in databases
  *
  * @package Panthera\modules\core
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

class simpleMenu
{
    private $_menu = array(), $panthera, $active, $cache = 0;

    public function __construct()
    {
        global $panthera;
        $this->panthera = $panthera;
        
        // configure caching
        if ($panthera->cacheType('cache') == 'memory' and $panthera->db->cache > 0)
            $this->cache = $panthera->db->cache;
    }
    
    /**
      * Load records from database
      *
      * @param string $category Category name
      * @return bool 
      * @author Damian Kęska
      */

    public function loadFromDB($category)
    {
        $rows = -1;
    
        // get from cache
        if ($this->cache > 0)
        {
            if ($this->panthera->cache->exists('menu.' .$category))
            {
                $this->panthera->logging->output('Loaded menu from cache id=menu.' .$category, 'simpleMenu');
                $rows = $this->panthera->cache->get('menu.'.$category);
            }
        }

        if ($rows == -1)
        {        
            $SQL = $this->panthera->db->query('SELECT * FROM `{$db_prefix}menus` WHERE `type`= :type ORDER BY `order` DESC', array('type' => $category));
            $count = $SQL -> rowCount();
        }

        if ($count > 0)
        {
            if ($rows == -1)
            {
                $rows = $SQL -> fetchAll();
                
                if ($this->cache > 0)
                {
                    $this->panthera->logging->output('Saving menu to cache id=menu'.$category, 'simpleMenu');
                    $this->panthera->cache->set('menu.'.$category, $rows, $this->cache);
                }
            }

            foreach ($rows as $row)
            {
                $attr = @unserialize($row['attributes']);

                if (!is_array($attr))
                    $attr = array('active' => False);

                // set link as active
                if ($this->active == $row['url_id'])
                    $attr['active'] = True;
                else
                    $attr['active'] = False;     

                $row['link'] = pantheraUrl($row['link']);

                $this -> add($row['url_id'], $row['title'], $row['link'], $attr, $row['icon'], $row['tooltip']);
            }
            
            return True;
        }
    }
    
    /**
      * Set item as active
      *
      * @param string $item Index of item (id)
      * @return bool 
      * @author Damian Kęska
      */

    public function setActive($item)
    {
        $this->active = (string)$item;
        return True;
    }
    
    /**
      * Add new item to menu
      *
      * @param string $item Index of item (id)
      * @param string $title Title
      * @param string $link URL Address
      * @param array $attributes Array of attributes (optional)
      * @param string $icon Link to icon (optional)
      * @param string $tooltip Tooltip text (optional)
      * @return void 
      * @author Damian Kęska
      */

    public function add($item, $title, $link, $attributes='', $icon='', $tooltip='')
    {
        $this->_menu[(string)$item] = array('link' => $link, 'name' => $item, 'title' => $title, 'attributes' => $attributes, 'icon' => $icon, 'tooltip' => $tooltip);
    }

    /**
      * Get item by index
      *
      * @param string $item Index of item (id)
      * @return array 
      * @author Damian Kęska
      */

    public function getItem($item)
    {
        if (array_key_exists($item, $this->_menu))
            return $this->_menu[$item];
    }
    
    /**
      * Show generated menu
      *
      * @return array 
      * @author Damian Kęska
      */

    public function show()
    {
        return $this->_menu;
    }
    
    /**
      * Clear the menu, remove all elements
      *
      * @return void 
      * @author Damian Kęska
      */

    public function clear()
    {
        $this->_menu = array();
    }
    
    // ==== STATIC FUNCTIONS ====
    
     /**
     * Get all menu items by `type_name`, $limitTo, $limitFrom, $orderBy = 'order', $order = 'DESC' (descending)
     *
     * @return array
     * @author Damian Kęska
     */

    public static function getItems($menu, $limit=0, $limitFrom=0, $orderBy='order', $order='DESC')
    {
          global $panthera;
          return $panthera->db->getRows('menus', array('type' => $menu), $limit, $limitFrom, '', $orderBy, $order);  
    }

    /**
     * Create menu item
     *
     * @return pantheraUser
     * @author Damian Kęska, Mateusz Warzyński
     */

    public static function createItem($type, $title, $attributes, $link, $language, $url_id, $order, $icon, $tooltip)
    {
        global $panthera;
        $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}menus` (`id`, `type`, `title`, `attributes`, `link`, `language`, `url_id`, `order`, `icon`, `tooltip`) VALUES (NULL, :type, :title, :attributes, :link, :language, :url_id, :order, :icon, :tooltip);', array('type' => $type, 'order' => $order, 'title' => $title, 'attributes' => $attributes, 'link' => $link, 'language' => $language, 'url_id' => $url_id, 'icon' => $icon, 'tooltip' => $tooltip));
        return (bool)$SQL->rowCount();
    }

    /**
     * Remove menu item
     *
     * @return pantheraUser
     * @author Mateusz Warzyński
     */

    public static function removeItem($id)
    {
        global $panthera;
        $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}menus` WHERE `id` = :id', array('id' => $id));
        return (bool)$SQL->rowCount();
    }

    /**
     * Create menu category
     *
     * @return pantheraUser
     * @author Mateusz Warzyński
     */

    public static function createCategory($type_name, $title, $description, $parent, $elements)
    {
        global $panthera;
        $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}menu_categories` (`id`, `type_name`, `title`, `description`, `parent`, `elements`) VALUES (NULL, :type_name, :title, :description, :parent, :elements);', array('type_name' => $type_name, 'title' => $title, 'description' => $description, 'parent' => $parent, 'elements' => $elements));
        return (bool)$SQL->rowCount();
    }


    /**
     * Remove menu category
     *
     * @return pantheraUser
     * @author Mateusz Warzyński
     */
     
    public static function removeCategory($id)
    {
        global $panthera;
        $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}menu_categories` WHERE `id` = :id', array('id' => $id));
        return (bool)$SQL->rowCount();
    }

    /**
     * Get menu categories
     *
     * @return pantheraUser
     * @author Damian Kęska
     */

    public static function getCategories($by, $limit=0, $limitFrom=0)
    {
          global $panthera;
          return $panthera->db->getRows('menu_categories', $by, $limit, $limitFrom, 'menuCategory');  
    }
}


class menuCategory extends pantheraFetchDB
{
    protected $_tableName = 'menu_categories';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'type_name', 'array');
}

class menuItem extends pantheraFetchDB
{
    protected $_tableName = 'menus';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'url_id', 'title');
}
