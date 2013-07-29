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
    

// execute plugins
$panthera -> get_options('ajax_page');
$display = str_replace('/', '', addslashes($_GET['display']));

if (is_file(SITE_DIR. '/css/admin/custom/' .$display. '.css'))
    $panthera -> template -> addStyle('{$PANTHERA_URL}/css/admin/custom/' .$display. '.css');
    
if (is_file(SITE_DIR. '/js/admin/custom/' .$display. '.js'))
    $panthera -> template -> addStyle('{$PANTHERA_URL}/js/admin/custom/' .$display. '.js');

if (is_file(PANTHERA_DIR. '/ajaxpages/' .$display. '.php'))
{
    include(PANTHERA_DIR. '/ajaxpages/' .$display. '.php');
} elseif (is_file(SITE_DIR. '/content/ajaxpages/' .$display. '.php')) {
    include(SITE_DIR. '/content/ajaxpages/' .$display. '.php');
}

$template -> display($tpl);
pa_exit();
