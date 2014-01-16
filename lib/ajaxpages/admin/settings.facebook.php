<?php
/**
 * Facebook integration options
 *
 * @package Panthera\core\ajaxpages\settings.facebook
 * @author Damian Kęska
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_facebook_integration'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

/**
 * Custom field handler - comma seperated values
 *
 * @param string $action
 * @param string $key
 * @param mixed $value
 * @package Panthera\adminUI
 * @return mixed 
 * @author Damian Kęska
 */

function enableFacebookLogin($action, $key, $value)
{
    global $panthera;
    
    $extensions = $panthera -> config -> getKey('login.extensions', array('facebook'), 'array', 'pa-login');
    
    if ($action == 'save')
    {
        if ($value === "1")
        {
            if (!in_array('facebook', $extensions))
            {
                $extensions[] = 'facebook';
            }
        } else {

            if (in_array('facebook', $extensions))
            {
                if(($key = array_search('facebook', $extensions)) !== false) 
                {
                    unset($extensions[$key]);
                }
            }
        }

        $panthera -> config -> setKey('login.extensions', $extensions, 'array', 'pa-login');
        return null;
    } else {
        return in_array('facebook', $extensions);
    }
}

// include a title bar
$titlebar = new uiTitlebar(localize('Facebook integration settings', 'facebook'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/facebook.png', 'left');

$panthera -> config -> getKey('register.facebook', 1, 'bool', 'register');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('facebook');
$config -> add('register.facebook', localize('Enable registration with Facebook', 'register'));
$config -> setFieldType('register.facebook', 'bool');

$config -> add('facebook__allow__login', localize('Allow users to login using Facebook account', 'facebook'));
$config -> setFieldType('facebook__allow__login', 'bool');
$config -> setFieldSaveHandler('facebook__allow__login', 'enableFacebookLogin');

$config -> add('facebook.connect.allowbackurl', localize('Allow setting back URL as GET parameter', 'facebook'));
$config -> setFieldType('facebook.connect.allowbackurl', 'bool');
$config -> setDescription('facebook.connect.allowbackurl', localize('Instead panthera->session can be used', 'facebook'));

$config -> add('facebook.default.backurl', localize('Default back URL to navigate if nothing selected', 'facebook'));
$config -> setDescription('facebook.default.backurl', localize('User will be redirected to this url right after accepting permissions. {$PANTHERA_URL} can be used.', 'facebook'));

$config -> add('facebook_appid', localize('Application ID', 'register'));
$config -> setDescription('facebook_appid', localize('Get one at https://developers.facebook.com/apps', 'facebook'));

$config -> add('facebook_secret', localize('Application secret', 'register'));

$config -> add('facebook.scope', localize('Default priviledges', 'register'));
$config -> setFieldSaveHandler('facebook.scope', 'uiSettingsCommaSeparated');
$config -> setDescription('facebook.scope', localize('Comma separated values, complete list is avaliable at: https://developers.facebook.com/docs/reference/login/', 'facebook'));

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
