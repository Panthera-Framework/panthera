<?php
/**
  * Users
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

$panthera -> locale -> loadDomain('users');

$panthera -> template -> push('action', '');
$panthera -> template -> push('user_uid', '');
$panthera -> template -> push('locales', $panthera -> locale -> getLocales());
$panthera -> template -> push('locale', $panthera -> locale -> getActive());

$isAdmin = checkUserPermissions($panthera->user, True);

$permissions = array(
    'canBlockUser' => $isAdmin,
    'canSeePermissions' => $isAdmin,
    'canEditOthers' => $isAdmin
);

$panthera -> template -> push('permissions', $permissions);

/**
  * User account details
  *
  * @hook user.fields
  * @author Damian Kęska
  */

if ($_GET['action'] == 'account') {

    $panthera -> importModule('meta');

    if (isset($_GET['uid']) and checkUserPermissions($panthera->user, True)) {
        $u = getUserById($_GET['uid']);
        $panthera -> template -> push('user_uid', '&uid=' .$_GET['uid']);
    } else {
        $u = $panthera->user;
    }
    
    if ($u->id == $user->id)
        $permissions['canBlockUser'] = False;

    if ($u != $user)
        $panthera -> template -> push ('dontRequireOld', True);

    // if we arent superuser we cannot view superuser profiles
    if (($u -> acl -> get('superuser') and !$panthera->user->acl->get('superuser')) or !$u->exists()) 
    {
        $noAccess = new uiNoAccess;
        $noAccess -> addMetas(array('superuser'));
        $noAccess -> display();
    }
    
    // user cannot ban superuser or other admin
    if (($u->acl->get('superuser') or $u->acl->get('admin')) and !$panthera->user->acl->get('superuser'))
    {
        $permissions['canBlockUser'] = False;
        $panthera -> template -> push('permissions', $permissions);
    }
    
    
    // ban/unban user
    if (isset($_POST['ban']))
    {
        if ($isAdmin) 
        {
            $u = new pantheraUser('id', $_GET['uid']);
            $banned = $u->isBanned();
            
            // user cannot ban itself
            if ($u == $panthera->user)
            {
                $noAccess = new uiNoAccess;
                $noAccess -> display();
            }
            
            // user cannot ban superuser or other admin
            if (!$permissions['canBlockUser'])
            {
                $noAccess = new uiNoAccess;
                $noAccess -> display();
            }
            
            
            if ($u -> isBanned(!$banned) == !$banned)
            {
                $u -> save();
                ajax_exit(array('status' => 'success', 'value' => !$banned));
            } else {
                $noAccess = new uiNoAccess;
                $noAccess -> addMetas(array('admin'));
                $noAccess -> display();
            }
            
        } else {
            $noAccess = new uiNoAccess;
            $noAccess -> addMetas(array('admin'));
            $noAccess -> display();
        }
        
        pa_exit();
    }

    if (isset($_POST['aclname'])) 
    {
        if (strlen($_POST['aclname']) < 3)
        {
            ajax_exit(array('status' => 'failed', 'message' => localize('Too short ACL attribute name', 'users')));
        }
        
        if (checkUserPermissions($panthera->user, True)) 
        {
            if ($_POST['value'] == "1")
                $aclValue = True;
            else
                $aclValue = False;

            $u -> acl -> set($_POST['aclname'], $aclValue);

            ajax_exit(array('status' => 'success', 'name' => $_POST['aclname'], 'value' => $aclValue, 'post_value' => $_POST['value'], 'result' => $u -> acl -> get($_POST['aclname'])));
        } else {
            ajax_exit(array('status' => 'failed', 'message' => localize('You are not allowed to manage permissions', 'messages')));
        }
    }

    $locales = $panthera->locale->getLocales();
    $localesActive = $panthera->locale->getLocales();

    // hide disabled locales
    foreach ($locales as $Key => $Value) {

        if($Value == True)
            $localesActive[$Key] = $Value;
    }
        
    $groups = pantheraGroup::listGroups();
    $groupsTpl = array();

    foreach ($groups as $group) {
        $groupsTpl[] = array('name' => $group->name);
    }

    $template -> push('locales_added', $localesActive);
    $template -> push('action', 'my_account');
    $template -> push('id', $u->id);
    $template -> push('user_login', $u->login);
    $template -> push('avatar_dimensions', explode('x', $panthera -> config -> getKey('avatar_dimensions', '80x80', 'string')));
    $template -> push('profile_picture', pantheraUrl($u->profile_picture));
    $template -> push('full_name', $u->full_name);
    $template -> push('primary_group', $u->primary_group);
    $template -> push('joined', $u->joined);
    $template -> push('user_language', $u->language);
    $template -> push('isBanned', $u->isBanned());
    $template -> push('jabber', $u->jabber);
    $template -> push('email', $u->mail);
    $template -> push('groups', $groupsTpl);

    // custom fields
    $template -> push('user_fields', $panthera -> get_filters('user.fields', array()));

    $aclList = array();
    $userTable = $u->acl->listAll();

    if (checkUserPermissions($panthera->user, True))
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

    if ($panthera->config->getKey('usr_view_acl_table', false, 'bool') or checkUserPermissions($panthera->user, True)) 
    {
        foreach ($permissionsTable as $key => $value) 
        {

            if (isset($aclList[$key]))
                continue;

            $acl = getUserRightAttribute($u, $key);
            $active = 0;

            if ($acl == True) {
                $active = 1;
                $val = localize('Yes');
            } else {
                $val = localize('No');
                $aclList[$key] = array('name' => $value['desc'], 'value' => $val, 'active' => $active);
            }
        }

        $template -> push('aclList', $aclList);
    }
    
    $template -> push('permissions', $permissions);
    
    $titlebar = new uiTitlebar(localize('Panel with informations about user.', 'users'));
    $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/users.png', 'left');
    
    $panthera -> template -> display('users_account.tpl');
    pa_exit();

/**
  * Create a new group
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'createGroup') {
    // check user permissions
    if (!checkUserPermissions($panthera->user, True)) 
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
    }

    if (!checkUserPermissions($panthera->user, True)) {
        ajax_exit(array('status' => 'failed', 'message' => localize('403 - Access denied')));
    }

    $groupName = $_POST['name'];
    $groupDescription = $_POST['description'];

    try {
        if (!pantheraGroup::create($groupName, $groupDescription))
            ajax_exit(array('status' => 'failed', 'message' => localize('Group propably already exists', 'acl')));

        ajax_exit(array('status' => 'success', 'name' => $groupName, 'description' => $groupDescription));
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', 'message' => localize('Invalid group name, only alphanumeric characters and "_" is allowed', 'acl')));
    }

/**
  * Remove a group
  *
  * @author Damian Kęska
  */

} elseif ($_GET['action'] == 'removeGroup') {
    // check user permissions
    if (!checkUserPermissions($panthera->user, True)) 
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
    }

    $groupName = $_POST['group'];

    try {
        if(!pantheraGroup::remove($groupName))
            ajax_exit(array('status' => 'failed'));

        ajax_exit(array('status' => 'success', 'name' => $groupName));
    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', localize('Cannot remove group', 'acl')));
    }

