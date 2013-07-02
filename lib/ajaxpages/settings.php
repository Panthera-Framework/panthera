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
        $template -> push('constants', $const['user']);
        $template -> push('settings_list', $options);
        $template -> push('acl_list', $user->acl->listAll());
        $template -> push('action', 'system_info');

    /**
      * User account details
      *
      * @hook user.fields
      * @author Damian Kęska
      */

    } elseif (@$_GET['action'] == 'my_account') {
        $tpl = "myaccount.tpl";

        if (isset($_GET['uid']) AND ($user->attributes->admin OR $user->attributes->superuser))
        {
            $u = getUserById($_GET['uid']);
            $template -> push('user_uid', '&uid=' .$_GET['uid']);
        } else {
            $u = $user;
        }

        if ($u != $user)
            $panthera -> template -> push ('dontRequireOld', True);

        // if we arent superuser we cannot view superuser profiles
        if (($u -> attributes -> superuser and !$user->attributes->superuser) or !$u->exists())
        {
            $template -> display('no_page.tpl');
            pa_exit();
        }

        if (isset($_POST['aclname']))
        {
            if ($user->attributes->admin or $user->attributes->superuser)
            {
                if ($_POST['value'] == "1")
                    $aclValue = True;
                else
                    $aclValue = False;

                $u -> acl -> set($_POST['aclname'], $aclValue);

                ajax_exit(array('status' => 'success', 'name' => $_POST['aclname'], 'value' => $aclValue, 'post_value' => $_POST['value'], 'result' => $u -> acl -> get($_POST['aclname'])));
            } else {
                ajax_exit(array('status' => 'success', 'message' => localize('You are not allowed to manage permissions', 'messages')));
            }
        }

        if (isset($_GET['changepassword']))
        {
            if ($u->checkPassword($_POST['old_passwd']) == True or ($u != $panthera->user and ($panthera->user->attributes -> admin or $panthera -> attributes -> root)))
            {
                  if ($_POST['new_passwd'] == $_POST['retyped_newpasswd'])
                  {
                        if ($u->changePassword($_POST['new_passwd']))
                        {
                            $u->save();
                            ajax_exit(array('status' => 'success', 'message' => localize('Password has been successfully changed')));
                            pa_exit();
                        } else {
                            print(json_encode(array('status' => 'failed', 'message' => localize('Error with changing password'))));
                            pa_exit();
                        }
                  } else {
                        print(json_encode(array('status' => 'failed', 'message' => localize('Passwords are not identical'))));
                        pa_exit();
                  }
            } else {
                  print(json_encode(array('status' => 'failed', 'message' => localize('Incorrect old password'))));
                  pa_exit();
            }
        }

        $locales = $panthera->locale->getLocales();
        $localesActive = $panthera->locale->getLocales();

        // hide disabled locales
        foreach ($locales as $Key => $Value)
        {
            if($Value == True)
                $localesActive[$Key] = $Value;
        }

        if (isset($_GET['changelanguage'])) {
            if (array_key_exists($_POST['language'], $localesActive)) {
                  $u -> language = $_POST['language'];
                  ajax_exit(array('status' => 'success'));

            } else {
                  ajax_exit(array('status' => 'failed', 'message' => localize('Language variable is empty')));
            }
            pa_exit();
        }

        if ($u->profile_picture == '')
            $u->profile_picture = '{$PANTHERA_URL}/images/default_avatar.png';


        $template -> push('locales_added', $localesActive);
        $template -> push('action', 'my_account');
        $template -> push('user_login', $u->login);
        $template -> push('avatar_dimensions', explode('x', $panthera -> config -> getKey('avatar_dimensions', '80x80', 'string')));
        $template -> push('profile_picture', pantheraUrl($u->profile_picture));
        $template -> push('full_name', $u->full_name);
        $template -> push('primary_group', $u->primary_group);
        $template -> push('joined', $u->joined);
        $template -> push('language', $u->language);

        // custom fields
        $template -> push('user_fields', $panthera -> get_filters('user.fields', array()));

        $aclList = array();
        $userTable = $u->acl->listAll();

        if ($user -> attributes -> admin or $user -> attributes -> superuser )
            $template -> push('allow_edit_acl', True);

        $permissionsTable = $panthera->listPermissions();

        foreach ($userTable as $key => $value)
        {
            $name = $key;

            // translating name to description
            if (isset($permissionsTable[$key]))
                $name = $permissionsTable[$name]['desc'];

            if ($value == True)
                $aclList[$key] = array('name' => $name, 'value' => localize('Yes'), 'active' => 1);
            else
                $aclList[$key] = array('name' => $name, 'value' => localize('No'), 'active' => 0);
        }

        if ($panthera->config->getKey('usr_view_acl_table', false, 'bool') or $user->attributes->admin or $user->attributes->superuser)
        {
            foreach ($permissionsTable as $key => $value)
            {
                if (isset($aclList[$key]))
                    continue;

                $acl = getUserRightAttribute($u, $key);
                $active = 0;

                if ($acl == True)
                {
                    $active = 1;
                    $val = localize('Yes');
                } else
                    $val = localize('No');

                $aclList[$key] = array('name' => $value['desc'], 'value' => $val, 'active' => $active);
            }
        }

        $template -> push('aclList', $aclList);

    } elseif (@$_GET['action'] == 'users') {
        if (!getUserRightAttribute($user, 'can_see_users_table'))
        {
            $template->display('no_access.tpl');
            pa_exit();
        }

        if (@$_GET['subaction'] == 'show_table')
            $tpl = "settings_showtable.tpl";
        else
            $tpl = "users.tpl";

        /*
            // count pages
            $pager = new Pager($count, $panthera->config->getKey('max_qmsg', 10));
            $pager -> maxLinks = 6;
            $limit = $pager -> getPageLimit($page);

            // pager display
            $template -> push('pager', $pager->getPages($page));
            $template -> push('page_from', $limit[0]);
            $template -> push('page_to', $limit[1]);
        */

        // $count = getQuickMessages(array('language' => $user->language), False);

        $usersPage = (intval(@$_GET['usersPage']));

        if ($usersPage < 0)
                $usersPage = 0;

        $maxOnPage = $panthera->config->getKey('paging_users_max', 25, 'int');

        if (intval($maxOnPage) < 2)
        {
            $maxOnPage = 25;
            $panthera->config->setKey('paging_users_max', 25);
        }


        $pager = new Pager(getUsers('', False), $maxOnPage);
        $pager -> maxLinks = 6;
        $limit = $pager -> getPageLimit($usersPage);

        // this we will pass to template
        $users = array();
        $usersData = getUsers('', $limit[1], $limit[0]);

        foreach ($usersData as $w)
        {
            // superuser cant be listed, it must be hidden
            if ($w -> attributes -> superuser and !$user->attributes->superuser)
                continue;

            $users[] = array('login' => $w->login, 'full_name' => $w->full_name, 'primary_group' => $w->primary_group, 'joined' => $w->joined, 'language' => $w->language, 'id' => $w->id);
        }

        // find all recent 1-10 users
        // var_dump(getUsers('', 10, 0));

        // find all recent 1-10 users with default language set to "polski"
        // var_dump(getUsers(array('language' => 'polski'), 10, 0));

        /*for ($i=0; $i<100; $i++)
        {
            $users[] = array('login' => 'test', 'full_name' => 'Testowy, nie istniejący user', 'primary_group' => 'non_existing', 'joined' => 'today', 'language' => 'Marsjański', 'id' => 1);
        }*/

        $template -> push('users_list', $users);
        $template -> push('view_users', True);
        $template -> push('pager', $pager->getPages($usersPage));
        $template -> push('users_from', $limit[0]);
        $template -> push('users_to', $limit[1]);
    } elseif (@$_GET['action'] == 'changeLocale') {
        if ($panthera -> locale -> setLocale($_POST['locale']) == $_POST['locale'])
        {
              $panthera->session->set('language', $_POST['locale']);
              ajax_exit(array('status' => 'success'));
        } else {
              ajax_exit(array('status' => 'error'));
        }
    }
