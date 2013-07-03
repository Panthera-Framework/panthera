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

$display = addslashes($_GET['display']);

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
$panthera -> addPermission('can_view_qmsg', localize('Can view quick messages', 'messages'));
$panthera -> addPermission('can_qmsg_manage_all', localize('Can manage all quickMessages elements', 'messages'));


if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) and !isset($_GET['_bypass_x_requested_with']))
    pa_redirect('pa-admin.php?'.$_SERVER['QUERY_STRING']);

// navigation    
$panthera -> add_option('page_load_ends', array('navigation', 'appendCurrentPage')); 
    
// set main template
$template -> setTemplate('admin');

// dont generate meta tags and keywords, allow only adding scripts and styles
$panthera -> template -> generateMeta = False;
$panthera -> template -> generateKeywords = False;
$template -> push ('username', $user->login);
$tpl = 'no_page.tpl';

if (is_file(SITE_DIR. '/css/admin/custom/' .$display. '.css'))
    $panthera -> template -> addStyle('{$PANTHERA_URL}/css/admin/custom/' .$display. '.css');
    
if (is_file(SITE_DIR. '/js/admin/custom/' .$display. '.js'))
    $panthera -> template -> addStyle('{$PANTHERA_URL}/js/admin/custom/' .$display. '.js');
    

// execute plugins
$panthera -> get_options('ajax_page');

if (is_file(PANTHERA_DIR. '/ajaxpages/' .$display. '.php'))
{
    include(PANTHERA_DIR. '/ajaxpages/' .$display. '.php');
} elseif (is_file(SITE_DIR. '/content/ajaxpages/' .$display. '.php')) {
    include(SITE_DIR. '/content/ajaxpages/' .$display. '.php');
}

$template -> display($tpl);
pa_exit();
