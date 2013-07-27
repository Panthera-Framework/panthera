<?php
/**
  * Easily synchronization information from Facebook
  * @package Panthera\plugins\faceIntegration
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

// register plugin
$pluginInfo = array('name' => 'Facebook Integration', 'author' => 'Mateusz Warzyński', 'description' => 'Login to Panthera admin panel with Facebook', 'version' => PANTHERA_VERSION);
$panthera -> addPermission('can_manage_facebook', localize('Can manage all faceIntegration elements', 'messages'));

$panthera -> config -> getKey('facebook_scope', array('scope' => 'user_about_me'), 'array');

function facebookAjaxpage()
{
    global $panthera;
    $scope = $panthera -> config -> getKey('facebook_scope');
    
    if ($_GET['display'] == 'facebook')
    {
        $dir = str_replace('plugin.php', '', __FILE__);
        include($dir.'/settings.php');
    }
}

/**
 * Make a quick facebook connect redirection and login Panthera user
 *
 * @page facebookConnect
 * @return bool
 * @author Damian Kęska
 */

function facebookConnect ($action, $back='')
{
    global $panthera;
    
    if ($action == 'connect' or $action == 'login') 
    {
        $scope = $panthera -> config -> getKey('facebook_scope');
    
        $panthera -> importModule('facebook'); // facebook wrapper
        $panthera -> importModule('meta'); // user and group metas
        
        $fb = new facebookWrapper();
        
        // if user is not logged in, redirect to login page
        if (!$fb -> isLoggedIn())
        {
            $fb->loginUser($scope, 'script');
            pa_exit();
        }
        
        $user = $fb->sdk->api('/me');
        
        // if we want to login to Panthera based website using Facebook account
        if ($action == 'login')
        {
            $searchUser = meta::getUsers('facebook', False, $user['id']);
            
            // create new user session by id
            if (count($searchUser) == 1)
            {
                $userID = key($searchUser);
                userCreateSessionById($userID);          
            }
        }
        
        if ($back != '')
            pa_redirect($back);
            
        pa_redirect('');
        
    } elseif ($action == 'connect') {
        $panthera -> importModule('facebook'); // facebook wrapper
        $fb = new facebookWrapper();
        
        if ($fb -> isLoggedIn())
        {
            $fb -> logoutUser();
        }
        
        if ($back != '')
            pa_redirect($back);
    }
}

/**
  * This function will run in user panel
  *
  * @param array $list List of user fields in user panel
  * @return mixed 
  * @author Damian Kęska
  */

function facebookLogin($list)
{
    global $panthera;
    
    $scope = $panthera -> config -> getKey('facebook_scope');

    $panthera -> importModule('facebook');

    $_SERVER['REQUEST_URI'] = str_ireplace('&logoutFacebook=True', '', $_SERVER['REQUEST_URI']);

    $fb = new facebookWrapper();

    if (isset($_GET['logoutFacebook']))
    {
        $fb -> logoutUser();
    }

    if ($fb -> isLoggedIn())
    {
        $user = $fb->sdk->api('/me');
        $list['Facebook'] =  localize("Connected with account") . ' <a href="https://facebook.com/' . $user['id'] . '" target="_blank">' . $user['name'] . '</a>, <a href="?display=settings&action=my_account&logoutFacebook=True">' . localize("disconnect") . '</a>';
        
        if (isset($_GET['code']))
            $panthera -> user -> meta -> set('facebook', $user['id']);
    } else {
        $link = $fb->loginUser($scope);
        $list['Facebook'] = '<a href="' . $link . '">' . localize("Connect with Facebook account") . '</a>';
    }

    return $list;
}

// Add 'facebook' item to admin menu
function fIntegrationToAdminMenu($menu) { $menu -> add('facebook', 'Facebook', '?display=facebook', '', '{$PANTHERA_URL}/images/admin/menu/facebook.png', ''); }
$panthera -> add_option('admin_menu', 'fIntegrationToAdminMenu');

$panthera -> add_option('ajax_page', 'facebookAjaxpage');
$panthera -> add_option('user.fields', 'facebookLogin');
