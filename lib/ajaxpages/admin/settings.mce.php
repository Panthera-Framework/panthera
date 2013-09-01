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

$panthera -> config -> getKey('cookie_encrypt', 1, 'bool');
$panthera -> locale -> loadDomain('settings');
$panthera -> locale -> loadDomain('mce');

// titlebar
$titlebar = new uiTitlebar(localize('Text editor settings', 'settings'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/mce.png', 'left');

$editors = array();
$mceConfig = uiMce::getConfiguration();

foreach (uiMce::getAvaliableEditors() as $editor)
{
    $editors[$editor] = $editor;
}

// default values
//$panthera -> config -> getKey('mce.css', '{$PANTHERA_URL}/css/style.css', 'string', 'mce');
$panthera -> config -> getKey('mce.default', 'tinymce', 'string', 'mce');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('mce');

$config -> add('mce.default', localize('Default Wysiwyg editor', 'mce'), $editors);
$config -> add('mce.css', localize('Style CSS', 'mce'));
$config -> setDescription('mce.css', localize('Address to CSS style to use inside of text editor', 'mce'));

// add mce specific configuration
foreach ($mceConfig['configuration'] as $key => $value)
{
    if (!$value['values'])
    {
        $value['values'] = '';
    }
    
    $config -> add('mce.' .uiMce::getActiveEditor(). '.' .$key, localize($key, 'mce'), $value['values']);
}

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
