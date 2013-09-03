<?php
/**
  * Newsletter configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_newsletter_settings'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('settings');
$panthera -> locale -> loadDomain('newsletter');

// titlebar
$titlebar = new uiTitlebar(localize('Newsletter settings', 'settings'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/newsletter.png', 'left');

// defaults
$panthera->config->getKey('nletter.confirm.content', array('english' => 'Hi, {$userName}. <br>Please confirm your newsletter subscription at {$PANTHERA_URL}/newsletter.php?confirm={$activateKey} <br>Your unsubscribe url: {$PANTHERA_URL}/newsletter.php?unsubscribe={$unsubscribeKey}'), 'array', 'newsletter');
$panthera->config->getKey('nletter.confirm.topic', array('english' => 'Please confirm your newsletter subscription'), 'array', 'newsletter');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('newsletter');
$config -> languageSelector(True);
$config -> add('nletter.confirm.content', localize('Message content', 'newsletter'));
$config -> add('nletter.confirm.topic', localize('Topic', 'newsletter'));
$config -> setFieldType('nletter.confirm.content', 'wysiwyg');
$config -> setFieldSaveHandler('nletter.confirm.content', 'uiSettingsMultilanguageField');
$config -> setFieldSaveHandler('nletter.confirm.topic', 'uiSettingsMultilanguageField');
$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
