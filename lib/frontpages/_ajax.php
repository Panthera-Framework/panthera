<?php
/**
  * Ajax front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

require 'content/app.php';

// user is not logged in
if (!checkUserPermissions($user))
{
    $template -> setTemplate('admin');
    $template->display('no_access.tpl');
    $panthera->finish();
    pa_exit();
}

$panthera -> template -> generateHeader = False;

// cosmetics, so here are permissions
$panthera -> addPermission('can_see_users_table', localize('Can see other profiles (admin panel)'));
$panthera -> addPermission('can_see_system_info', localize('Can view system informations (admin panel)'));
$panthera -> addPermission('can_update_config_overlay', localize('Can change config overlay (admin panel)'));
$panthera -> addPermission('can_update_locales', localize('Can manage system locales', 'messages'));
$panthera -> addPermission('can_update_config_overlay', localize('Can edit site configuration', 'messages'));
$panthera -> addPermission('can_see_system_info', localize('Can see system informations', 'messages'));
$panthera -> addPermission('can_see_debug', localize('Can view debugging informations', 'messages'));
$panthera -> addPermission('can_manage_debug', localize('Can manage debugger system', 'messages'));
$panthera -> addPermission('can_see_debhook', localize('Can view plugins debugger page', 'messages'));
$panthera -> addPermission('can_update_menus', localize('Can update menus', 'messages'));
$panthera -> addPermission('can_see_ajax_pages', localize('Can see index of all ajax pages', 'messages'));
$panthera -> addPermission('can_manage_all_uploads', localize('Can edit and delete existing uploads added by other users', 'messages'));
$panthera -> addPermission('can_delete_own_uploads', localize('Can delete own uploaded files', 'messages'));
$panthera -> addPermission('can_upload_files', localize('Can upload files', 'messages'));


$template -> setTemplate('admin');

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) and !isset($_GET['_bypass_x_requested_with']))
    pa_redirect('pa-admin.php?'.$_SERVER['QUERY_STRING']);

$template -> push ('username', $user->login);

#A$post_content = 'To jest przykladowa strona [b]dzialajaca na Pantherze[/b] ;-)';
#$post_content = $panthera->get_filters('post_content', $post_content);can_see_settings

$tpl = 'no_page.tpl';

$panthera -> get_options('ajax_page');
$display = addslashes($_GET['display']);

if (is_file(PANTHERA_DIR. '/ajaxpages/' .$display. '.php'))
{
    include(PANTHERA_DIR. '/ajaxpages/' .$display. '.php');
} elseif (is_file(SITE_DIR. '/content/ajaxpages/' .$display. '.php')) {
    include(SITE_DIR. '/content/ajaxpages/' .$display. '.php');
}

$template -> display($tpl);
$panthera -> finish();
