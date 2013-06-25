<?php
/**
  * Private messaging functions and interfaces for Panthera
  *
  * @package Panthera\plugins\privateMessages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

// register plugin
$pluginInfo = array('name' => 'Private messaging', 'author' => 'Mateusz Warzyński', 'description' => 'A little bit mail system for Panthera Framework', 'version' => PANTHERA_VERSION);

$panthera -> addPermission('can_send_pmsg', localize('Can send private messages', 'pmessages'));

/**
 * Get user from users by id or login
 *
 * @return object
 * @author Mateusz Warzyński
 */

class getUser extends pantheraFetchDB
{
    protected $_tableName = 'users';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'login');
}

/**
 * Get columns from item in privateMessages by id
 *
 * @return object
 * @author Mateusz Warzyński
 */

class privateMessage extends pantheraFetchDB
{
    protected $_tableName = 'private_messages';
    protected $_idColumn = 'id';
    protected $_constructBy = array('id', 'array');
}


/**
 * Send private message
 *
 * @return void
 * @author Mateusz Warzyński
 */

function sendPrivateMessage($title, $sender, $sender_id, $recipient, $recipient_id, $content)
{
    global $panthera;
    $array = array('title' => $title, 'sender' => $sender, 'sender_id' => $sender_id, 'recipient' => $recipient, 'recipient_id' => $recipient_id, 'content' => $content);

    $SQL = $panthera->db->query('INSERT INTO `{$db_prefix}private_messages` (`id`, `title`, `sender`, `sender_id`, `recipient`, `recipient_id`, `content`, `sent`, `visibility_sender`, `visibility_recipient`) VALUES (NULL, :title, :sender, :sender_id, :recipient, :recipient_id, :content, NOW(), 1, 1);', $array);
    if ($SQL)
        return True;
    return False;
}

/**
 * Get all private messages from `{$db_prefix}private_messages` matching criteries specified in parameters
 *
 * @return array
 * @author Mateusz Warzyński
 */

function getPrivateMessages($by, $limit=0, $limitFrom=0)
{
      global $panthera;
      return $panthera->db->getRows('private_messages', $by, $limit, $limitFrom, 'privateMessage');
}

/**
 * Remove private message from `{$db_prefix}private_messages` by id
 *
 * @return bool
 * @author Mateusz Warzyński
 */

function removePrivateMessage($id)
{
    global $panthera;
    $SQL = $panthera->db->query('DELETE FROM `{$db_prefix}private_messages` WHERE `id` = :id', array('id' => $id));

    if ($SQL)
        return True;

    return False;
}

// display content and do actions
function pMessagesAjax()
{
    global $panthera, $user, $template;

    // display private messages content
    if ($_GET['display'] == 'privatemessages')
    {

        $tpl = 'privatemessages.tpl';

        // check user permissions
        if (!getUserRightAttribute($user, 'can_send_pmsg'))
        {
            print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
            pa_exit();
        }

        /** JSON PAGES **/

        // Send new private message
        if ($_GET['action'] == 'send_message') {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $recipient_login = $_POST['recipient_login'];

            // check areas
            if ($title == '' or strlen($title) < 4)
                  ajax_exit(array('status' => 'failed', 'message' => 'Title is empty or too short!'));

            if ($_POST['sender_id'] == '')
                  ajax_exit(array('status' => 'failed', 'message' => 'Sender_ID is empty!'));

            if ($recipient_login == '')
                  ajax_exit(array('status' => 'failed', 'message' => 'Recipient login is empty!'));

            if ($content == '' or strlen($content) < 4)
                  ajax_exit(array('status' => 'failed', 'message' => 'Message content is empty or too short!'));

            // check if sender exists
            $sender = new getUser('id', intval($_POST['sender_id']));
            if (!$sender -> exists())
                  ajax_exit(array('status' => 'failed', 'message' => 'Sender does not exist!'));

            // check if recipient exists
            $recipient = new getUser('login', $recipient_login);
            if (!$recipient -> exists())
                  ajax_exit(array('status' => 'failed', 'message' => 'Recipient does not exist!'));

            // filter input values
            $title= filterInput($title, 'quotehtml');

            // send new message
            if (sendPrivateMessage($_POST['title'], $sender->full_name, $sender->id, $recipient->full_name, $recipient->id, $content) == True)
                  ajax_exit(array('status' => 'success'));
            else
                  ajax_exit(array('status' => 'failed'));
        }


        // hide message sent by user or remove it if recipient hide it
        if (@$_GET['action'] == 'remove_message_sent') {
            $id = intval($_GET['messageid']);
            $message = new privateMessage('id', $id);

            if ($message -> exists())
            {
                  $message -> visibility_sender = !(bool)$message->visibility_sender; // reverse bool value

                  // remove if sender and recipient hide this message
                  if ($message->visibility_sender == 0 and $message->visibility_recipient == 0)
                        removePrivateMessage($id);

                  ajax_exit(array('status' => 'success'));
            } else {
                  ajax_exit(array('status' => 'failed', 'error' => localize('Message does not exists')));
            }
        }

        // hide message received by user or remove it if sender hide it
        if (@$_GET['action'] == 'remove_message_received') {
            $id = intval($_GET['messageid']);
            $message = new privateMessage('id', $id);

            if ($message -> exists())
            {
                  $message -> visibility_recipient = !(bool)$message->visibility_recipient; // reverse bool value

                  // remove if sender and recipient hide this message
                  if ($message->visibility_sender == 0 and $message->visibility_recipient == 0)
                        removePrivateMessage($id);

                  ajax_exit(array('status' => 'success'));
            } else {
                  ajax_exit(array('status' => 'failed', 'error' => localize('Message does not exists')));
            }
        }


        /** END OF JSON PAGES **/

        /** Ajax-HTML PAGES **/

        if (@$_GET['action'] == 'new_message') {
            $template -> push('sender_id', $user->id);
            $template -> push('action', 'new_message');
            $template -> display($tpl);
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

            $template -> push('action', 'show_message');
            $template -> display($tpl);
            pa_exit();
        }

        /** END OF Ajax-HTML PAGES **/


        // Displaying main privateMessages site

        // get messages by recipient_id
        $count = getPrivateMessages(array('recipient_id' => $user->id), False);
        $m = getPrivateMessages(array('recipient_id' => $user->id), $count, 0);
        $template -> push('pmessages_list_received', $m);

        // get messages by sender_id
        $count = getPrivateMessages(array('sender_id' => $user->id), False);
        $m = getPrivateMessages(array('sender_id' => $user->id), $count, 0);
        $template -> push('pmessages_list_sent', $m);

        $template -> display($tpl);
        pa_exit();
    }
}

// add privateMessages plugin to index list
function privateMessagesToAjaxList($list)
{
    $list[] = array('location' => 'plugins', 'name' => 'privateMessages', 'link' => '?display=privatemessages');

    return $list;
}

function pmToDash($attr) {
    if ($attr[1] != "main") { return $attr; }
    $attr[0][] = array('link' => '?display=privatemessages', 'name' => localize('Private messages', 'pmessages'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-mail-flag-icon.png', 'linkType' => 'ajax');
    return $attr;
}

$panthera -> add_option('dash_menu', 'pmToDash');


$panthera -> add_option('ajaxpages_list', 'privateMessagesToAjaxList');
$panthera -> add_option('ajax_page', 'pMessagesAjax');
?>
