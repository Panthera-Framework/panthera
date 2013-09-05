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

// exit if newsletter does not exists (exists method is a built-in method of pantheraFetchDB's abstract class)
if (!$newsletter->exists())
{
    $panthera -> template -> push('error_message', localize('Selected newsletter does not exists'));
    $panthera -> template -> display('_ajax_admin_error.tpl');
    pa_exit();
}

// removing subscriber
if ($_GET['action'] == 'remove_subscriber')
{
    if(newsletterManagement::removeSubscriber($_POST['id']))
        ajax_exit(array('status' => 'success'));

    ajax_exit(array('status' => 'failed', 'messsage' => localize('Cannot find subscriber')));
}

if ($_GET['action'] == 'add_subscriber')
{
	if (strlen($_GET['email']) > 4)
		$email = $_GET['email'];
	else
		ajax_exit(array('status' => 'failed', 'message' => localize('Check email address!')));
	
	if ($newsletter -> registerUser($email, 'mail', -1, '', True, True))
		ajax_exit(array('status' => 'success'));
	else
		ajax_exit(array('status' => 'failed', 'message' => localize('Cannot add subscriber!')));
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

// get all users from current page
$users = $newsletter -> getUsers($limit[0], $limit[1]);

$panthera -> template -> push ('newsletter_users', $users);
$panthera -> template -> display('newsletter_users.tpl');
pa_exit();
?>
