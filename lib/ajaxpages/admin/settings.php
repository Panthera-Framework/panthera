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

    $template -> push('action', '');
    $template -> push('user_uid', '');
    $template -> push('locales', $panthera -> locale -> getLocales());
    $template -> push('locale', $panthera -> locale -> getActive());

    if (@$_GET['action'] == 'system_info')
    {
        if (!getUserRightAttribute($user, 'can_see_system_info'))
        {
            $template->display('no_access.tpl');
            pa_exit();
        }

        $tpl = "settings_systeminfo.tpl";

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
    }