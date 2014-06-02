<?php
/**
 * Comments  - comments system management module
 * 
 * @package Panthera\core\components\comments
 * @author Mateusz Warzyński
 * @license GNU Affero General Public License 3, see license.txt
 */
  
/**
 * Comment item class - allows view and edit of single items
 *
 * @implements pantheraFetchDB
 * @package Panthera\core\components\comments 
 * @author Mateusz Warzyński
 */

class userComment extends pantheraFetchDB
{
    protected $_tableName = 'users_comments';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
    
    public $author_login;
    
    
    /**
      * Get comments from database
      *
      * @param string $sBarQuery, search bar query
      * @param string $group, eg. "custompage", "blogpost"
      * @param int $objectID group id, eg. custompage id or quick message id
      * @param int $limit
      * @param int $limitFrom
      * @param string $orderBy, eg. 'id'
      * @param string $orderDirection, 'DESC'/'ASC' 
      * @return mixed
      * @author Mateusz Warzyński
      */
    
    public static function fetchComments($w, $limit=0, $limitFrom=0, $orderBy='id', $orderDirection='DESC', $total=False)
    {
        $comments = static::fetchAll($w, $limit, $limitFrom, $orderBy, $orderDirection);
        
        if ($total)
            return $comments;
        
        // get information about user
        foreach ($comments as $comment)
        {
            $comment->content = htmlspecialchars_decode($comment->content);
            
            $user = new pantheraUser('id', $comment->author_id);
            $comment->author_login = $user->login;
        }
        
        return $comments;
    }
    
    
    
    /**
      * Post comment, add record to database
      *
      * @param string $content
      * @param int $userId 
      * @param string $group, group of page where comment is added, eg. 'custompage'
      * @param int $objectID, ID of group object, eg. 5
      * @param int $allowed, if should be moderated
      * @return mixed
      * @author Mateusz Warzyński
      */
    
    public static function postComment($content, $userId, $group, $objectID, $allowed=1)
    {
        $panthera = pantheraCore::getInstance();
        
        if (strlen($content) > 5500)
        return False;
        
        if ($userId == false or $userId == null) {
            $user = -1;   
        } else {
            $author = new pantheraUser('id', $userId);

            if ($author->exists())
                $user = $author->id;
            else
                return False;   
        }
        
        if (strlen($content) < 5)
            return False;
        
        if (strlen($group) < 3)
            return False;
        
        if (!isset($objectID))
            return False;
            
        $array = array(
            'content' => filterInput($content, "quotehtml,quotes,wysiwyg"),
            'userid' => $user,
            'group' => $group,
            'objectID' => $objectID,
            'allowed' => (bool)$allowed
        );
        
        $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}users_comments` (`id`, `content`, `author_id`, `group`, `object_id`, `posted`, `modified`, `allowed`) VALUES (NULL, :content, :userid, :group, :objectID, NOW(), NOW(), :allowed);', $array);
    }
    
    
    
    /**
      * Delete comments by id
      *
      * @param array $id, ids of comments to delete, eg. {1, 2, 3, 4, 5}
      * @return bool
      * @author Mateusz Warzyński
      */
    
    public static function deleteComments($id)
    {
        $panthera = pantheraCore::getInstance();
        
        $where = new whereClause;
        
        foreach ($id as $key => $i)
            $where -> add('OR', 'id', '=', strval($i));   
        
        $show = $where->show();
        $query = 'DELETE FROM `{$db_prefix}users_comments` WHERE ' .$show[0];
        
        $SQL = $panthera -> db -> query($query, $show[1]);
        return (bool)$SQL->rowCount();
    }
    
    
    
    /**
      * Hold comments
      *
      * @param array $id, ids of comments to delete, eg. {1, 2, 3, 4, 5}
      * @return bool
      * @author Mateusz Warzyński
      */
    
    public static function holdComments($id)
    {
        foreach ($id as $i)
        {
            $comment = new userComment('id', $i);
            $comment->allowed = !(bool)$comment->allowed;
            $comment -> save();
        }

        return True;
    }
}