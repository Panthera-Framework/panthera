<?php
/**
  * Compose newsletter
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_compose_newsletters')) {
    $noAccess = new uiNoAccess;
    $noAccess -> addMetas(array('can_compose_newsletters'));
    $noAccess -> display();
}

$panthera -> locale -> loadDomain('newsletter');
$panthera -> importModule('newsletter');
$panthera -> template -> setTitle(localize('Compose a new message', 'newsletter'));
$language = $panthera -> locale -> getActive();

$newsletter = new newsletter('nid', $_GET['nid']);

// display error page if newsletter category does not exists
if (!$newsletter->exists())
{
    $noAccess = new uiNoAccess;
    $noAccess -> display();
}

// titlebar
$titlebar = new uiTitlebar(localize('Newsletter', 'newsletter'). ' - ' .localize('Edit a message footer', 'newsletter'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'left');

$attr = unserialize($newsletter -> attributes);

if (!$attr['footer'])
{
    $attr['footer'] = '';
    $newsletter -> attributes = serialize($attr);
    $newsletter -> save();
}

$panthera -> template -> push ('nid', $newsletter->nid);
$panthera -> template -> push ('mailFooter', filterInput($attr['footer'], 'wysiwyg'));
$panthera -> template -> display('newsletter_footer.tpl');
pa_exit();
