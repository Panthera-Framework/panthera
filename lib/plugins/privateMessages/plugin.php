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

        // Send new private message
        if ($_GET['action'] == 'send_message') {
            // filter input values
            $title= filterInput($title, 'quotehtml');

            $recipient = @$_POST['recipient_login'];
            
            if (isset($_POST['recipient_id'])) {
                    
                $recipient_ = new pantheraUser('id', $_POST['recipient_id']);
                
                if ($recipient_->exists())
                    $recipient = $recipient_->login;
                else
                    ajax_exit(array('status' => 'failed', 'error' => localize('Cannot get recipient user!', 'pmessages')));
            }

            // send new message
            if (privateMessage::sendMessage($_POST['title'], $_POST['content'], $recipient))
                  ajax_exit(array('status' => 'success'));
            
            ajax_exit(array('status' => 'failed'));
        }

        // hide group of messages or remove them if recipient hide them
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
        
        // hide message or remove it if recipient hide it
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
        
        // seen message
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

        /** END OF Ajax-HTML PAGES **/


        // Display main privateMessages site
        
        // get messages
        $count = privateMessage::getMessages(False, False, 'recipient_id');
        $messages = privateMessage::getMessages($count, 0, 'recipient_id');
        
        // parse messages
        $m = array();
        foreach ($messages as $key => $message)
        {
            // check if user didn't remove message
            if (($message['visibility_recipient'] and $message['recipient_id'] == $panthera->user->id) or ($message['visibility_sender'] and $message['sender_id'] == $panthera->user->id)) {
                    
                // check if title of message exists in array (have been parsed earlier) 
                if (array_key_exists($message['title'], $m)) {
                    
                    // raise count
                    $m[$message['title']]['count'] = $m[$message['title']]['count']+1;
                    
                } else {
                    
                    $m[$message['title']] = $message;
                    
                    // get interlocutor
                    if ($message['sender_id'] == $panthera->user->id)
                        $m[$message['title']]['interlocutor'] = $message['recipient'];
                    else
                        $m[$message['title']]['interlocutor'] = $message['sender'];
                    
                    $m[$message['title']]['count'] = 1;
                }
                
                // check if user has seen message
                if (!$message['seen'] and $message['recipient_id'] == $panthera->user->id)
                    $m[$message['title']]['seen'] = 0;
                else
                    $m[$message['title']]['seen'] = 1;
                
                // get sent time 
                $m[$message['title']]['sent'] = elapsedTime($message['sent']);
                
                // set actual ID
                $m[$message['title']]['id'] = $message['id'];
            }
        }
        $template -> push('messages', $m);
        
        // clear memory
        unset($m);
        
        $titlebar = new uiTitlebar(localize('Private Messages', 'pmessages'));
        $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/messages.png', 'left');

        $template -> display('privatemessages.tpl');
        pa_exit();
    }
}

// add privateMessages plugin to index list
function privateMessagesToAjaxList($list)
{
    $list[] = array('location' => 'plugins', 'name' => 'privateMessages', 'link' => '?display=privatemessages');

    return $list;
}

// Add 'privatemessages' item to admin menu
function pMessagesToAdminMenu($menu) { $menu -> add('privatemessages', 'Private messages', '?display=privatemessages&cat=admin', '', '{$PANTHERA_URL}/images/admin/menu/Actions-mail-flag-icon.png', ''); }
$panthera -> add_option('admin_menu', 'pMessagesToAdminMenu');

$panthera -> add_option('ajaxpages_list', 'privateMessagesToAjaxList');
$panthera -> add_option('ajax_page', 'pMessagesAjax');
?>
