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

    // Initialize Facebook session
    $facebook = new facebookWrapper();

    $facebook->loginUser($scope, 'script');

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
