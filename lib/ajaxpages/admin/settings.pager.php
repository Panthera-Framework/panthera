<?php
/**
  * Pager configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_pager_settings'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('settings');

// titlebar
$titlebar = new uiTitlebar(localize('Pager settings', 'settings'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/pager.png', 'left');

// defaults
$panthera -> config -> getKey('pager', array(), 'array', 'ui');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('ui');
$config -> add('pager', localize('Pager settings per element', 'settings'));
$config -> setFieldType('pager', 'packaged');

// handlers
$config -> setFieldSaveHandler('pager', 'uiSettingsMultipleSelectBoolField');

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