/**
  * Save information about user to database
  *
  * @author Mateusz Warzyński
  */

} elseif ($_GET['action'] == 'edit_user') {
    
    
    if ($isAdmin)
    {
        if (strlen($_POST['uid']) > 0)
            $u = getUserById($_POST['uid']);
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find user by selected id', 'users')));
    } else {
        $u = $panthera -> user;
    }
    
    /**
      * Change password
      *
      * @author Damian Kęska
      */
    
    if ($_POST['passwd']) 
    {
        if (strlen($_POST['passwd']) > 6) 
        {
            if ($_POST['passwd'] == $_POST['retyped_passwd']) 
            {
                if ($u->changePassword($_POST['passwd'])) 
                {
                    $u->save();
                } else {
                    ajax_exit(array('status' => 'failed', 'message' => localize('Error with changing password')));
                }
                
            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Passwords are not identical')));
                pa_exit();
            }
        } else
            ajax_exit(array('status' => 'failed', 'message' => localize('Password is too short!', 'users')));
    }

    if (strlen($_POST['full_name']) > 4)
    {
        $u -> full_name = filterInput($_POST['full_name'], 'strip');
    } else {
        ajax_exit(array('status' => 'failed', 'message' => localize('Full name is too short', 'users')));
    }
    
    if (strlen($_POST['avatar']) > 6)
    {
        $u -> profile_picture = filterInput($_POST['avatar'], 'strip');
    }

    //     
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $u -> mail = $_POST['email'];
    }
    
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $u -> jabber = $_POST['jabber'];
    }
    
    $languages = $panthera -> locale -> getLocales();

    if (isset($languages[$_POST['language']]))
    {
        $u -> language = $_POST['language'];
    }
    
    if ($isAdmin)
    {
        $u -> primary_group = $_POST['primary_group'];
    }
    
    $u -> save();
    
    ajax_exit(array('status' => 'success', 'message' => 'Information about user has been updated'));
    
