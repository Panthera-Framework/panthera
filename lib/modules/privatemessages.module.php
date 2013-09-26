<?php

/**
  * Private messages module
  * 
  * @package Panthera\modules\privateessages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
  
global $panthera;

/**
 * Private messages data model
 * 
 * @package Panthera/modules/privatemessages
 * @author Mateusz Warzyński
 */

class privateMessage extends pantheraFetchDB
{
    protected $_tableName = 'private_messages';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
    
    /**
      * Move message to other layer
      *
      * @param int $messageId
      * @param string $label 
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function moveToLabel($messageId, $label)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $message = new privateMessage('id', $messageId);
        
        if (!$message->exists())
            return False;
        
        $message -> directory = strval($label);
        $message -> save();
        
        return True;
    }
    
    
    /**
      * Block an user
      *
      * @param int $blockedUserId
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function blockUser($blockedUserId)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $blockedUser = new pantheraUser('id', $blockedUserId);
        
        if (!$blockedUser->exists())
            return False;
        
        $array = array();
        
        if (!$panthera->db->query('INSERT INTO `{$db_prefix}pm_blocked_users` (`id`, `user_id`, `blocked_user_id`) VALUES (NULL, :user, :blocked);', array("user_id" => intval($panthera->user->id), "blocked_user_id" => intval($blockedUser->id))))
            return False;
        
        return True;
    }
    
    /**
      * Unblock an user
      *
      * @param int $id of record
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function unBlockUser($recordId)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        if (!$this->panthera->db->query('DELETE FROM `{$db_prefix}pm_blocked_users` WHERE `id` = :id', array('id' => intval($recordId))))
            return False;
        
        return True;
    }
    
    /**
      * Check blocked users
      * 
      * @return array
      * @author Mateusz Warzyński
      */
    
    public static function getBlockedUsers()
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $SQL = $panthera -> db -> query("SELECT * FROM `{$db_prefix}pm_blocked_users` WHERE `user_id` = :user_id", array('user_id' => intval($panthera->user->id)));        
        return $SQL -> fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
      * Send a private message
      *
      * @param string $title of a private message
      * @param string $content
      * @param string $recipient_login of recipient
      * @return bool 
      * @author Mateusz Warzyński
      */
    
    public static function sendMessage($title, $content, $recipient_login)
    {
        global $panthera;
        
        $recipient = new pantheraUser('login', $recipient_login);
        
        if (!$panthera->user or !$recipient->exists() or !$content)
            return False;
        
        $array = array('title' => htmlspecialchars($title), 'sender' => $panthera->user->full_name, 'sender_id' => $panthera->user->id, 'recipient' => $recipient->full_name, 'recipient_id' => intval($recipient->id), 'content' => htmlspecialchars($content));

        if (!$panthera->db->query('INSERT INTO `{$db_prefix}private_messages` (`id`, `title`, `sender`, `sender_id`, `recipient`, `recipient_id`, `content`, `sent`, `visibility_sender`, `visibility_recipient`, `seen`) VALUES (NULL, :title, :sender, :sender_id, :recipient, :recipient_id, :content, NOW(), 1, 1, 0);', $array))
            return False;
        
        return True;
    }
    
    /**
      * Get private messages (or amount of them)
      *
      * @param int $limit of messages
      * @param int $limitFrom of messages 
      * @param string $orderBy
      * @param string $order (desc/asc) 
      * @return array|bool
      * @author Mateusz Warzyński
      */
    
    public static function getMessages($limit=0, $limitFrom=0, $orderBy='sent', $order='DESC')
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $where = new whereClause;
        $where -> add('AND', 'recipient_id', '=', $panthera->user->id);
        $where -> add('OR', 'sender_id', '=', $panthera->user->id);
        $messages = $panthera->db->getRows('private_messages', $where, $limit, $limitFrom, '', $orderBy, $order);
        
        // return amount of messages
        if ($limit === False and $limitFrom === False) {
            return $messages;
        }
        
