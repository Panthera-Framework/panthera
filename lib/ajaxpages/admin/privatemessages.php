<?php
/**
  * Private messaging functions and interfaces for Panthera
  *
  * @package Panthera\plugins\privateMessages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

// Remove it if private message is done
$panthera-> template -> display('no_page.tpl');
pa_exit();

$panthera -> addPermission('can_send_pmsg', localize('Can send private messages', 'pmessages'));
$panthera -> locale -> loadDomain('pmessages');
$panthera -> importModule('privatemessages');

$canSendPMessage = getUserRightAttribute($user, 'can_send_pmsg');

/**
  * Send new private message(s)
  *
  * @author Mateusz Warzyński
  */
          
if ($_GET['action'] == 'send_message') {

    $title = filterInput($_POST['title'], 'quotehtml');
    $content = filterInput($_POST['content'], 'quotehtml');
           
    if (isset($_POST['recipient_id'])) {
                    
        $recipient_ = new pantheraUser('id', $_POST['recipient_id']);
               
        if ($recipient_->exists())
            $recipient = $recipient_->login;
        else
            ajax_exit(array('status' => 'failed', 'error' => localize('Cannot get recipient user!', 'pmessages')));
    } else {
        $recipient = $_POST['recipient_login'];
    }
            
    // if we got more than one recipient
    if (strpos($recipient, ',')) {
        $recipients = explode(', ', $recipient);
                
        foreach ($recipients as $r)
        {
            if (strpos($r, 'group:') !== False)
            {
                $r = trim(str_ireplace('group:', '', $r));

                if (!privateMessages::sendToGroup($r, $title, $content))
                    ajax_exit(array('status' => 'failed', 'message' => "Something went wrong with sending messages."));
                
            } elseif(strpos($r, 'user:') !== False) {
                $r = trim(str_ireplace('user:', '', $r));
                $user = new pantheraUser('login', $r);
                
                if ($user->exists())
                {
                    if (!privateMessage::sendMessage($title, $content, $r))
                        ajax_exit(array('status' => 'failed', 'message' => localize('Error while sending message to some recipients!')));
                    
                }
            }
        }
        ajax_exit(array('status' => 'success'));
    
    // if we got one recipient          
    } else {
        if (strpos($recipient, 'group:') !== False) {
            
            $recipient = trim(str_ireplace('group:', '', $recipient));
            $group = new pantheraGroup('name', $recipient);
            $users = $group->findUsers();
            
            if (!privateMessages::sendToGroup($users, $title, $content))
                ajax_exit(array('status' => 'failed', 'message' => "Something went wrong with sending messages."));
            
            ajax_exit(array('status' => 'success'));
        } elseif(strpos($recipient, 'user:') !== False) {
            $recipient = trim(str_ireplace('user:', '', $recipient));
            $user = new pantheraUser('login', $recipient);
               
            if ($user->exists()) {
                if (!privateMessage::sendMessage($title, $content, $recipient))
                    ajax_exit(array('status' => 'failed', 'message' => localize('Error while sending message to some recipients!')));
                    
                ajax_exit(array('status' => 'success'));
            }
        }
    }

    ajax_exit(array('status' => 'failed'));
}

/**
  * Hide (or remove) group of messages
  *
  * @author Mateusz Warzyński
  */
          
if (@$_GET['action'] == 'remove_messages') {
            
    $message = new privateMessage('id', intval($_POST['messageid']));

    if ($message->exists()) {
        if ($message->recipient_id == $panthera->user->id)
                    $interlocutor = $message->sender_id;
        else
                    $interlocutor = $message->recipient_id;

        if (privateMessage::removeGroup($interlocutor, $message->title))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'message' => 'Cannot remove messages!'));
    }
}            
        
/**
  * Hide (or remove) message
  *
  * @author Mateusz Warzyński
  */
          
if (@$_GET['action'] == 'remove_message') {
            
    $message = new privateMessage('id', intval($_GET['messageid']));

    if ($message -> exists()) {
            
        $message->remove();
        $message->save();

        ajax_exit(array('status' => 'success'));
    } else {
        ajax_exit(array('status' => 'failed', 'error' => localize('Message does not exists', 'privatemessages')));
    }
}
        
/**
  * Set 'seen' of message (as true)
  *
  * @author Mateusz Warzyński
  */
          
