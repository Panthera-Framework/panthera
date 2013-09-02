<?php
/**
  * Newsletter management
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($panthera->user, 'can_manage_newsletter')) {
    $panthera->template->display('no_access.tpl');
    pa_exit();
}

// import /lib/modules/newsletter.module.php file to access its classes and functions
$panthera -> importModule('newsletter');
$panthera -> locale -> loadDomain('newsletter');

if ($_GET['action'] == 'createCategory')
{
	if (strlen($_GET['title']) > 2) {
		if (newsletterManagement::create($_GET['title']))
			ajax_exit(array('status' => 'success'));
		else
			ajax_exit(array('status' => 'failed', 'message' => localize('Cannot add newsletter category! Check title.', 'newsletter')));
	} else {
		ajax_exit(array('status' => 'failed', 'message' => localize('Title should contain at least 3 letters.', 'newsletter')));
	}
}

if ($_GET['action'] == 'removeCategory')
{
	if ($_GET['nid'] != '') {
		if (newsletterManagement::remove('nid', $_GET['nid']))
			ajax_exit(array('status' => 'success'));
		else
			ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove newsletter category!', 'newsletter')));
	} else {
		ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove newsletter category! ID is missing.', 'newsletter')));
	}
}

$newsletters = newsletterManagement::search();

if ($_GET['query'] != '') {
	foreach ($newsletters as $key => $newsletter) {
		if ( stripos( $newsletter['title'], $_GET['query'] ) !== False )
			$news_array[] = $newsletter;
	}
	$newsletters = $news_array;
}

$panthera -> importModule('admin/ui.searchbar');
$panthera -> locale -> loadDomain('search');

$sBar = new uiSearchbar( 'uiTop' );
$sBar->setQuery( $_GET['query'] );
$sBar->setAddress( '?display=newsletter&cat=admin' );
$sBar->navigate( True );
$sBar->addIcon( '{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_manage_newsletter', localize( 'Manage permissions' ) );

$panthera -> template -> push('categories', $newsletters);

$titlebar = new uiTitlebar(localize('Newsletter management', 'newsletter'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'left');

$panthera -> template -> display('newsletter.tpl');
pa_exit();
?>
