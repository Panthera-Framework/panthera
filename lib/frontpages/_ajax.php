<?php
/**
  * Ajax front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

require_once 'content/app.php';

// front controllers utils
include PANTHERA_DIR. '/frontController.class.php';

// only for registered users
/*if (!checkUserPermissions($user))
{
    $template -> setTemplate('admin');
    $template->display('no_access.tpl');
    $panthera->finish();
    pa_exit();
}*/

$cat = '';

// if we are using ajaxpage from selected category
if (isset($_GET['cat']))
{
    $cat = str_replace('/', '', str_replace('.', '', $_GET['cat']));
    
    if (!getContentDir('ajaxpages/' .$cat))
    {
        $cat = '';
    }
    
    $cat .= '/';
}

$display = $cat.str_replace('/', '', addslashes($_GET['display']));

// admin category is built-in
if ($cat == 'admin/')
{
    $template -> setTemplate('admin');

    // check user permissions
    if (!getUserRightAttribute($panthera->user, 'can_access_pa')) {
        $template->display('no_access.tpl');
        pa_exit();
    }

    if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) and !isset($_GET['_bypass_x_requested_with']))
        pa_redirect('pa-admin.php?'.$_SERVER['QUERY_STRING']);    

    // set main template
    $template -> push ('username', $user->login);
    
    if (is_file(SITE_DIR. '/css/admin/custom/' .$display. '.css'))
        $panthera -> template -> addStyle('{$PANTHERA_URL}/css/admin/custom/' .$display. '.css');
    
    if (is_file(SITE_DIR. '/js/admin/custom/' .$display. '.js'))
        $panthera -> template -> addStyle('{$PANTHERA_URL}/js/admin/custom/' .$display. '.js');
}

$panthera -> get_options('ajaxpages.category', $_GET['cat']);

// dont generate meta tags and keywords, allow only adding scripts and styles
$panthera -> template -> generateMeta = False;
$panthera -> template -> generateKeywords = False;
$tpl = 'no_page.tpl';

// navigation    
$panthera -> add_option('page_load_ends', array('navigation', 'appendCurrentPage')); 

// execute plugins
$panthera -> get_options('ajax_page');
$pageFile = getContentDir('ajaxpages/' .$display. '.php');

// find page and load it
if ($pageFile)
{
    include $pageFile;
    $name = str_replace($cat, '', $display);
    
    $controllerName = $name. 'AjaxController';
    
    if (class_exists($name. 'AjaxControllerCore'))
        $controllerName = $name. 'AjaxControllerCore';
    
    if (frontController::$searchFrontControllerName)
        $controllerName = frontController::$searchFrontControllerName;
    
    if (class_exists($controllerName))
    {
        $controller = new $controllerName;
        print($controller -> display());
        pa_exit();
    }
}

// set default template if none selected
if (!$panthera->template->name)
{
    $panthera -> template -> setTemplate($panthera->config->getKey('template'));
}

$template -> display($tpl);
pa_exit();
