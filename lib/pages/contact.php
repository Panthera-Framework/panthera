<?php
/**
  * Example contact action handler
  *
  * @package Panthera\core\pages
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

// just in case
$panthera -> importModule('contact');
$contact = new contactFrontpage;

// see documentation or module source code to get list of all variables that can be modified
$contact -> topicTemplate = "[".$_SERVER['HTTP_HOST']."] {\$p_contactTopic}";
$contact -> mailBody = localize('From', 'contactpage').": {\$p_contactName} < {\$p_contactMail} >\n\n".localize('Content', 'contactpage').":\n{\$p_contactContent}";
$contact -> fields['p_contactJabber']['enabled'] = True;

// test array with example input
/*
$data = array(
    'p_contactMail' => 'my_address@mydomain.org',
    'p_contactContent' => 'This is a test, this should be sent to my inbox, modify me to test form validation',
    'p_contactName' => 'Jan Kowalski',
    'p_contactTopic' => 'This is a topic',
    'p_contactJabber' => 'jabber@example.org'
);*/

if (isset($_GET['send']))
{
    $result = $contact->handleData($_POST); // any array can be used to test the form
    
    if (is_array($result))
    {
        $panthera -> template -> push('p_contactErrorMsg', $result['error']);
    } else {
        $panthera -> template -> push('p_contactSuccess', True);
    }
}

$panthera -> template -> display('contact.tpl');
pa_exit();