/**
  * Remove an user (by id)
  *
  * @author Mateusz Warzyński
  */

} elseif ($_GET['action'] == 'removeUser') {
    // check user permissions
    if (!$isAdmin) 
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
    }

    $id = $_POST['id'];
    
    $usersPage = (intval(@$_GET['usersPage']));
    
    if ($usersPage < 0)
        $usersPage = 0;
    
    $sid = 'search:' .hash('md4', $_GET['hash']);
    $panthera -> cache -> set($sid, NULL);

    try {
        $cUser = getCurrentUser();
        if ($cUser->id == $id)
            ajax_exit(array('status' => 'failed', 'message' => localize('You can not remove yourself', 'users')));

        if (removeUser($id))
            ajax_exit(array('status' => 'success', 'message' => localize('User has been removed', 'users')));

    } catch (Exception $e) {
        ajax_exit(array('status' => 'failed', localize('Cannot remove user', 'users')));
    }

/**
  * Add new user
  *
  * @author Mateusz Warzyński
  */


} elseif ($_GET['action'] == 'add_user') {
    // check permissions
    if (!$isAdmin)
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('No rights to execute this action', 'permissions')));
        pa_exit();
    }

    if (strlen($_POST['login']) > 2)
        $login = $_POST['login'];
    else
        ajax_exit(array('status' => 'failed', 'message' => localize('Login is too short!', 'users')));

    if (strlen($_POST['passwd']) > 6) {
            
        $password = encodePassword($_POST['passwd']);
        
        if (!verifyPassword($_POST['retyped_passwd'], $password))
            ajax_exit(array('status' => 'failed', 'message' =>  localize('Passwords does not match', 'users')));
        
    } else {
        ajax_exit(array('status' => 'failed', 'message' => localize('Password is too short!', 'users')));
    }
    
    if (strlen($_POST['full_name']) > 4)
        $full_name = $_POST['full_name'];
    else
        ajax_exit(array('status' => 'failed', 'message' => localize('Full name is too short', 'users')));

    if (strlen($_POST['avatar']) > 6)
        $avatar = $_POST['avatar'];
    else
        $avatar = '{$PANTHERA_URL}/images/default_avatar.png';

    $mail = $_POST['email'];
    $jabber = $_POST['jabber'];

    $language = $_POST['language'];
    $primary_group = $_POST['primary_group'];

    $attributes = array();

    if (createNewUser($login, $password, $full_name, $primary_group, $attributes, $language, $mail, $jabber, $avatar)) {
        $sid = 'search:' .hash('md4', $_POST['hash']);
        $panthera -> cache -> set($sid, NULL);
        ajax_exit(array('status' => 'success', 'message' => localize('User has been successfully added!', 'users')));
    } else {
        ajax_exit(array('status' => 'failed', 'message' => localize('Error while adding user!', 'users')));
    }

