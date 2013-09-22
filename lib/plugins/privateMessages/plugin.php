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
  
// register plugin
$pluginInfo = array('name' => 'Private messaging', 'author' => 'Mateusz Warzyński', 'description' => 'A little bit mail system for Panthera Framework', 'version' => PANTHERA_VERSION);

$panthera -> addPermission('can_send_pmsg', localize('Can send private messages', 'pmessages'));

$panthera -> importModule('privatemessages');

// display content and do actions
function pMessagesAjax()
{
    global $panthera, $user, $template;

    // display private messages content
    if ($_GET['display'] == 'privatemessages')
    {

        // check user permissions
        if (!getUserRightAttribute($user, 'can_send_pmsg'))
        {
            print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
            pa_exit();
        }

        /** JSON PAGES **/

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
            if (strpos($recipient, ','))
            {
                $recipients = explode(', ', $recipient);
                
                foreach ($recipients as $r)
                {
                    if (!privateMessage::sendMessage($title, $content, $r))
                        ajax_exit(array('status' => 'failed', 'message' => localize('Error while sending message to some recipients!')));
                }

                ajax_exit(array('status' => 'success'));
                
            } else {
                if (privateMessage::sendMessage($title, $content, $recipient))
                    ajax_exit(array('status' => 'success'));
                else
                    ajax_exit(array('status' => 'failed', 'message' => "Cannot send a message"));
            }
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

            if ($message -> exists())
            {
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

        /** END OF JSON PAGES **/

        
        /** Ajax-HTML PAGES **/
        
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
            $panthera -> template -> display('privatemessages_select.tpl');
            pa_exit();
        }

        /** END OF Ajax-HTML PAGES **/

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
    }
}



/**
  * Add privateMessages plugin to index list
  *
  * @author Mateusz Warzyński
  */

function privateMessagesToAjaxList($list)
{
    $list[] = array('location' => 'plugins', 'name' => 'privateMessages', 'link' => '?display=privatemessages');

    return $list;
}


/**
  * Add 'privatemessages' item to admin menu
  *
  * @author Mateusz Warzyński
  */

function pMessagesToAdminMenu($menu) { $menu -> add('privatemessages', 'Private messages', '?display=privatemessages&cat=admin', '', '{$PANTHERA_URL}/images/admin/menu/Actions-mail-flag-icon.png', ''); }
$panthera -> add_option('admin_menu', 'pMessagesToAdminMenu');

$panthera -> add_option('ajaxpages_list', 'privateMessagesToAjaxList');
$panthera -> add_option('ajax_page', 'pMessagesAjax');
?>
