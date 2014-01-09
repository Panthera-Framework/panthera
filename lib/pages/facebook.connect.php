<?php
/**
 * User registration form and processing page
 * For modifications this file should be copied to /content/pages directory and edited
 * Please don't modify framework files in /lib directory unless you  
 *
 * @package Panthera\core\pages
 * @author Damian Kęska
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;

$panthera -> importModule('facebook');

// some defaults to add to site configuration
$backURL = $panthera -> config -> getKey('facebook.default.backurl', '{$PANTHERA_URL}/', 'string', 'facebook');
$appID = $panthera -> config -> getKey('facebook_appid', '', 'string', 'facebook');
$secret = $panthera -> config -> getKey('facebook_secret', '', 'string', 'facebook');
$scope = $panthera -> config -> getKey('facebook.scope', array('publish_stream', 'user_likes'), 'array', 'facebook');

// if back url can be provided as argument (in $_GET)
if ($panthera -> config -> getKey('facebook.connect.allowbackurl', 1, 'bool', 'facebook'))
{
    $backURL = $_GET['back'];
} else {
    // if it can't be, so we should check session, maybe other page stored a back url in session we can use here
    if ($panthera -> session -> exists('facebookBackURL'))
    {
        $backURL = $panthera -> session -> get('facebookBackURL');
    } // else back url will be taken from site configuration
}

// some logging to allow debugging
if ($panthera -> logging -> debug)
{
    $panthera -> logging -> output('Back URL is "' .$backURL. '"', 'facebook');
    $panthera -> logging -> output('Scope: ' .json_encode($scope), 'facebook');
}

// redirect admin to facebook configuration page in admin panel if facebook integration was not configured yet
if (checkUserPermissions($panthera->user, True) and (!$appID or !$secret))
{
    pa_redirect('/pa-admin.php?display=settings.facebook&cat=admin');
    exit;
}

if (!$backURL)
{
    pa_exit();
}

if ($panthera->session->exists('facebookScope'))
{
    $scope = $panthera -> session -> get('facebookScope');
}

$facebook = new FacebookWrapper;
$facebook -> loginUser($scope, 'header', $backURL);