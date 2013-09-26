<?php
/**
  * Show and manage newsletter users
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($panthera->user, 'can_manage_newsletter_users')) {
    $panthera->template->display('no_access.tpl');
    pa_exit();
}

// import /lib/modules/newsletter.module.php file to access its classes and functions
$panthera -> importModule('newsletter');
$panthera -> locale -> loadDomain('newsletter');

// GET newsletter by `nid` (from GET parameter)
$newsletter = new newsletter('nid', $_GET['nid']);
$types = newsletterManagement::getTypes();

// exit if newsletter does not exists (exists method is a built-in method of pantheraFetchDB's abstract class)
if (!$newsletter->exists())
{
    $panthera -> template -> push('error_message', localize('Selected newsletter does not exist.', 'newsletter'));
    $panthera -> template -> display('_ajax_admin_error.tpl');
    pa_exit();
}

/**
  * Remove a subscriber
  *
  * @author Mateusz Warzyński
  * @author Damian Kęska
  */
  
if ($_GET['action'] == 'removeSubscriber')
{
    if(newsletterManagement::removeSubscriber($_POST['id'])) {
        newsletterManagement::updateUsersCount($_GET['nid']);
        ajax_exit(array('status' => 'success'));
    }
    
    ajax_exit(array('status' => 'failed', 'messsage' => localize('Cannot find subscriber', 'newsletter')));
}

/**
  * Add a new anonymous subscriber
  *
  * @author Mateusz Warzyński
  * @author Damian Kęska
  */

if ($_GET['action'] == 'addSubscriber')
{
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
	{
		ajax_exit(array('status' => 'failed', 'message' => localize('Check email address.', 'newsletter')));
	}
	
	$userID = -1; // guest
    $type = '';
	
	if (isset($_POST['user']))
	{
	    $u = new pantheraUser('login', $_POST['user']);
	    
	    if ($u->exists())
	    {
	        $userID = $u->id;
	    }
	}
	
	if (in_array($_POST['type'], $types))
	{
	    $type = $_POST['type'];
	}
	
	if ($newsletter -> registerUser($_POST['email'], $type, $userID, '', True, True))
    {
        newsletterManagement::updateUsersCount($_GET['nid']);
        $subscription = $newsletter -> getSubscription($_POST['email']);
        $notes = $subscription['notes'];
        
        if ($_POST['notes'])
        {
            $notes = strip_tags($_POST['notes']);
            $subscriber = new newsletterSubscriber('id', $subscription['id']);
            $subscriber -> notes = $notes;
            $subscriber -> save();
        }
        
	    ajax_exit(array('status' => 'success', 'id' => $subscription['id'], 'type' => $subscription['type'], 'address' => $subscription['address'], 'added' => $subscription['added'], 'notes' => $notes));
	} else {
		ajax_exit(array('status' => 'failed', 'message' => localize('Cannot add subscriber', 'newsletter')));
    }
}

$panthera -> template -> push ('nid', $_GET['nid']);
$panthera -> template -> push ('action', '');

if ($_GET['action'] == 'show_table')
    $panthera -> template -> push ('action', 'show_table');

// get count of newsletter users
$usersCount = $newsletter -> getUsers(False); // false means we dont want to get records but it's count

$page = intval($_POST['pagenum']);

// pages are only > -1 (we are counting from 0, so the real page is page-1 means page 1 is 0 in code)
if ($page < 0)
    $page = 0;

// get records only for current page
$uiPager = new uiPager('adminNewsletter', $usersCount, 'adminNewsletter');
$uiPager -> setActive($page); 
$uiPager -> setLinkTemplates('#', 'createPopup(\'?' .getQueryString($_GET, 'page={$page}', '_'). '\', 1024);');
$limit = $uiPager -> getPageLimit();

// get all avaliable newsletter types
$panthera -> template -> push ('newsletter_types', $types);

// get all users from current page
$users = $newsletter -> getUsers($limit[0], $limit[1]);
$panthera -> template -> push ('newsletter_users', $users);
$panthera -> template -> display('newsletter_users.tpl');
pa_exit();
?>
