<?php
/**
  * General site configuration page
  *
  * @package Panthera\core\ajaxpages\settings.session
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */
  
$panthera = pantheraCore::getInstance();

if (!getUserRightAttribute($user, 'can_update_config_overlay') and !getUserRightAttribute($user, 'can_edit_session_settings'))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

function settingsUrlHandler($action, $key, $value)
{
    $panthera = pantheraCore::getInstance();

    if ($action == 'save')
    {
        $ctx = stream_context_create(array( 
            'http' => array( 
                'timeout' => 5 
                ) 
            ) 
        ); 
    
        if (!file_get_contents($value. '/_ajax.php', 0, $ctx))
        {
            throw new Exception('Cannot connect to selected URL (' .$key. ' key)');
        }
        
        return $value;
    }
    
    return $panthera -> config -> getKey($key);
}

$panthera -> config -> getKey('cookie_encrypt', 1, 'bool');
$panthera -> locale -> loadDomain('settings');

// defaults
$panthera -> config -> getKey('site_title', array('english' => 'Panthera Framework'), 'array');
$panthera -> config -> getKey('site_description', array('english' => 'Another site based on Panthera Framework'), 'array');
$panthera -> config -> getKey('site_metas', array('english' => 'another, panthera, framework, based, site'), 'array');

// titlebar
$titlebar = new uiTitlebar(localize('Site configuration', 'settings'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/site-settings.png', 'left');

// load uiSettings with "passwordrecovery" config section
$config = new uiSettings('*');
$config -> languageSelector(True);
$config -> add('site_title', localize('Site title', 'settings'));
$config -> add('site_description', localize('Description', 'settings'));
$config -> add('site_metas', localize('Tags', 'settings'));
$config -> add('dateFormat', localize('Date format', 'settings'));
$config -> add('url', localize('Website main directory url', 'settings'));
$config -> add('ajax_url', localize('Ajax URL', 'settings'));
$config -> add('redirect_after_login', localize('Redirect after login', 'settings'));
$config -> add('crontab_key', localize('Crontab key', 'settings'));
$config -> add('gmaps_key', localize('Google Maps API key', 'settings'));
$config -> add('debug', localize('Debugger', 'settings'), new integerRange(0, 1));

// descriptions
$config -> setDescription('site_title', localize('Default site title displayed on every page', 'settings'));
$config -> setDescription('site_description', localize('Site description', 'settings'));
$config -> setDescription('site_metas', localize('Meta tags', 'settings'));
$config -> setDescription('ajax_url', localize('Address to _ajax.php front controller', 'settings'));
$config -> setDescription('url', localize('A full domain with protocol included eg. http://example.com', 'settings'));
$config -> setDescription('redirect_after_login', localize('Redirect unprivileged user to this page after login (relative path)', 'settings'));
$config -> setDescription('crontab_key', localize('A secret key used to execute cron jobs', 'settings'));
$config -> setDescription('gmaps_key', localize('Google APIs key', 'settings'));
$config -> setDescription('debug', localize('Rich featured and lightweight Panthera debugger', 'settings'));
$config -> setDescription('dateFormat', localize('Preferred, system wide date format accessible via $panthera -> dateFormat', 'settings'));

// handlers
$config -> setFieldSaveHandler('url', 'settingsUrlHandler');
$config -> setFieldSaveHandler('site_title', 'uiSettingsMultilanguageField');
$config -> setFieldSaveHandler('site_description', 'uiSettingsMultilanguageField');
$config -> setFieldSaveHandler('site_metas', 'uiSettingsMultilanguageField');

$result = $config -> handleInput($_POST);

if (is_array($result))
{
    ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));
} elseif ($result === True) {
    ajax_exit(array('status' => 'success'));
}

$panthera -> template -> display('settings.generic_template.tpl');
pa_exit();
