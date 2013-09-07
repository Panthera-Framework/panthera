<?php
/**
  * Facebook integration with Panthera
  *
  * @package Panthera\plugins\faceIntegration
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
global $panthera, $user, $template;

if (!defined('IN_PANTHERA'))
    exit;

$panthera -> importModule('facebook');

if ($_GET['display'] == 'facebook')
{
    $tpl = 'facebook.tpl';

    $panthera -> locale -> loadDomain('facebook');

    if (@$_GET['action'] == 'settings')
    {
        $tpl = 'facebook_settings.tpl';
        
        if (!getUserRightAttribute($user, 'can_update_config_overlay')) {
                $template->display('no_access.tpl');
                pa_exit();
        }
        
        if ($_GET['subaction'] == 'save')
        {
            $appID = $_POST['appid'];
            $secret = $_POST['secret'];
			
			if ($_POST['scope'] != '' AND strlen($_POST['scope']) > 4)
				$scope['scope'] = $_POST['scope'];
            
            if (gettype($appID) != 'string' OR gettype($secret) != 'string')
                ajax_exit(array('status' => 'failed', 'message' => 'Invalid type of variables'));
            
            if (!$panthera->config->setKey("facebook_appid", $appID) OR !$panthera->config->setKey("facebook_secret", $secret) OR !$panthera->config->setKey("facebook_scope", $scope))
            {
                ajax_exit(array('status' => 'failed', 'message' => localize('Invalid value for this data type')));
                pa_exit();
            } else {
                ajax_exit(array('status' => 'success', 'message' => localize('AppID, Secret and Permissions have been saved!')));
                pa_exit();
            }
        }
        
        $template -> push('appid', $panthera -> config -> getKey('facebook_appid'));
        $template -> push('secret', $panthera -> config -> getKey('facebook_secret'));
		$template -> push('scope', $panthera -> config -> getKey('facebook_scope')['scope']);
		
		$titlebar = new uiTitlebar(localize('Settings', 'facebook'));
		$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/facebook.png', 'left');
		
        $template -> display($tpl);
        pa_exit();
    }

	$titlebar = new uiTitlebar(localize('Facebook', 'facebook'));
	$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/facebook.png', 'left');

    // Initialize Facebook session
    if ($panthera->config->getKey('facebook_appid') == '' or $panthera->config->getKey('facebook_secret') == '') {
		$template -> push('error', true);
		$template -> display($tpl);
		pa_exit();
	}
	
    $facebook = new facebookWrapper();

    $facebook->loginUser(array('scope' => 'user_about_me'), 'script');

    // Get info about user (it will be needed in ajaxpages and template, therefore I created variable...)
    $userinfo = $facebook->sdk->api('/me');

    /* Ajax pages */
    if (@$_GET['action'] == 'synchronize')
    {
        if (!getUserRightAttribute($user, 'can_manage_facebook'))
        {
            print(json_encode(array('status' => 'failed', 'error' => localize('Permission denied. You dont have access to this action', 'messages'))));
            pa_exit();
        }

        if (is_string($userinfo['name']) and is_string($userinfo['id']))
        {
            $user -> full_name = $userinfo['name'];
            $user -> profile_picture = 'http://graph.facebook.com/' . $userinfo["id"] . '/picture?width=200&height=200';
            $user -> attributes -> facebook_id = $userinfo["id"];

            ajax_exit(array('status' => 'success', 'message' => localize("Data has been successfully synchronized!")));
        } else {
            ajax_exit(array('status' => 'failed', 'error' => 'Cannot synchronize data!'));
        }
    }

    $template -> push('user', $userinfo);
    $template -> display($tpl);
    pa_exit();
}