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
      * Get private messages
      *
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
        
        return $panthera->db->getRows('private_messages', $where, $limit, $limitFrom, '', $orderBy, $order);
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
        
        $SQL = $panthera -> db -> query("SELECT * FROM `pa_private_messages` WHERE `title` = :title AND ((`recipient_id` = :interlocutor AND `sender_id` = :user_id) OR (`recipient_id` = :user_id AND `sender_id` = :interlocutor))", array('interlocutor' => $interlocutor, 'user_id' => $panthera->user->id, 'title' => $title));
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
        
        $user2 = new pantheraUser('id', $interlocutor);
        
        // check if interlocutor exists
        if (!$user2->exists())
            return False;
        
        // get array with messages
        $SQL = $panthera -> db -> query("SELECT * FROM `pa_private_messages` WHERE `title` = :title AND ((`recipient_id` = :interlocutor AND `sender_id` = :user_id) OR (`recipient_id` = :user_id AND `sender_id` = :interlocutor))", array('interlocutor' => $user2->id, 'user_id' => $panthera->user->id, 'title' => $title));
        $messages = $SQL -> fetchAll(PDO::FETCH_ASSOC);
        
        // check if thera are any messages
        if (count($messages)) {
            foreach ($messages as $key => $message)
            {
                // remove message
                if ((!$message['visibility_sender'] and $message['sender_id'] == $panthera->user->id) OR (!$message['visibility_recipient'] and $message['recipient_id'] == $panthera->user->id)) {
                    $remove = new privateMessage('id', intval($message['id']));
                    $remove->remove();
                    $remove->save();
                }
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