        // parse messages
        $m = array();
        foreach ($messages as $key => $message)
        {
            if (($message['visibility_recipient'] and $message['recipient_id'] == $panthera->user->id) or ($message['visibility_sender'] and $message['sender_id'] == $panthera->user->id)) {
                
                if ($message['sender_id'] == $panthera->user->id)
                    $interlocutor = $message['recipient'];
                else
                    $interlocutor = $message['sender'];
                
                $name = $message['title'].$interlocutor;
                
                // check if title of message exists in array (have been parsed earlier) 
                if (array_key_exists($name, $m)) {
                    $m[$name]['count'] = $m[$name]['count']+1;
                    
                } else {
                    $m[$name] = $message;
                    $m[$name]['interlocutor'] = $interlocutor;
                    $m[$name]['count'] = 1;
                }
                
                // check if user has seen message
                if (!$message['seen'] and $message['recipient_id'] == $panthera->user->id)
                    $m[$name]['seen'] = 0;
                else
                    $m[$name]['seen'] = 1;
                
                // get sent time 
                $m[$name]['sent'] = elapsedTime($message['sent']);
                
                // set actual ID
                $m[$name]['id'] = $message['id'];
            }
        }
        
        return $m;
    }
    
    /**
      * Send messages to group
      *
      * @param array $group of users
      * @param string $title of messages
      * @param string $content of messages  
      * @return array|bool
      * @author Mateusz Warzyński
      */
      
    public static function sendToGroup($groupName, $title, $content)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $group = new pantheraGroup('name', strval($groupName));
        
        if (!$group->exists())
            return True;

        $users = $group->findUsers();
        
        if (count($users)) {
            foreach ($users as $number => $user) {
                if (!self::sendMessage($title, $content, $user['login']))
                    return False;
            }
        }
        
        return True;
    } 
    
    /**
      * Get conversation between two users
      *
      * @param int $interlocutor
      * @param string $title of messages 
      * @return array|bool
      * @author Mateusz Warzyński
      */
    
    public static function getConversation($interlocutor, $title)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        $panthera -> logging -> output('Get conversation with interlocutor='.$interlocutor, 'pmessages');
        
        $SQL = $panthera -> db -> query("SELECT * FROM `{$db_prefix}private_messages` WHERE `title` = :title AND ((`recipient_id` = :interlocutor AND `sender_id` = :user_id) OR (`recipient_id` = :user_id AND `sender_id` = :interlocutor))", array('interlocutor' => $interlocutor, 'user_id' => $panthera->user->id, 'title' => $title));
        return $SQL -> fetchAll(PDO::FETCH_ASSOC);
    }

    /**
      * Remove group of messages
      *
      * @param int $interlocutor id
      * @param string $title of messages 
      * @return array|bool
      * @author Mateusz Warzyński
      */

    public static function removeGroup($interlocutor, $title)
    {
        global $panthera;
        
        if (!$panthera->user)
            return False;
        
        // get array with messages
        $SQL = $panthera -> db -> query("SELECT * FROM `{$db_prefix}private_messages` WHERE `title` = :title AND ((`recipient_id` = :interlocutor AND `sender_id` = :user_id) OR (`recipient_id` = :user_id AND `sender_id` = :interlocutor))", array('interlocutor' => $interlocutor, 'user_id' => $panthera->user->id, 'title' => $title));
        $messages = $SQL -> fetchAll(PDO::FETCH_ASSOC);
        
        // check if thera are any messages
        if (count($messages)) {
            foreach ($messages as $key => $message)
            {
                // remove message
                $remove = new privateMessage('id', intval($message['id']));
                $remove->remove();
                $remove->save();
            }
        } else {
            return False;
        }

        return True;
    }
    
    /**
      * Change visiblity of (or remove) message
      *
      * @return bool
      * @author Mateusz Warzyński
      */
    
    public function remove()
    {
        // Set visibility for current user to False
        if ($this->recipient_id == $this->panthera->user->id)
            $this->visibility_recipient = 0;
        
        elseif ($this->sender_id == $this->panthera->user->id)
            $this->visibility_sender = 0;
        
        else
            return False;
        
        
        // Remove message if pm.remove is True and recipient removed this message
        if (!$this->visibility_recipient and !$this->visibiliy_sender and $this->panthera->config->getKey('pm.remove', 1, 'bool', 'pm')) {
            if (!$this->panthera->db->query('DELETE FROM `{$db_prefix}private_messages` WHERE `id` = :id', array('id' => $this->id)))
                return False;
        }
        
        return True;
    }
    
    /**
      * Seen message
      *
      * @return bool
      * @author Mateusz Warzyński
      */
    
    public function seen()
    {
        if (!$this->seen and $this->recipient_id == $this->panthera->user->id) {
            
            // Change value of `seen` (user displayed message) and save it
            $this -> seen = 1;
            $this -> save();
            return True;
        }
        
        return False;
    }
} 