<?php
/**
 * Notes module
 * 
 * @package Panthera\modules\notes
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;
  
$panthera = pantheraCore::getInstance();

/**
  * Notes data model
  * 
  * @package Panthera/modules/notes
  * @author Mateusz Warzyński
  */

class note extends pantheraFetchDB
{
    protected $_tableName = 'notes';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
    
    
    /**
      * Get notes
      *
      * @return array 
      * @author Mateusz Warzyński
      */
    
    public static function getNotes($limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
    {
        $panthera = pantheraCore::getInstance();
        
        if (!$panthera->user)
            return False;
        
        return $panthera->db->getRows('notes', $by, $limit, $limitFrom, 'note', $orderBy, $order);
    }
    
    /**
      * Create new note
      *
      * @param string $title 
      * @param string $content
      * @param string $permissions
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function createNote($title, $content) //  $permissions
    {
        $panthera = pantheraCore::getInstance();
        
        if (!$panthera->user)
            return False;
        
        /*$group = new pantheraGroup('name', strval($groupName));
        
        if (!$group->exists())
            return False;*/ 
        
        $array = array('title' => htmlspecialchars($title), 'content' => htmlspecialchars($content), 'owner' => $panthera->user->id);

        if (!$panthera->db->query('INSERT INTO `{$db_prefix}notes` (`id`, `title`, `content`, `owner`, `created`) VALUES (NULL, :title, :content, :owner, NOW());', $array))
            return False;
    }
    
    /**
      * Remove note
      *
      * @return bool 
      * @author Mateusz Warzyński
      */
      
    public function remove()
    {
        if (!$panthera->user)
            return False;
        
        if ($panthera->user->id != $this->owner)
            return False;
        
        if (!$this->panthera->db->query('DELETE FROM `{$db_prefix}notes` WHERE `id` = :id', array('id' => $this->id)))
            return False;
        
        return True;
    }
    
}
