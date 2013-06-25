<?php
/**
  * Home site
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'dash.tpl'; // change to 'main.tpl', then open console in chromium and go to ?display=dash (dash.tpl is a copy of main.tpl).

if (!getUserRightAttribute($user, 'can_see_dash')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('dash');

$menu = array();

switch ($_GET['menu'])
{
    case 'settings':
        $menu[] = array('link' => '?display=dash', 'name' => localize('Back'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/home.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=settings&action=users', 'name' => localize('Users'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=settings&action=my_account', 'name' => localize('My account', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/user.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=database', 'name' => localize('Database management', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/db.png' , 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=settings&action=system_info', 'name' => localize('Informations about system', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/system.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=conftool', 'name' => localize('Configuration editor', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/config.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=shellutils', 'name' => localize('Shell utils', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-yakuake-icon.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=errorpages', 'name' => localize('System error pages', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-process-stop-icon.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=menuedit', 'name' => localize('Menu editor', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=locales', 'name' => localize('Language settings', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/locales.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=plugins', 'name' => ucfirst(localize('plugins', 'dash')), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-preferences-plugin-icon.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=templates', 'name' => ucfirst(localize('templates', 'dash')), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Icon-template.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=langtool', 'name' => ucfirst(localize('translates', 'dash')), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-preferences-desktop-locale-icon.png', 'linkType' => 'ajax');
    break;

    case '':
        $_GET['menu'] = 'main';

        // main menu, there are predefined variables
        $menu[] = array('link' => '{$PANTHERA_URL}', 'name' => localize('Front page', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/home.png');
        $menu[] = array('link' => '?display=dash&menu=settings', 'name' => localize('Settings', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/settings.png' , 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=debug', 'name' => localize('Debugging center'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/developement.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=ajaxpages', 'name' => localize('Index of ajax pages', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-tab-detach-icon.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=settings&action=users', 'name' => localize('Users'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png', 'linkType' => 'ajax');
        $menu[] = array('link' => '?display=mailing', 'name' => localize('Mailing', 'dash'), 'icon' => '{$PANTHERA_URL}/images/admin/menu/mail-replied.png', 'linkType' => 'ajax');
    break;
}


list($menu, $category) = $panthera -> get_filters('dash_menu', array($menu, $_GET['menu']));

$template -> push ('dash_menu', $menu);
$template -> push ('dash_messages', $panthera -> get_filters('ajaxpages.dash.msg', array()));

if (class_exists('gallery') and $category == 'main')
    $panthera -> template -> push ('galleryItems', gallery::getRecentPicture('', 9));

/** END OF Ajax-HTML PAGES **/

?>
