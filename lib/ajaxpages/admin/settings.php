<?php
/**
  * Settings
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

    $tpl = 'settings.tpl';

    $panthera -> locale -> loadDomain('settings');

    if (@$_GET['action'] == 'system_info')
    {
        if (!getUserRightAttribute($user, 'can_see_system_info'))
        {
            $noAccess = new uiNoAccess; $noAccess -> display();
            pa_exit();
        }
        
        $template -> push('action', '');
        $template -> push('user_uid', '');
        $template -> push('locales', $panthera -> locale -> getLocales());
        $template -> push('locale', $panthera -> locale -> getActive());

        $yn = array(0 => localize('False'), 1 => localize('True'));

        $options = array ('template' => $config['template'],
                            'timezone' => $config['timezone'],
                            'System Time' => date('G:i:s d.m.Y'),
                            'url' => $panthera->config->getKey('url'),
                            'ajax_url' => $panthera->config->getKey('ajax_url'),
                            '__FILE__' => __FILE__,
                            'PANTHERA_DIR' => PANTHERA_DIR,
                            'SITE_DIR' => SITE_DIR,
                            'Panthera Version' => PANTHERA_VERSION,
                            'Panthera debugger active' => $yn[intval($panthera->config->getKey('debug'))],
                            'Session lifetime' => $panthera->config->getKey('session_lifetime', '3600', 'int'),
                            'Session browser check' => $yn[$panthera->config->getKey('session_useragent')],
                            'Cookie encryption' => $yn[$panthera->config->getKey('cookie_encrypt')],
                            'PHP' => phpversion(),
                            'magic_quotes_gpc' => $yn[intval(ini_get('magic_quotes_gpc'))],
                            'register_globals' => $yn[intval(ini_get('register_globals'))],
                            'session.save_handler' => ini_get('session.save_handler'),
                            'display_errors' => $yn[ini_get('display_errors')],
                            'post_max_size' => ini_get('post_max_size'),
                            'PDO Drivers' => implode(', ', PDO::getAvailableDrivers()),
                            'Template engine' => $panthera->template->engine,
                            'Server software' => $_SERVER['SERVER_SOFTWARE'],
                            'System' => @php_uname());

        /** PHP APC **/

        $options['apc.cache_by_default'] = $yn[intval(ini_get('apc.cache_by_default'))];
        $options['apc.enabled'] = $yn[intval(ini_get('apc.enabled'))];

        /** MEMCACHED **/

        if (class_exists('Memcached'))
            $options['memcached'] = localize('Avaliable');

        /** Xcache **/

        if (extension_loaded('xcache'))
            $options['xcache'] = localize('Avaliable');

        /** Panthera cache system **/

        $options['varCache'] = $panthera->config->getKey('varcache_type', 'db', 'string');
        $options['cache'] = $panthera->config->getKey('cache_type', 'db', 'string');

        /** Constants **/
        $const = get_defined_constants(true);
        $template -> push('const', $const['user']);

        $options = $panthera->get_filters('_ajax_settings', $options);

        if (!defined('DISABLE_BROWSER_DETECTION'))
            $template -> push ('clientInfo', (array)$panthera -> session -> get('clientInfo'));

        $template -> push('constants', $const['user']);
        $template -> push('settings_list', $options);
        $template -> push('acl_list', $user->acl->listAll());
        $template -> push('action', 'system_info');
        
		$titlebar = new uiTitlebar(localize('Panel with main settings and informations about Panthera', 'settings'));
		$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/settings.png', 'left');
		
        $panthera -> template -> display('settings_systeminfo.tpl');
        pa_exit();
    }
    
$defaults = array();
$defaults['system'] = array();
$defaults['system']['database'] = array(
    'link' => '?display=database&cat=admin', 
    'name' => localize('Database management', 'dash'), 
    'description' => localize('Monitor connection status, create backups', 'settings'), 
    'icon' => '{$PANTHERA_URL}/images/admin/menu/db.png' , 
    'linkType' => 'ajax'
);

