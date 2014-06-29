<?php
/**
 * Advertisements module for Panthera Framework
 * 
 * @package Panthera\core\components\advertisements
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Advertisements category ORM model
 * 
 * Represents "ad_places" table
 * 
 * @package Panthera\core\components\advertisements
 * @author Damian Kęska
 */

class adCategory extends pantheraFetchDB
{
    protected $_tableName = 'ad_places';
    protected $_idColumn = 'placename';
    protected $_constructBy = array(
        'id',
        'placename', // placename = id. id will be translated to placename as $_idColumn is set to "placename"
        'array',
    );
    protected $_unsetColumns = array();
    
    /**
     * Get advertisements
     * 
     * @param int $limit Limit results
     * @param int $offset Start query from selected position
     * @param bool $expired Show expired
     * @return array
     */
    
    public function getAds($limit=0, $offset=0, $expired=False)
    {
        $w = new whereClause();
        $w -> add('', 'placename', '=', $this -> placename);
        
        // get only active ads
        if (!$expired)
            $w -> add('AND', 'expires', '>', date('d-m-Y H:i:s'));
        
        return adItem::fetchAll($w, $limit, $offset, 'position', 'ASC');
    }
    
    /**
     * Creates a new block category (this function also validates input)
     * 
     * @param $title Title
     * @param $description (Optional) Description
     * @param $name (Optional) Block name - if empty will be generated from $title
     * @return int|bool
     */
    
    public static function createBlock($title, $description='', $name='')
    {
        $panthera = panthera::getInstance();
        
        if (!$name)
            $name = $title;
        
        $name = Tools::seoUrl(trim($name));
        $title = trim(strip_tags($title));
        
        if (!$name or !$title)
            return false;
        
        // make sure that $name will be unique
        $name = $panthera -> db -> createUniqueData('ad_places', 'placename', $name);
        
        return static::create(array(
            'placename' => $name,
            'title' => $title,
            'description' => $description,
        ));
    }
}

/**
 * Advertisements entry ORM model
 * 
 * Represents "ad_items" table
 * 
 * @package Panthera\core\components\advertisements
 * @author Damian Kęska
 */

class adItem extends pantheraFetchDB
{
    protected $_tableName = 'ad_items';
    protected $_idColumn = 'adid';
    protected $_constructBy = array(
        'id',
        'adid',
        'array',
    );
    protected $_unsetColumns = array();
    
    /**
     * Extended create() function with validation enabled
     * 
     * @param array $array Array of values
     * @author Damian Kęska
     * @return int
     */
    
    public static function create($array)
    {
        // validate category
        if (!isset($array['placename']))
            throw new advertisementsModuleException('No block category selected', 1);
        
        $blockCategory = new adCategory('placename', $array['placename']);
        
        if (!$blockCategory -> exists())
            throw new advertisementsModuleException('Invalid block category selected', 2);
        
        // name
        if (!isset($array['name']) or !trim($array['name']))
            throw new advertisementsModuleException('Name not specified', 3);
        
        $array['name'] = trim($array['name']);
        
        if (!isset($array['htmlcode']) or !$array['htmlcode'])
            throw new advertisementsModuleException('No HTML code specified', 4);
        
        // position
        if (!isset($array['position']))
            $array['position'] = 0;
        
        $array['position'] = intval($array['position']);
        
        // author id
        if (!isset($array['authorid']))
            throw new advertisementsModuleException('Author ID must be specified', 5);
        
        if (isset($array['authorid']) and !is_object($array['authorid']))
            $array['authorid'] = new pantheraUser('id', $array['authorid']);
        
        if (!$array['authorid'] -> exists())
            throw new advertisementsModuleException('Invalid author ID specified', 6);
            
        $array['authorid'] = $array['authorid'] -> id;
        
        if (!isset($array['expires']))
            $array['expires'] = 0;
        else
            $array['expires'] = Tools::userFriendlyStringToDate($array['expires'], 'Y-m-d H:i:s');
        
        if (!$array['expires'] and $array['expires'] !== 0)
            throw new advertisementsModuleException('Invalid input expiration date specified', 7);
            
        if (!isset($array['created']))
            $array['created'] = DB_TIME_NOW;
        
        return parent::create($array);
    }
}

class advertisementsModuleException extends Exception {}
