<?php
/**
  * Newsletter module with support for multiple protocols like e-mail (smtp), jabber etc.
  *
  * @package Panthera\modules\custompages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
/**
  * Panthera fetch DB based wrapper for custom_pages table in database
  * @package Panthera\modules\custompages
  * @author Damian Kęska
  */
  
class customPage extends pantheraFetchDB
{
    protected $_tableName = 'custom_pages';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'url_id', 'unique', 'array');

    /**
      * Return columns from database with parsed Panthera URLS
      *
      * @param string $var Variable name
      * @return mixed 
      * @author Damian Kęska
      */

    public function __get($var)
    {
        if ($var == 'html')
            return pantheraUrl(parent::__get($var));

        return parent::__get($var);
    }
    
    /**
      * Set column's value to database and convert automaticaly Panthera URLS
      *
      * @param string $var Variable name
      * @param string $value Value
      * @return mixed 
      * @author Damian Kęska
      */

    public function __set($var, $value)
    {
        if ($var == 'html')
            return parent::__set($var, pantheraUrl($value, True));

       return parent::__set($var, $value);

    }
    
    /**
     * Get all custom pages from `{$db_prefix}_custom_pages` matching criteries specified in parameters
     *
     * @return array
     * @author Mateusz Warzyński
     */

    public static function fetch($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
    {
          global $panthera;
          return $panthera->db->getRows('custom_pages', $by, $limit, $limitFrom, 'customPage', $orderBy, $order);
    }
    
    /**
     * Create custom page
     *
     * @return bool
     * @author Mateusz Warzyński
     */

    public static function create($title, $language, $author_name, $author_id, $unique, $url_id)
    {
        global $panthera;
        $array = array('unique' => $unique, 'url_id' => $url_id, 'title' => $title, 'meta_tags' => '', 'html' => '', 'author_name' => $author_name, 'author_id' => $author_id, 'language' => $language, 'mod_author_name' => $author_name, 'mod_author_id' => $author_id);

        $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}custom_pages` (`id`, `unique`, `url_id`, `title`, `meta_tags`, `html`, `author_name`, `author_id`, `language`, `created`, `mod_author_name`, `mod_author_id`, `mod_time`) VALUES (NULL, :unique, :url_id, :title, :meta_tags, :html, :author_name, :author_id, :language, NOW(), :mod_author_name, :mod_author_id, NOW());', $array);

        if ($SQL)
          return True;

        return False;
    }
    
    /**
      * Remove selected custom pages from database
      *
      * @param array $where List of columns and values to put in where clause of sql query
      * @return bool 
      * @author Damian Kęska
      */

    public static function remove($where)
    {
        global $panthera;
        $dbSet = $panthera->db->dbSet($where, $sep = " AND ");
        $SQL = $panthera -> db -> query('DELETE FROM `{$db_prefix}custom_pages` WHERE ' .$dbSet[0], $dbSet[1]);
        return (bool)$SQL->rowCount();
    }
    
    /**
     * Simply remove custom page by `id`. Returns True if any row was affected
     *
     * @return bool
     * @author Mateusz Warzyński
     */

    public static function removeById($id)
    {
        global $panthera;
        $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}custom_pages` WHERE `id` = :id', array('id' => $id));

        if ($SQL)
            return True;

        return False;
    }
    
    /**
      * Get custom page by unique id and language
      *
      * @param string $field
      * @param string $value
      * @param string $language
      * @param bool $languageFallback - fallback, forceNative
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function getBy($field, $value, $language='', $languageFallback='fallback')
    {
        global $panthera;
        $panthera -> importModule('meta');
        
        // if not specified language it will be taken from active session
        if ($language == '')
            $language = $panthera -> locale -> getActive();
            
        if (meta::get('var', 'cp_gen_' .$value))
            $language = 'all';
            
        $statement = new whereClause();
        $statement -> add('', $field, '=', $value);
        $statement -> add('AND', 'language', '=', $language);

        $cpage = new customPage($statement, '');

        if (!$cpage -> exists() and $languageFallback == 'fallback')
        {
            // try to get page in other language
            if (!$cpage -> exists())
                $cpage = new customPage($field, $value);
        }
        
        // force native language
        if($cpage->language != $language and $languageFallback == 'forceNative')
        {
            $statement = new whereClause();
            $statement -> add('', 'unique', '=', $cpage->unique);
            $statement -> add('AND', 'language', '=', $language);
            $cpage = new customPage($statement, '');
        }
        
        return $cpage;
    }
}





