<?php
/**
 * Comments  - comments system management module
 *
 * @package Panthera\core\components\comments
 * @author Mateusz Warzyński
 * @license LGPLv3
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
    use userModelExtension;
    
    protected $_tableName = 'users_comments';
    protected $_idColumn = 'id';
    protected $_constructBy = array(
        'id', 'array',
    );
    
    /**
     * Fetch comments for specified objects group
     * 
     * Example:
     * <code>
     * $whereClause = new whereClause;
     * $whereClause -> add('AND', 'articleID', '=', 5);
     * $comments = userComment::fetchComments('articles', $whereClause, 5, 0, 'ASC'); // @see pantheraFetchDB::fetchAll() args
     * </code>
     * 
     * @see pantheraFetchDB::fetchAll()
     * @args pantheraFetchDB::fetchAll()
     * @return array
     */
    
    public static function fetchComments()
    {
        $args = func_get_args();
        
        if (!isset($args[1]) || !is_object($args[1]))
            $args[1] = new whereClause;
        
        // empty string as first argument will tell us that we want to explore comments from all modules
        if ($args[0])
	    $args[1] -> add('AND', 'group', '=', $args[0]);
        
        // remove first additional argument
        array_shift($args);
        
        return call_user_func_array('parent::fetchAll', $args);
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

        if ($userId === false or $userId === null) {
            $user = -1;
        } else {
            $author = new pantheraUser('id', $userId);

            if ($author->exists())
                $user = $author->id;
            else
                return False;
        }

        if (strlen($content) < 5 || strlen($group) < 3 || isset($objectID))
            return False;

        $array = array(
            'content' => filterInput($content, "quotehtml,quotes,wysiwyg"),
            'userid' => $user,
            'group' => $group,
            'objectID' => $objectID,
            'allowed' => (bool)$allowed,
            'posted' => DB_TIME_NOW,
            'modified' => DB_TIME_NOW,
        );

        return self::create($array);
    }



    /**
     * Delete comments by id
     *
     * @param array $id Ids of comments to delete, eg. {1, 2, 3, 4, 5}
     * @return bool
     * @author Mateusz Warzyński
     */

    public static function deleteComments($id)
    {
        $panthera = pantheraCore::getInstance();

        if (is_numeric($id))
            $id = array($id);

        foreach ($id as $key => $value)
        {
            $object = new static('id', $value);
            $object -> delete();
        }
        
        return true;
    }

    /**
     * Hold comments
     *
     * @param array|string|int $id Ids of comments to delete, eg. {1, 2, 3, 4, 5}. Can be also a numeric string or number eg. "1", 1, "50"
     * @return bool
     * @author Mateusz Warzyński
     */

    public static function holdComments($id, $value=True)
    {
        if (is_numeric($id))
            $id = array($id);
        
        foreach ($id as $i)
        {
            $comment = new userComment('id', $i);
            $comment -> allowed = (bool)$value;
            $comment -> save();
        }

        return True;
    }
}