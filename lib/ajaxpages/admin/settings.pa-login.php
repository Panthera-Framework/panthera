<?php
/**
  * pa-login front controller settings
  *
  * @package Panthera\core\ajaxpages\settings.pa-login
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_login_settings'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

// some defaults
$panthera -> config -> getKey('login.failures.max', 5, 'int', 'pa-login');
$panthera -> config -> getKey('redirect_after_login', 'index.php', 'string', 'pa-login');
$panthera -> config -> getKey('login.failures.bantime', 300, 'int', 'pa-login');
 
// include a title bar
$titlebar = new uiTitlebar(localize('User registration settings', 'register'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/register.png', 'left');

// load uiSettings with "pa-login" config section
$config = new uiSettings('pa-login');
$config -> add('login.failures.max', localize('Maximum number of failures', 'register'));

$config -> add('redirect_after_login', localize('Login redirection', 'register'));
$config -> setDescription('redirect_after_login', localize('Where to redirect user right after login (internal url)', 'palogin'));

$config -> add('login.failures.bantime', localize('Block user when reaches maximum number of login failures', 'register'));
$config -> setDescription('login.failures.bantime', localize('In seconds', 'palogin'));

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
