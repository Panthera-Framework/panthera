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

            // send new message
            if (privateMessage::sendMessage($_POST['title'], $_POST['content'], $_POST['recipient_login']))
                  ajax_exit(array('status' => 'success'));
            
            ajax_exit(array('status' => 'failed'));
        }


        // hide message sent by user or remove it if recipient hide it
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

        if (@$_GET['action'] == 'new_message') {
            $titlebar = new uiTitlebar(localize('Private Messages', 'pmessages')." - ".localize('Send message', 'pmessages'));
            $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/messages.png', 'left');
            
            $template -> display('privatemessages_newmessage.tpl');
            pa_exit();
        }

        if (@$_GET['action'] == 'show_message') {

            $message = new privateMessage('id', $_GET['messageid']);
            if ($message -> exists()) {
                  $template -> push('message', $message);
                  $template -> push('message_content', nl2br($message->content));
            } else {
                  ajax_exit(array('status' => 'failed'));
            }

            $template -> display('privatemessages_showmessage.tpl');
            pa_exit();
        }

        /** END OF Ajax-HTML PAGES **/


        // Displaying main privateMessages site

        // get messages by recipient_id
        $count = privateMessage::getMessages(array('recipient_id' => $user->id), False, False, 'recipient_id');
        $received = privateMessage::getMessages(array('recipient_id' => $user->id), $count, 0, 'recipient_id');
        
        // check if user didn't remove message
        foreach ($received as $key => $message)
        {
            if ($message['visibility_recipient'])
                $m[] = $message;
        }
        
        $template -> push('received', $m);
        
        // clear memory
        unset($m);

        // get messages by sender_id
        $count = privateMessage::getMessages(array('sender_id' => $user->id), False);
        $sent = privateMessage::getMessages(array('sender_id' => $user->id), $count, 0);
        
        // check if user didn't remove message
        foreach ($sent as $key => $message)
        {
            if ($message['visibility_recipient'])
                $m[] = $message;
        }
        
        $template -> push('sent', $m);
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
