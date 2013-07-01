<?php
/**
  * Manage mailing system
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'mailing.tpl';


if (!getUserRightAttribute($panthera->user, 'can_view_mailing')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> importModule('mailing');

$panthera -> locale -> loadDomain('mailing');

// send one or more e-mails
if ($_GET['action'] == 'send')
{
    if (!getUserRightAttribute($user, 'can_send_mails'))
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages')));

    if (strlen($_POST['body']) < 5)
        ajax_exit(array('status' => 'failed', 'message' => localize('Message is too short')));

    if (!$panthera->types->validate($_POST['from'], 'email'))
        ajax_exit(array('status' => 'failed', 'message' => localize('Please type a valid e-mail adress in "from" input')));

    $recipients = explode(',', $_POST['recipients']);

    $r = 0;

    $mail = new mailMessage();

    // subject
    $mail -> setSubject($_POST['subject']);
    $mail -> setFrom($_POST['from']);

    // all recipients
    foreach ($recipients as $recipient)
    {
        if ($panthera->types->validate($recipient, 'email')) // custom e-mail adress
        {
            $mail -> addRecipient(trim($recipient, ' '));
            $r++;
        } elseif (substr($recipient, 0, 4) == 'gid:') {
            // groups support here

        } elseif (substr($recipient, 0, 4) == 'uid:') { // get user by id
            $mailUser = new pantheraUser('id', $recipient);

            if ($mailUser->exists())
            {
                $mail -> addRecipient($mailUser->mail);
                $r++;
            }
        } elseif (substr($recipient, 0, 2) == 'u:') { // get user by login
            $mailUser = new pantheraUser('login', $recipient);

            if ($mailUser->exists())
            {
                $mail -> addRecipient($mailUser->mail);
                $r++;
            }
        }
    }

    if ($r > 0)
    {
        $panthera -> session -> set('mailing_last_from', $_POST['from']);
        $panthera -> session -> set('mailing_last_body', $_POST['body']);
        $panthera -> session -> set('mailing_last_recipients', $_POST['recipients']);
        $panthera -> session -> set('mailing_last_subject', $_POST['subject']);

        if($mail -> send(pantheraUrl($_POST['body']), 'html'))
            ajax_exit(array('status' => 'success', 'message' => localize('Sent'). ' ' .$r. ' ' .localize('mails')));
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error')));
    }

    ajax_exit(array('status' => 'failed', 'message' => localize('Please specify at least one recipient')));
} elseif ($_GET['action'] == 'select') {
    // list users and groups

    $template -> push('action', 'select');
    $template -> display($tpl);
    pa_exit();
}

/*$message = new mailMessage();
$message -> setSubject('Testowa wiadomość');
$message -> addRecipient('xyz@gmail.com');
$message -> send('No to lecimy', 'plain');*/

$yn = array(0 => localize('No'), 1 => localize('Yes'));

$mailAttributes = array();

$mailAttributes[] = array('name' => localize('Use PHP mail() function'), 'record_name' => 'mailing_use_php', 'value' => (bool)$panthera -> config -> getKey('mailing_use_php', True, 'bool'));

// mailing server
$mailAttributes[] = array('name' => localize('Server'), 'record_name' => 'mailing_server', 'value' => $panthera -> config -> getKey('mailing_server'));
$mailAttributes[] = array('name' => localize('Port'), 'record_name' => 'mailing_server_port', 'value' => $panthera -> config -> getKey('mailing_server_port'));

// auth data
$mailAttributes[] = array('name' => localize('Login'), 'record_name' => 'mailing_user', 'value' => $panthera -> config -> getKey('mailing_user', 'email'));
$mailAttributes[] = array('name' => localize('Password'), 'record_name' => 'mailing_password', 'value' => '******');

// From header
$mailAttributes[] = array('name' => 'SSL', 'record_name' => 'mailing_smtp_ssl', 'value' => (bool)$panthera -> config -> getKey('mailing_smtp_ssl', True, 'bool'));

// From header
$mailAttributes[] = array('name' => localize('Default sender'), 'record_name' => 'mailing_from', 'value' => $panthera -> config -> getKey('mailing_from', 'email'));

if (!$panthera->session->exists('mailing_last_from'))
    $panthera -> session -> set('mailing_last_from', $panthera -> config -> getKey('mailing_from', 'email'));

$panthera -> template -> push ('last_subject', $panthera->session->get('mailing_last_subject'));
$panthera -> template -> push ('last_recipients', $panthera->session->get('mailing_last_recipients'));
$panthera -> template -> push ('last_body', $panthera->session->get('mailing_last_body'));
$panthera -> template -> push ('last_from', $panthera->session->get('mailing_last_from'));
$panthera -> template -> push ('mail_attributes', $mailAttributes);

?>