$defaults['system']['cache'] = array(
    'link' => '?display=cache&cat=admin', 
    'name' => localize('Cache management', 'dash'),
    'description' => localize('Monitor and manage cache settings', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/cache.png' , 
    'linkType' => 'ajax'
);

$defaults['system']['leopard'] = array(
    'link' => '?display=leopard&cat=admin', 
    'name' => localize('Package management', 'dash'),
    'description' => localize('Install or remove Panthera packages', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/package.png' , 
    'linkType' => 'ajax'
);

$defaults['system']['conftool'] = array(
    'link' => '?display=conftool&cat=admin', 
    'name' => localize('Configuration editor', 'dash'), 
    'icon' => '{$PANTHERA_URL}/images/admin/menu/config.png', 
    'linkType' => 'ajax'
);

$defaults['system']['locales'] = array(
    'link' => '?display=locales&cat=admin',
    'name' => localize('Language settings', 'dash'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/locales.png',
    'linkType' => 'ajax'
);

$defaults['system']['plugins'] = array(
    'link' => '?display=plugins&cat=admin',
    'name' => ucfirst(localize('plugins', 'dash')),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/Apps-preferences-plugin-icon.png',
    'linkType' => 'ajax'
);

$defaults['system']['templates'] = array(
    'link' => '?display=templates&cat=admin',
    'name' => ucfirst(localize('templates', 'dash')),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/Icon-template.png',
    'linkType' => 'ajax'
);

$defaults['system']['session'] = array(
    'link' => '?display=settings.session&cat=admin',
    'name' => ucfirst(localize('session', 'dash')),
    'description' => localize('Session, cookies and browser security settings', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/session-icon.png',
    'linkType' => 'ajax'
);

$defaults['system']['mce'] = array(
    'link' => '?display=settings.mce&cat=admin',
    'name' => ucfirst(localize('mce settings', 'dash')),
    'description' => localize('Text editor settings', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/mce.png',
    'linkType' => 'ajax'
);

$defaults['system']['dash'] = array(
    'link' => '?display=settings.dash&cat=admin',
    'name' => localize('Dashboard', 'settings'),
    'description' => localize('Configure Admin Panel main screen', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/dashboard.png',
    'linkType' => 'ajax'
);

$defaults['system']['pager'] = array(
    'link' => '?display=settings.pager&cat=admin',
    'name' => localize('Pager settings', 'settings'),
    'description' => localize('Setup all pagers used in Panthera Framework', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/pager.png',
    'linkType' => 'ajax'
);

// Content section
$defaults['content'] = array();

$defaults['content']['users'] = array(
    'link' => '?display=users&cat=admin',
    'name' => ucfirst(localize('users', 'dash')),
    'description' => localize('Manage system translations', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/users.png',
    'linkType' => 'ajax'
);

$defaults['content']['mailing'] = array(
    'link' => '?display=mailing&cat=admin',
    'name' => localize('Mailing', 'dash'),
    'description' => localize('Send e-mails, manage it\'s configuration', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/mail-replied.png',
    'linkType' => 'ajax'
);

$defaults['content']['newsletter'] = array(
    'link' => '?display=settings.newsletter&cat=admin',
    'name' => localize('Newsletter settings', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/newsletter.png',
    'linkType' => 'ajax'
);

$defaults['content']['menuedit'] = array(
    'link' => '?display=menuedit&cat=admin',
    'name' => localize('Menu editor', 'dash'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png',
    'linkType' => 'ajax'
);

$defaults['content']['langtool'] = array(
    'link' => '?display=langtool&cat=admin',
    'name' => ucfirst(localize('translates', 'dash')),
    'description' => localize('Manage system translations', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/langtool.png',
    'linkType' => 'ajax'
);

$defaults['content']['passwordrecovery'] = array(
    'link' => '?display=settings.passwordrecovery&cat=admin',
    'name' => ucfirst(localize('password recovery', 'dash')),
    'description' => localize('Default mail title, content, password length', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/password-recovery.png',
    'linkType' => 'ajax'
);

$defaults['content']['sitesettings'] = array(
    'link' => '?display=settings.site&cat=admin',
    'name' => localize('Site configuration', 'settings'),
    'description' => localize('Website URL address, title', 'settings'),
    'icon' => '{$PANTHERA_URL}/images/admin/menu/site-settings.png',
    'linkType' => 'ajax'
);

// titlebar
$titlebar = new uiTitlebar(localize('Settings', 'settings'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/settings.png', 'left');

$sBar = new uiSearchbar('uiTop');
//$sBar -> setMethod('POST');
$sBar -> setQuery($_GET['query']);
$sBar -> setAddress('?display=settings&cat=admin');
$sBar -> navigate(True);
    
$panthera -> logging -> startTimer();
$defaultsSum = hash('md4', serialize($defaults));

// settings main menu
$listDB = $panthera -> config -> getKey('settings.items', $defaults, 'array', 'settings');

if ($panthera -> config -> getKey('settings.items.checksum') != $defaultsSum)
{
    $listDB = array_merge($listDB, $defaults);
    $panthera -> config -> setKey('settings.items', $defaults, 'array', 'settings');
    $panthera -> config -> setKey('settings.items.checksum', $defaultsSum, 'string', 'settings');
    $panthera -> logging -> output ('Updated default settings items', 'settings');
}

if (!$_GET['query'])
{
    $list = $listDB;
} else {
    $list = array();

    foreach ($listDB as $sectionName => $section)
    {
        foreach ($section as $key => $item)
        {
            if (stripos($item['name'], $_GET['query']) !== False or stripos($item['description'], $_GET['query']) !== False or stripos($item['link'], 'display='.$_GET['query']) !== False)
            {
                $list[$sectionName][$key] = $item;
            }
        }
    }
}

$panthera -> template -> push('items', $list);
$panthera -> template -> display('settings.tpl');
pa_exit();
