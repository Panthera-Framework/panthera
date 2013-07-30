<?php
/**
  * Admin Panel front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

require 'content/app.php';

if (!checkUserPermissions($user))
    pa_redirect('pa-login.php');

$panthera -> template -> setTemplate('admin');
$panthera -> template -> setTitle($panthera -> config -> getKey('site_title', 'Panthera', 'string'));

if (!isset($_GET['display']))
    $_GET['display'] = 'dash';

if ($_SERVER['QUERY_STRING'] != '')
    $panthera -> template -> push ('navigateTo', $_SERVER['QUERY_STRING']);

$panthera -> importModule('simpleMenu');

/** Admin Menu **/
// build a menu
$menu = new simpleMenu();
$menu -> add('dash', localize('Dash'), '?display=dash', '', '{$PANTHERA_URL}/images/admin/menu/dashboard.png', '');

// other built-in pages
if (getUserRightAttribute($user, 'can_see_debug'))
    $menu -> add('debug', localize('Debugging center'), '?display=debug', '', '{$PANTHERA_URL}/images/admin/menu/developement.png', '');

$menu -> add('users', localize('Users'), '?display=settings&action=users', '', '{$PANTHERA_URL}/images/admin/menu/users.png', '');

// end of built-in pages
$menu -> loadFromDB('admin');

// allow plugins modify admin menu
$panthera -> get_options('admin_menu', $menu);

// set current active menu (optional)
$menu -> setActive(@$_GET['display']);
/** End of Admin Menu **/

$panthera -> template -> push ('user', $panthera->user);

// langauges list
$locales = $panthera -> locale -> getLocales();
$localesTpl = array();

foreach ($locales as $lang => $enabled)
{
    if ($enabled == True)
    {
        if (is_file(SITE_DIR. '/images/admin/flags/' .$lang. '.png'))
            $localesTpl[] = $lang;
    } 
}

$panthera -> template -> push('flags', $localesTpl);
$panthera -> template -> push('admin_menu', $menu->show());
$panthera -> template -> push('displayPage', $_GET['display']);
$panthera -> template -> push('queryString', $_SERVER['QUERY_STRING']);
$panthera -> template -> push('PANTHERA_VERSION', PANTHERA_VERSION);
$panthera -> template -> display();
$panthera -> finish();