/**
  * Show list of users
  *
  * @author Mateusz Warzyński
  */

} else {
        if (!getUserRightAttribute($user, 'can_see_users_table'))
        {
            $noAccess = new uiNoAccess; $noAccess -> display();
            pa_exit();
        }
        
        $tpl = "users.tpl";
        
        $panthera -> importModule('admin/ui.searchbar');
        $panthera -> locale -> loadDomain('search');

        $sBar = new uiSearchbar('uiTop');
        //$sBar -> setMethod('POST');
        $sBar -> setQuery($_GET['query']);
        $sBar -> setAddress('?display=users&cat=admin');
        $sBar -> navigate(True);
        $sBar -> addIcon('{$PANTHERA_URL}/images/admin/ui/permissions.png', '#', '?display=acl&cat=admin&popup=true&name=can_see_users_table', localize('Manage permissions'));
        $sBar -> addSetting('order', localize('Order by', 'search'), 'select', array(
            'id' => array('title' => 'id', 'selected' => ($_GET['order'] == 'id')),
            'login' => array('title' => localize('Login', 'users'), 'selected' => ($_GET['order'] == 'login')),
            'full_name' => array('title' => localize('Full name', 'users'), 'selected' => ($_GET['order'] == 'full_name')),
            'joined' => array('title' => localize('Joined', 'users'), 'selected' => ($_GET['order'] == 'joined')),
            'lastlogin' => array('title' => localize('Last logged in', 'users'), 'selected' => ($_GET['order'] == 'lastlogin')),
            'lastip' => array('title' => localize('Last used IP address', 'users'), 'selected' => ($_GET['order'] == 'lastip')),
            'mail' => array('title' => localize('E-Mail address', 'users'), 'selected' => ($_GET['order'] == 'mail')),
            'primary_group' => array('title' => localize('Group', 'users'), 'selected' => ($_GET['order'] == 'primary_group'))
        ));
        
        $sBar -> addSetting('direction', localize('Direction', 'search'), 'select', array(
            'ASC' => array('title' => localize('Ascending', 'search'), 'selected' => ($_GET['direction'] == 'ASC')),
            'DESC' => array('title' => localize('Descending', 'search'), 'selected' => ($_GET['direction'] == 'DESC'))
        ));

        $usersPage = (intval(@$_GET['usersPage']));
        $order = 'id'; $orderColumns = array('id', 'login', 'full_name', 'joined', 'lastlogin', 'lastip', 'mail', 'primary_group');
        $direction = 'DESC';

        if ($usersPage < 0)
                $usersPage = 0;

        $maxOnPage = $panthera->config->getKey('paging_users_max', 25, 'int');

        if (intval($maxOnPage) < 2)
        {
            $maxOnPage = 25;
            $panthera->config->setKey('paging_users_max', 25);
        }

        $w = new whereClause();
        
        if ($_GET['query'])
        {
            $_GET['query'] = trim(strtolower($_GET['query'])); // strip unneeded spaces and make it lowercase
            $w -> add( 'AND', 'login', 'LIKE', '%' .$_GET['query']. '%');
            $w -> add( 'OR', 'full_name', 'LIKE', '%' .$_GET['query']. '%');
        }
        
        // order by
        if (in_array($_GET['order'], $orderColumns))
        {
            $order = $_GET['order'];
        }
        
        if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
        {
            $direction = $_GET['direction'];
        }
        
        // search identificatior (used to cache results)
        $sid = 'search:' .hash('md4', $_GET['query'].$_GET['order'].$_GET['direction'].$usersPage);
        
        // try to get results from cache
        if ($panthera->cache)
        {
            if ($panthera->cache->exists($sid))
            {
                list($usersTotal, $users) = $panthera -> cache -> get($sid);
                $panthera -> logging -> output('Getting search results ' .$sid. ' from cache', 'pantheraUser');
            }
        }

        // if does not exists in cache
        if (!isset($usersTotal))
        {
            $usersTotal = getUsers($w, False, False, $order);
        }
        
        // uiPager
        $panthera -> importModule('admin/ui.pager');
        $uiPager = new uiPager('users', $usersTotal, 'adminUsersList');
        $uiPager -> setActive($usersPage);
        $uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString($_GET, 'page={$page}', '_'). '\');');
        $limit = $uiPager -> getPageLimit();

        // this we will pass to template
        if (!isset($users))
        {
            $users = array();
            $usersData = getUsers($w, $limit[1], $limit[0], $order, $direction);

            foreach ($usersData as $w)
            {
                // superuser cant be listed, it must be hidden
                if ($w -> attributes -> superuser and !$user->attributes->superuser)
                    continue;

                $users[] = array(
                    'login' => $w->login, 
                    'name' => $w->getName(), 
                    'primary_group' => $w->primary_group, 
                    'joined' => $w->joined, 
                    'language' => $w->language, 
                    'id' => $w->id, 
                    'avatar' => pantheraUrl($w->profile_picture),
                    'lastip' => $w->getRaw('lastip'),
                    'lastlogin' => $w->lastlogin,
                    'banned' => $w->isBanned()
                );
            }
            
            if ($panthera->cache)
            {
                $panthera->cache->set($sid, array($usersTotal, $users), 'usersTable');
                $panthera->logging->output('Saving users search results to cache ' .$sid, 'pantheraUser');
            }
        }

        // groups listing
        if (@$_GET['subaction'] != 'show_table')
        {
            $panthera -> locale -> loadDomain('acl');
            $groups = pantheraGroup::listGroups();
            $groupsTpl = array();

            foreach ($groups as $group)
            {
                $groupsTpl[] = array('name' => $group->name, 'description' => $group->description, 'id' => $group->group_id);
            }

            $panthera -> template -> push('groups', $groupsTpl);
        }

        // find all recent 1-10 users
        // var_dump(getUsers('', 10, 0));

        // find all recent 1-10 users with default language set to "polski"
        // var_dump(getUsers(array('language' => 'polski'), 10, 0));

        /*for ($i=0; $i<100; $i++)
        {
            $users[] = array('login' => 'test', 'full_name' => 'Testowy, nie istniejący user', 'primary_group' => 'non_existing', 'joined' => 'today', 'language' => 'Marsjański', 'id' => 1);
        }*/

        $template -> push('avatar_dimensions', explode('x', $panthera -> config -> getKey('avatar_dimensions', '80x80', 'string')));
        $panthera -> template -> push('locales_added', $panthera->locale->getLocales());
        $panthera -> template -> push('users_list', $users);
        $panthera -> template -> push('view_users', True);
        $panthera -> template -> push('usersCacheHash', $_GET['query'].$_GET['order'].$_GET['direction'].$usersPage);
        
        $titlebar = new uiTitlebar(localize('All registered users on this website', 'users'));
        $titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/users.png', 'left');
}
