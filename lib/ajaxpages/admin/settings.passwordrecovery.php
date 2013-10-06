<?php
/**
  * Password recovery settings
  *
  * @package Panthera\core\ajaxpages\settings.passwordrecovery
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_password_recovery'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$locales = $panthera -> locale -> getLocales();
$panthera -> template -> push ('languages', $locales);
$panthera -> template -> push ('activeLanguage', $panthera -> locale -> getFromOverride($_GET['language']));

// some defaults
$panthera -> config -> getKey('recovery.mail.title', array (
    'english' => 'Password recovery'
), 'array', 'passwordrecovery');
$panthera -> config -> getKey('recovery.passwd.length', 12, 'int', 'passwordrecovery');
$panthera -> config -> getKey('recovery.key.length', 32, 'int', 'passwordrecovery');
$panthera -> config -> getKey('recovery.mail.content', array(
    'english' => 'You requested a new password. If you want to change your current password to "{$recovery_passwd}" please visit this url: {$PANTHERA_URL}/pa-login.php?key={$recovery_key}'
), 'array', 'passwordrecovery');
 
// include a title bar
$titlebar = new uiTitlebar(localize('Password recovery settings', 'passwordrecovery'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/password-recovery.png', 'left');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('passwordrecovery');
$config -> add('recovery.passwd.length', localize('New password length', 'passwordrecovery'), new integerRange(4, 32)); // please note that "." is replaced to "_-_"
$config -> add('recovery.key.length', localize('Recovery id length', 'passwordrecovery'), new integerRange(4, 32));
$config -> add('recovery.mail.content', localize('Mail content', 'passwordrecovery'));
$config -> add('recovery.mail.title', localize('Mail message title', 'passwordrecovery'));
$config -> setFieldSaveHandler('recovery.mail.content', 'uiSettingsMultilanguageField');
$config -> setFieldSaveHandler('recovery.mail.title', 'uiSettingsMultilanguageField');
$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.passwordrecovery.tpl');
pa_exit();
