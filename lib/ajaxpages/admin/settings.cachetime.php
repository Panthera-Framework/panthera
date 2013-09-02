<?php
/**
  * Session configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_session_settings'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('cache');

// titlebar
$titlebar = new uiTitlebar(localize('Cache life time settings', 'cache'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/cache.png', 'left');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('dash');
$config -> add('cache_timing', '');
$config -> setFieldType('cache_timing', 'packaged');

// descriptions
//$config -> setDescription('site_title', localize('Default site title displayed on every page', 'settings'));

// handlers
$config -> setFieldSaveHandler('dash.widgets', 'uiSettingsMultipleSelectBoolField');

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
