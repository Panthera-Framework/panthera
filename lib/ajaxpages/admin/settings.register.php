<?php
/**
  * User registration options
  *
  * @package Panthera\core\ajaxpages\settings.passwordrecovery
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_registration'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$locales = $panthera -> locale -> getLocales();
$panthera -> template -> push ('languages', $locales);
$panthera -> template -> push ('activeLanguage', $panthera -> locale -> getFromOverride($_GET['language']));

// some defaults
$panthera -> config -> getKey('register.group', 'users', 'string', 'register');
$panthera -> config -> getKey('register.avatar', '{$PANTHERA_URL}/images/default_avatar.png', 'string', 'register');
$panthera -> config -> getKey('register.confirmation.required', 1, 'bool', 'register');
$panthera -> config -> getKey('register.open', 0, 'bool', 'register');
$panthera -> config -> getKey('register.verification.message', array('english' => 'Hello {$userName}, here is a link to confirm your account '.pantheraUrl('{$PANTHERA_URL}/pa-login.php?ckey=', False, 'frontend').'{$key}&login={$userName}'), 'array', 'register');
$panthera -> config -> getKey('register.verification.title', array('english' => 'Account confirmation'), 'array', 'register');
 
// include a title bar
$titlebar = new uiTitlebar(localize('User registration settings', 'register'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/register.png', 'left');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('register');
$config -> languageSelector(True);
$config -> add('register.open', localize('Registration open', 'register'));
$config -> setFieldType('register.open', 'bool');
$config -> add('register.group', localize('Default group name', 'register')); // please note that "." is replaced to "_-_"
$config -> add('register.avatar', localize('Default avatar', 'register'));
$config -> add('register.confirmation.required', localize('Require mail confirmation', 'register'));

// mail message
$config -> add('register.verification.message', localize('Mail message', 'register'));
$config -> setFieldSaveHandler('register.verification.message', 'uiSettingsMultilanguageField');
$config -> setDescription('register.verification.message', '{$key}, {$userName}, {$userID}');

// mail title
$config -> add('register.verification.title', localize('Mail title', 'register'));
$config -> setFieldSaveHandler('register.verification.title', 'uiSettingsMultilanguageField');
$config -> setDescription('register.verification.title', '{$key}, {$userName}, {$userID}');

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