if ($_GET['action'] == 'seen_message') {
            
    $message = new privateMessage('id', intval($_GET['messageid']));

    if ($message -> exists()) {
        if ($message->seen())
           ajax_exit(array('status' => 'success'));
    }

    ajax_exit(array('status' => 'failed'));
}
        
/**
  * Get conversation between current user and interlocutor
  *
  * @author Mateusz Warzyński
  */
        
if (@$_GET['action'] == 'show_message') {
            
    $getMessage = new privateMessage('id', $_GET['messageid']);

    if ($getMessage -> exists()) {
        $template -> push('message', $getMessage);
                 
        // get ID of interlocutor
        if ($getMessage->recipient_id == $panthera->user->id)
            $interlocutor = $getMessage->sender_id;
        else
            $interlocutor = $getMessage->recipient_id;
    
        $messages = privateMessage::getConversation($interlocutor, $getMessage->title);
                      
        // check if there are removed messages
        $m = array();
        foreach ($messages as $key => $message) {
            if (($message['visibility_recipient'] and $message['recipient_id'] == $panthera->user->id) OR ($message['visibility_sender'] and $message['sender_id'] == $panthera->user->id))
                $m[] = $message;
        } 
                      
        $template -> push('interlocutor', $interlocutor);
    
        if (count($m))
            $template -> push('messages', $m);
                  
    } else {
        ajax_exit(array('status' => 'failed', 'message' => localize('Cannot get messages!', 'pmessages')));
    }

    $titlebar = new uiTitlebar(strval($getMessage->title));
    $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-mail-flag-icon.png', 'left');

    $template -> push('user_id', $panthera->user->id);
    $template -> display('privatemessages_showmessage.tpl');
    pa_exit();
} 
        

/**
  * Select user as recipient in sending window
  *
  * @author Mateusz Warzyński
  * @author Damian Kęska
*/
        
if ($_GET['action'] == 'select') {
    $groups = pantheraGroup::listGroups();
    $groupsTpl = array();

    foreach ($groups as $group) {
        if (isset($_GET['query'])) {
            if (stripos($group->name, $_GET['query']) !== False)
                $groupsTpl[] = array('name' => $group->name);
        } else {
            $groupsTpl[] = array('name' => $group->name);
        }
    }
      
    $w = new whereClause();

    if ($_GET['query']) {
        $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase
        $w -> add( 'AND', 'login', 'LIKE', '%' .$_GET['query']. '%');
        $w -> add( 'OR', 'full_name', 'LIKE', '%' .$_GET['query']. '%');
    }
            
    $usersTotal = getUsers($w, False);
            
    // uiPager
    $panthera -> importModule('admin/ui.pager');
    $uiPager = new uiPager('users', $usersTotal, 10);
    $uiPager -> setActive($_GET['page']);
    $uiPager -> setLinkTemplatesFromConfig('privatemessages_select.tpl');
    $limit = $uiPager -> getPageLimit();

    $users = array();
    $usersData = getUsers($w, $limit[1], $limit[0]);

    foreach ($usersData as $w) {
        // superuser cant be listed, it must be hidden
        if ($w -> acl -> superuser and !$panthera -> user -> acl -> superuser)
            continue;

        $users[] = array(
            'login' => $w->login, 
            'name' => $w->getName(),
            'avatar' => pantheraUrl($w->profile_picture),
        );

    }
            
    $panthera -> importModule('admin/ui.searchbar');
    $panthera -> locale -> loadDomain('search');

    $sBar = new uiSearchbar('uiTop');

    $sBar -> setQuery($_GET['query']);
    $sBar -> setMethod('GET');
    $sBar -> setAddress('?display=privatemessages&cat=admin&action=select');

    $panthera -> template -> push('callback', htmlspecialchars($_GET['callback']));
    $panthera -> template -> push('users', $users);
    $panthera -> template -> push('groups', $groupsTpl);
    $panthera -> template -> display('privatemessages_select.tpl');
    pa_exit();
}

/**
  * Display main privateMessages site
  *
  * @author Mateusz Warzyński
  */

// get messages
$count = privateMessage::getMessages(False, False, 'recipient_id');
$template -> push('messages', privateMessage::getMessages($count, 0, 'recipient_id'));

$titlebar = new uiTitlebar(localize('Private Messages', 'pmessages'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/messages.png', 'left');

$template -> display('privatemessages.tpl');
pa_exit();