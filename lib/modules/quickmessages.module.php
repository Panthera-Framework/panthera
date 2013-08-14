<?php
/**
  * Quick messages module
  * 
  * @package Panthera\modules\messages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
global $panthera;

/**
  * Quick messsages data model
  *
  * @package Panthera\modules\messages
  * @author Damian Kęska
  */

class quickMessage extends pantheraFetchDB
{
    protected $_tableName = 'quick_messages';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'url_id', 'unique', 'array');

    public function __get($var)
    {
        if ($var == 'message' or $var == 'icon')
            return pantheraUrl(parent::__get($var));

        return parent::__get($var);
    }

    public function __set($var, $value)
    {
        if ($var == 'message' or $var == 'icon')
            return parent::__set($var, pantheraUrl($value, True));

       return parent::__set($var, $value);
    }
    
    /**
      * Get author name
      *
      * @param bool $modificationAuthor
      * @return string 
      * @author Damian Kęska
      */
    
    public function getAuthorName($modificationAuthor=False)
    {
        if ($modificationAuthor == True)
        {
            if ($this->__get('mod_author_full_name'))
                return $this->__get('mod_author_full_name');
                
            return $this->__get('mod_author_login');
        }
    
        if ($this->__get('author_full_name'))
            return $this->__get('author_full_name');
            
        return $this->__get('author_login');
    }
    
    /**
      * Increase view count
      *
      * @param int $count
      * @return mixed 
      * @author Damian Kęska
      */
    
    public function increaseViewcount($count=1)
    {
        return $this->viewcount += $count;
    }
    
    /**
      * Check if message is visible
      *
      * @return bool 
      * @author Damian Kęska
      */
    
    public function isVisible()
    {
        return (bool)intval($this->__get('visibility'));
    }
    
    /**
     * Get all quick messages from `{$db_prefix}_quick_messages` matching criteries specified in parameters
     *
     * @return array
     * @author Damian Kęska
     */

    public static function getQuickMessages($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
    {
          global $panthera;
          return $panthera->db->getRows('quick_messages', $by, $limit, $limitFrom, 'quickMessage', $orderBy, $order);
    }
    
    /**
      * Get modification time
      *
      * @return string 
      * @author Damian Kęska
      */
    
    public function getTimestamp()
    {
        return $this->__get('mod_time');
    }
    
    /**
     * Create quick message
     *
     * @return void
     * @author Mateusz Warzyński
     */

    public static function create($title, $content, $login, $full_name, $url_id, $language, $category, $visibility=0, $icon='')
    {
        global $panthera;
        $array = array('unique' => md5(rand(1,500).$title), 'title' => $title, 'message' => $content, 'author_login' => $login, 'author_full_name' => $full_name, 'visibility' => $visibility, 'mod_author_login' => $login, 'mod_author_full_name' => $full_name, 'url_id' => $url_id, 'language' => $language, 'category_name' => $category, 'icon' => $icon);

        $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}quick_messages` (`id`, `unique`, `title`, `message`, `author_login`, `author_full_name`, `mod_time`, `visibility`, `mod_author_login`, `mod_author_full_name`, `url_id`, `language`, `category_name`, `icon`) VALUES (NULL, :unique, :title, :message, :author_login, :author_full_name, NOW(), :visibility, :mod_author_login, :mod_author_full_name, :url_id, :language, :category_name, :icon);', $array);
    }
    
    /**
     * Simply remove quick message by `id`. Returns True if any row was affected
     *
     * @return bool
     * @author Damian Kęska
     */

    function remove($id)
    {
        global $panthera;
        
        // clear cache first
        if ($panthera->cache)
        {
            $qmsg = new quickMessage('id', $id);
            $qmsg -> clearCache();
            unset($qmsg);
        }
        
        $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}quick_messages` WHERE `id` = :id', array('id' => $id));

        if ($SQL)
            return True;

        return False;
    }
}


/**
  * Quick messsages category data model
  *
  * @package Panthera\modules\messages
  * @author Damian Kęska
  */

class quickCategory extends pantheraFetchDB
{
    protected $_tableName = 'qmsg_categories';
    protected $_idColumn = 'category_id';
    protected $_constructBy = array('category_id', 'id', 'category_name', 'array'); // `id` because its a synonym to `category_id` - see __construct of pantheraFetchDB
    
    /**
     * Get all categories of quick messages from `{$db_prefix}_qmsg_categories` matching criteries specified in parameters
     *
     * @return array
     * @author Damian Kęska
     */

    public static function getCategories($by, $limit=0, $limitFrom=0, $orderBy='category_id', $order='DESC')
    {
          global $panthera;
          return $panthera->db->getRows('qmsg_categories', $by, $limit, $limitFrom, '', $orderBy, $order);
    }
}
