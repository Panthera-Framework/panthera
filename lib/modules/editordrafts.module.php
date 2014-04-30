<?php
/**
  * Editor drafts (saved messages avaliable to paste)
  *
  * @package Panthera\modules\editordrafts
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

/**
  * Newsletter drafts management
  *
  * @package Panthera\modules\messages
  * @author Damian Kęska
  */

class editorDraft extends pantheraFetchDB
{
    protected $_tableName = 'editor_drafts';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array', 'textid');
    
    /**
      * Strip HTML tags, trim spaces and hash content to get more accurate textid
      *
      * @param string $content
      * @return string 
      * @author Damian Kęska
      */
    
    public static function getHash($content)
    {
        $content = str_replace("\n", '', $content); // strip all new line tags
        $content = str_replace("\r", '', $content);
        $content = str_replace(' ', '', $content);
        $content = str_replace('&nbsp;', '', $content);
    
        return hash('md4', strip_tags($content));
    }
    
    /**
      * Remove a draft
      *
      * @param int|string $id Draft ID or textid
      * @return bool
      * @author Damian Kęska
      */
    
    public static function removeDraft($id)
    {
        $panthera = pantheraCore::getInstance();
    
        if (is_numeric($id))
        {
            $field = 'id';
        } else {
            $field = 'textid';
        }
    
        $draft = new editorDraft($field, $id);
        
        if (!$draft->exists())
        {
            return True;
        }
        
        $id = $draft -> id;
        
        $panthera -> db -> query ('DELETE FROM `{$db_prefix}editor_drafts` WHERE `id` = :id', array('id' => $id));
        return True;
    }
    
    /**
      * Create a new draft or update existing with same content
      *
      * @param string $content
      * @param int|string|pantheraUser $user
      * @return mixed 
      * @author Damian Kęska
      */
    
    public static function createDraft($content, $user='', $id='')
    {
        $panthera = pantheraCore::getInstance();

        if (!is_numeric($user))
        {
            $user = pantheraUser::getAttribute('id', $user);
        }
        
        if (!$user)
        {
            $user = -1; // nobody
        }
        
        $contentHash = self::getHash($content);
        
        if ($id)
        {
            $draft = new editorDraft('id', $id);
        } else {
            $draft = new editorDraft('textid', $contentHash);
        }

        // don't create new draft if there is already one with similar content
        if ($draft->exists())
        {
            if ($draft -> author_id == $user)
            {
                $draft -> content = $content;
                $draft -> save();
                return True;
            }
        }

        $data = array(
            'id' => 'null',
            'directory' => 'newsletter',
            'author_id' => $user,
            'content' => $content,
            'date' => DB_TIME_NOW,
            'textid' => $contentHash
        );
        
        $prepared = $panthera -> db -> buildInsertString($data, False, 'editor_drafts');
        $SQL = $panthera -> db -> query ($prepared['query'], $prepared['values']);
        
        return True;
    }


    /**
      * Fetch all drafts
      *
      * @param array|object $by
      * @param int $limit
      * @param int $limitFrom
      * @param string $orderBy
      * @param string $order DESC/ASC
      * @return array 
      * @author Damian Kęska
      */
      
    public static function fetch($by, $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
    {
          $panthera = pantheraCore::getInstance();
          return $panthera->db->getRows('editor_drafts', $by, $limit, $limitFrom, '', $orderBy, $order);
    }
    
    /**
      * Fetch all user's drafts
      *
      * @param string|int $user Login or ID (id would be better for performance reasons)
      * @param int $limit
      * @param int $limitFrom
      * @param string $orderBy
      * @param string $order DESC/ASC
      * @return array 
      * @author Damian Kęska
      */
    
    public static function fetchByUser($user, $directory='', $limit=0, $limitFrom=0, $orderBy='id', $order='DESC')
    {
        if (is_string($user))
        {
            $u = new pantheraUser('login', $user);
            
            if ($u->exists)
            {
                $user = $u->id;
            }
        }
        
        $by = array('author_id' => $user);
        
        if ($directory)
        {
            $by['directory'] = $directory;
        }
    
        return self::fetch($by, $limit, $limitFrom, $orderBy, $order);
    }
}
