<?php
/**
  * Admin panel login front controller
  *
  * @package Panthera\core\frontpages
  * @config pa-login redirect_after_login
  * @config pa-login login.failures.max
  * @config pa-login login.failures.bantime
  * @config ajax_url
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
{
    if (!getUserRightAttribute($panthera->user, 'can_access_pa'))
    {
        pa_redirect($panthera->config->getKey('redirect_after_login', 'index.php', 'string', 'pa-login'));
        pa_exit(); // just in case
    }
    
    pa_redirect('pa-admin.php');
}

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
        $u = new pantheraUser('login', $_POST['log']);
        
        if ($u -> attributes -> get('loginFailures') >= intval($panthera -> config -> getKey('login.failures.max', 5, 'int', 'pa-login')) and $u -> attributes -> get('loginFailures') !== 0)
        {
            if (intval($u -> attributes -> get('loginFailureExpiration')) <= time())
            {
                $u -> attributes -> set('loginFailures', 0);
                $u -> attributes -> remove('loginFailureExpiration');
                $u -> save();
                
            } else {
                $panthera -> get_options('login.failures.exceeded', array('user' => $u, 'failures' => $u -> attributes -> get('loginFailures'), 'expiration' => $u -> attributes -> get('loginFailureExpiration')));
                $panthera -> template -> push('flags', $localesTpl);
                $panthera -> template -> push('message', localize('Number of login failures exceeded, please wait a moment before next try', 'messages'));
                $panthera -> template -> setTemplate('admin');
                $panthera -> template -> display('login.tpl');
                pa_exit();
                
            }
        }
        
        $result = userCreateSession($_POST['log'], $_POST['pwd']);
        
        /**
          * Successful login
          *
          * @author Damian Kęska
          */
        
        if($result and is_bool($result))
        {
            // if user cannot access Admin Panel, redirect to other location (specified in redirect_after_login config section)
            if (!getUserRightAttribute($panthera->user, 'can_access_pa'))
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

            /**
              * Suspended/banned account
              *
              * @author Damian Kęska
              */

        } elseif ($result === 'BANNED') {
            $template -> push('message', localize('This account has been suspended, please contact administrator for details', 'messages'));
            $panthera -> get_options('login.failures.suspended', array('user' => $u, 'failures' => $u -> attributes -> get('loginFailures'), 'expiration' => $u -> attributes -> get('loginFailureExpiration')));


            /**
              * Login failure
              *
              * @author Damian Kęska
              */

        } elseif ($result === False) {
            $template -> push('message', localize('Invalid user name or password', 'messages'));
            $u -> attributes -> set('loginFailures', intval($u -> attributes -> get('loginFailures'))+1);
            
            // plugins support
            $panthera -> get_options('login.failures.exceeded', array('user' => $u, 'failures' => $u -> attributes -> get('loginFailures'), 'expiration' => $u -> attributes -> get('loginFailureExpiration')));
            
            if ($u -> attributes -> get('loginFailures') >= intval($panthera -> config -> getKey('login.failures.max', 5, 'int', 'pa-login')))
            {
                $u -> attributes -> set('loginFailureExpiration', (time()+intval($panthera -> config -> getKey('login.failures.bantime', 300, 'int', 'pa-login')))); // 5 minutes by default
            }
            
            $u -> attributes -> set('lastFailLoginIP', $_SERVER['REMOTE_ADDR']);
            $u -> save();
        }
    }
}

// save the referer when logging in
if (strpos($_SERVER['HTTP_REFERER'], $panthera->config->getKey('ajax_url')) !== False and strpos($_SERVER['HTTP_REFERER'], '&cat=admin') !== False)
{
    $panthera->session->set('login_referer', $_SERVER['HTTP_REFERER']);
}

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
$panthera -> template -> setTitle(localize('Log in'));
$template -> setTemplate('admin');
$template -> display('login.tpl');
$panthera -> finish();
?>
