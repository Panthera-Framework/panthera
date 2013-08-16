<?php
/**
  * Admin panel login front controller
  *
  * @package Panthera\core
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

require 'content/app.php';

$locales = $panthera -> locale -> getLocales();

// logout user, TODO: CHANGE TO POST
if (isset($_GET['logout']))
    logoutUser();
    
// redirect user if already logged in
if(checkUserPermissions($user))
    pa_redirect('pa-admin.php');

if (isset($_POST['log']) or isset($_GET['key']))
{
    if ($_POST['recovery'] == "1" or isset($_GET['key']))
    {
        $panthera -> importModule('passwordrecovery');
        if (isset($_GET['key']))
        {
            // change user password
            if (recoveryChangePassword($_GET['key']))
                $template -> push('message', localize('Password changed, you can use new one', 'messages'));
            else
                $template -> push('message', localize('Invalid recovery key, please check if you copied link correctly', 'messages'));

        } else {
            // send an e-mail with new password
            if (recoveryCreate($_POST['log']))
                $template -> push('message', localize('New password was sent in a e-mail message to you', 'messages'));
            else
                $template -> push('message', localize('Invalid user name specified', 'messages'));
        }
    } else {
        if(userCreateSession($_POST['log'], $_POST['pwd']))
        {
            // if user cannot access Admin Panel, redirect to other location (specified in redirect_after_login config section)
            if (!getUserRightAttribute($user, 'can_access_pa'))
            {
                pa_redirect($panthera->config->getKey('redirect_after_login', 'index.php', 'string', 'pa-login'));
            }
        
            if ($panthera->session->exists('login_referer'))
            {
                header('Location: ' .$panthera->session->get('login_referer'));
                $panthera -> session -> remove ('login_referer');
                pa_exit();
            }
        
            pa_redirect('pa-admin.php');
            pa_exit();
        } else
            $template -> push('message', localize('Invalid user name or password', 'messages'));
    }
}

// save the referer when logging in
if (strpos($_SERVER['HTTP_REFERER'], $panthera->config->getKey('ajax_url')) !== False and strpos($_SERVER['HTTP_REFERER'], '&cat=admin') !== False)
{
    $panthera->session->set('login_referer', $_SERVER['HTTP_REFERER']);
}

$panthera -> template -> setTitle(localize('Log in'));
$template -> setTemplate('admin');
$template -> display('login.tpl');
$panthera -> finish();
?>
