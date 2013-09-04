<?php
/**
  * Custom pages configuration
  *
  * @package Panthera\core\ajaxpages\settings.custompages
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

$panthera -> locale -> loadDomain('settings');
$panthera -> locale -> loadDomain('custompages');

/**
  * Filter pager and cache timing variables to show only custom pages related entries
  *
  * @package Panthera\core\ajaxpages\settings.custompages
  * @param string $input
  * @return array 
  * @author Damian Kęska
  */

function filterUiSettingsAdd ($input)
{
    // input = $_a, $fKey, $setting, $label, $validator, $value
    
    if (strpos($input[1], 'w_') === 0)
    {
        $input[0] = False; // return false
        return $input;
    }
    
    if (strpos($input[1], '__p_pager') !== False)
    {
        if (!stripos($input[1], 'custompage') !== False)
        {
            $input[0] = False; // return false
            return $input;
        }
    }
    
    if (strpos($input[1], '__p_cache_timing') !== False)
    {
        if (!stripos($input[1], 'custompage') !== False)
        {
            $input[0] = False; // return false
            return $input;
        }
    }
    
    return $input;
}

$panthera -> add_option('ui.settings.add', 'filterUiSettingsAdd');

// titlebar
$titlebar = new uiTitlebar(localize('Static pages configuration', 'settings'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/custom-pages.png', 'left');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('*');
$config -> add('custompage', localize('Custom pages SEO urls configuration', 'custompages'));
$config -> setFieldType('custompage', 'packaged');
$config -> setDescription('custompage', localize('{$id} tag will be replaced to represent selected element', 'custompages'));

// add pager configuration
$config -> add('pager', localize('Admin Panel pager settings', 'settings'));
$config -> setFieldType('pager', 'packaged');

// cache timing
$config -> add('cache_timing', localize('Cache life time', 'custompages'));
$config -> setFieldType('cache_timing', 'packaged');


$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
