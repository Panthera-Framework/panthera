<?php
/**
 * Users
 *
 * @package Panthera\core\system\users
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */

/**
 * Users management pageController
 *
 * @package Panthera\core\system\users
 * @author Mateusz Warzyński
 * @author Damian Kęska
 */

class usersAjaxControllerCore extends pageController
{
    protected $uiTitlebar = array(
        'All registered users on this website', 'users'
    );

    protected $requirements = array(
        'admin/ui.pager',
        'admin/ui.searchbar'
    );

    protected $permissions = '';
    protected $tempPermissions = array();

    protected $actionPermissions = array(
        'ban' => array('admin.users.ban' => array('Ban users', 'users')),
        'createGroup' => 'admin',
        'removeGroup' => 'admin',
        'removeUser' => 'admin',
        'editUser' => _CONTROLLER_PERMISSION_INLINE_,
        'account' => 'admin',
        'addUser' => 'admin',
        'getUsersAPI' => 'admin',
        'switchUser' => 'admin',
    );

    protected $actionuiTitlebar = array(
        'account' => array('Panel with informations about user.', 'users'),
    );

    /**
     * User account details
     *
     * @hook user.fields
     * @author Damian Kęska
     * @author Mateusz Warzyński
     */

    public function accountAction()
    {
        if (isset($_GET['uid']) and $this->checkPermissions('admin', true))
        {
            $u = new pantheraUser('id', $_GET['uid']);
            $this -> panthera -> template -> push('user_uid', '&uid=' .$_GET['uid']);
        } else {
            $u = $this->panthera->user;
        }

        if (!$u -> exists()) {
            $noAccess = new uiNoAccess;
            $noAccess -> display();
        }

        if ($u->id == $this->panthera->user->id) {
            $permissions['canBlockUser'] = False;
            $this -> panthera -> template -> push('user_fields', $this->panthera->get_filters('user.fields', array())); // custom fields
        }

        if ($u != $this->panthera->user)
            $this -> panthera -> template -> push('dontRequireOld', True);

        // if we arent superuser we cannot view superuser profiles
        if (($u->acl->get('superuser') and !$this->panthera->user->acl->get('superuser')) or !$u->exists())
        {
            $noAccess = new uiNoAccess;
            $noAccess -> addMetas(array('superuser'));
            $noAccess -> display();
        }

        // user cannot ban superuser or other admin
        if (($u->acl->get('superuser') or $u->acl->get('admin')) and !$this->panthera->user->acl->get('superuser'))
        {
            $this -> tempPermissions['canBlockUser'] = False;
            $this -> tempPermissions['canSeePermissions'] = False;

        } else {
            $this -> panthera -> template -> push(array(
                'userSwitchable' => True,
            ));

            $this -> tempPermissions['canSeePermissions'] = True;
        }

        // TODO: users ajaxpage: move acl to action function
        if (isset($_POST['aclname']))
        {
            if (strlen($_POST['aclname']) < 3)
            {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Too short ACL attribute name', 'users'),
                ));
            }

            if ($this -> checkPermissions('admin', true))
            {
                if ($_POST['value'] == "1")
                    $aclValue = True;
                else
                    $aclValue = False;

                if ($_POST['aclname'] == 'superuser' and !$this -> panthera -> user -> acl -> get('superuser'))
                    ajax_exit(array(
                        'status' => 'failed',
                        'message' => localize('You are not allowed to manage permissions', 'messages'),
                    ));

                $u -> acl -> set($_POST['aclname'], $aclValue);

                ajax_exit(array(
                    'status' => 'success',
                    'name' => $_POST['aclname'],
                    'value' => $aclValue,
                    'post_value' => $_POST['value'],
                    'result' => $u -> acl -> get($_POST['aclname']),
                ));
            } else {
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('You are not allowed to manage permissions', 'messages'),
                ));
            }
        }

        $locales = $this->panthera->locale->getLocales();
        $localesActive = $this->panthera->locale->getLocales();

        // hide disabled locales
        foreach ($locales as $Key => $Value) {

            if($Value == True)
                $localesActive[$Key] = $Value;
        }

        $groups = pantheraGroup::listGroups();
        $groupsTpl = array();

        foreach ($groups as $group)
            $groupsTpl[] = array(
                'name' => $group->name,
                'group_id' => $group->group_id,
            );

        $this -> panthera -> template -> push(array(
            'locales_added' => $localesActive,
            'avatar_dimensions' => explode('x', $this->panthera->config->getKey('avatar_dimensions', '80x80', 'string')),
            'profile_picture' => pantheraUrl($u->profile_picture),
            'groups' => $groupsTpl,
            'facebookID' => $u->acl->get('facebook'),
            'lastloginHistory' => $u -> getLastLoginHistory(),
            'user' => $u,
        ));

        $aclList = array();
        $userTable = $u->acl->listAll();

        $permissionsTable = $this->panthera->listPermissions();

        foreach ($userTable as $key => $value)
        {
            $name = $key;

            // translating name to description
            if (isset($permissionsTable[$key]))
                $name = $this -> panthera -> locale -> localizeFromArray($permissionsTable[$name]);

            if ($key == 'admin' or $key == 'superadmin')
                continue;

            if ($value)
                $aclList[$key] = array(
                    'name' => $name,
                    'value' => localize('Yes'),
                    'active' => true,
                );
            else
                $aclList[$key] = array(
                    'name' => $name,
                    'value' => localize('No'),
                    'active' => false,
                );
        }

        if ($this -> checkPermissions(array('admin' => 'admin', 'admin.acl.viewall' => array('See all permissions on a list', 'acl')), true))
        {
            foreach ($permissionsTable as $key => $value)
            {
                if (isset($aclList[$key]))
                    continue;

                $acl = getUserRightAttribute($u, $key);

                if ($acl == True) {
                    $active = 1;
                    $val = localize('Yes');
                } else {
                    $val = localize('No');
                }

                $aclList[$key] = array(
                    'name' => $this -> panthera -> locale -> localizeFromArray($value),
                    'value' => $val,
                    'active' => $active,
                );
            }

            $this -> panthera -> template -> push('aclList', $aclList);
        }

        $this -> panthera -> template -> push('permissions', $this->tempPermissions);
        $this -> panthera -> template -> display('users_account.tpl');
        pa_exit();
    }

    /**
     * Logout and switch user
     *
     * @return null
     */

    public function switchUserAction()
    {
        $u = new pantheraUser('id', intval($_POST['uid']));

        if ($u -> exists() and !$u -> isAdmin())
        {
            $_GET['uid'] = $_POST['uid'];
            userTools::userCreateSession($u -> login, null, true);
        }

        pa_redirect('_ajax.php?display=users&cat=admin&action=account');
    }

    /**
     * Ban user
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */

    public function banAction()
    {
        if (strlen($_GET['uid']) > 0)
            $u = new pantheraUser('id', $_GET['uid']);

        if (!$u -> exists())
            ajax_exit(array('Cannot get user by given id.', 'users'));

        // user cannot ban itself
        if ($u -> id == $this -> panthera -> user -> id) {
            $noAccess = new uiNoAccess;
            $noAccess -> display();
        }

        $banned = $u->isBanned();

        if ($u->isBanned(!$banned) == !$banned)
        {
            $u -> save();
            ajax_exit(array('status' => 'success', 'value' => !$banned));
        } else {
            ajax_exit(array('status' => 'failed'));
        }
    }



    /**
     * Create a new group
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     */

    public function createGroupAction()
    {
        $groupName = $_POST['name'];
        $groupDescription = $_POST['description'];

        try {
            if (!pantheraGroup::create($groupName, $groupDescription))
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Group propably already exists', 'acl'),
                ));

            ajax_exit(array(
                'status' => 'success',
                'name' => $groupName,
                'description' => $groupDescription,
            ));
        } catch (Exception $e) {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Invalid group name, only alphanumeric characters and "_" is allowed', 'acl'),
            ));
        }
    }



    /**
     * Remove a user group
     *
     * @author Damian Kęska
     * @author Mateusz Warzyński
     * @return null
     */

    public function removeGroupAction()
    {
        $groupName = $_POST['group'];

        try {
            if(!pantheraGroup::remove($groupName))
                ajax_exit(array(
                    'status' => 'failed',
                ));

            ajax_exit(array(
                'status' => 'success',
                'name' => $groupName,
            ));
        } catch (Exception $e) {
            ajax_exit(array(
                'status' => 'failed',
                localize('Cannot remove group', 'acl'),
            ));
        }
    }



    /**
     * Save information about user to database
     *
     * @author Mateusz Warzyński
     * @return null
     */

    public function editUserAction()
    {
        if (strlen($_POST['uid']) > 0)
        {
            if (intval($_POST['uid']) == $this->panthera->user->id)
                $u = $this->panthera->user;
            elseif ($this->checkPermissions('admin'))
                $u = new pantheraUser('id', $_POST['uid']);
        }

        if (!$u -> exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot find user by selected id', 'users')));

        if ($_POST['passwd'])
        {
            if (strlen($_POST['passwd']) > 6)
            {
                if ($_POST['passwd'] == $_POST['retyped_passwd'])
                {
                    if ($u -> changePassword($_POST['passwd']))
                        $u -> save();
                    else
                        ajax_exit(array('status' => 'failed', 'message' => localize('Error with changing password')));

                } else {
                    ajax_exit(array('status' => 'failed', 'message' => localize('Passwords are not identical')));
                }

            } else {
                ajax_exit(array('status' => 'failed', 'message' => localize('Password is too short!', 'users')));
            }
        }

        if (strlen($_POST['full_name']) > 4)
            $u->full_name = filterInput($_POST['full_name'], 'strip');
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Full name is too short', 'users')));

        if (strlen($_POST['avatar']) > 6)
            $u->profile_picture = filterInput($_POST['avatar'], 'strip');

        // TODO: e-mail confirmation
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            $u->mail = $_POST['email'];

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            $u->jabber = $_POST['jabber'];
        
        
        if (in_array($_POST['gender'], pantheraUser::$genders))
            $u->gender = $_POST['gender'];

        if (strlen($_POST['address']) > 3)
            $u->address = $_POST['address'];
        
        if (strlen($_POST['city']) > 3)
            $u->city = $_POST['city'];

        if (strlen($_POST['postal_code']) > 3)
            $u->postal_code = $_POST['postal_code'];

        $u -> active = (isset($_POST['active']) and $_POST['active']);
        $languages = $this->panthera->locale->getLocales();

        if (isset($languages[$_POST['language']]))
            $u->language = $_POST['language'];

        if ($this -> checkPermissions('admin', true))
        {
            $g = new pantheraGroup('id', $_POST['primary_group']);

            if ($g -> exists())
                $u -> primary_group = intval($_POST['primary_group']);

            if (strlen($_POST['facebookID']) > 5)
                $u -> acl -> set('facebook', intval($_POST['facebookID']));
            else
                $u -> acl -> set('facebook', null);
        }

        $u -> save();

        ajax_exit(array(
            'status' => 'success',
        ));
    }



    /**
     * Remove an user (by id)
     *
     * @author Mateusz Warzyński
     * @author Damian Kęska
     * @return null
     */

    public function removeUserAction()
    {
        $id = $_POST['id'];

        $usersPage = (intval(@$_GET['usersPage']));

        if ($usersPage < 0)
            $usersPage = 0;

        if ($_GET['hash'] != 0)
            $this -> panthera -> cache -> set($_GET['hash'], NULL);

        try {
            // if this is current user
            if ($id == $this->panthera->user->login)
                ajax_exit(array('status' => 'failed', 'message' => localize('You can not remove yourself', 'users')));

            $u = new pantheraUser('login', $id);

            if ($u -> acl -> get('superuser') and !$this->panthera->user->acl->get('superuser'))
                ajax_exit(array(
                    'status' => 'success',
                    'message' => localize('Cannot remove superuser', 'users'),
                ));

            if (userTools::removeUser($id, $u->id))
                ajax_exit(array(
                    'status' => 'success',
                ));

        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', localize('Cannot remove user', 'users')));
        }
    }



    /**
     * Add new user
     *
     * @input POST
     * @author Mateusz Warzyński
     * @return null
     */
    public function addUserAction()
    {
        $error = null;

        if (strlen($_POST['login']) > 2)
            $login = $_POST['login'];
        else
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Login is too short!', 'users'),
            ));

        if (strlen($_POST['passwd']) > 6)
        {
            $password = $_POST['passwd'];
            $passwordEncoded = encodePassword($password);

            if (!verifyPassword($_POST['retyped_passwd'], $passwordEncoded))
                ajax_exit(array(
                    'status' => 'failed',
                    'message' =>  localize('Passwords does not match', 'users'),
                ));

        } else {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Password is too short!', 'users'),
            ));
        }

        if (strlen($_POST['full_name']) > 4)
            $full_name = $_POST['full_name'];
        else
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Full name is too short', 'users'),
            ));

        if (strlen($_POST['avatar']) > 6)
            $avatar = $_POST['avatar'];
        else
            $avatar = '{$PANTHERA_URL}/images/default_avatar.png';


        $gender = $_POST['gender'];
        $address = filterInput($_POST['address'], 'strip');
        $city = filterInput($_POST['city'], 'strip');
        $postal_code = filterInput($_POST['postal_code'], 'strip');
        $mail = trim($_POST['email']);
        $jabber = trim($_POST['jabber']);
        $language = $_POST['language'];

        // group
        $group = new pantheraGroup('name', $_POST['primary_group']);
        $primary_group = $group->groupid;

        if (!in_array($_POST['gender'], pantheraUser::$genders))
            $error = localize('Please select a valid gender', 'users');

        if (!$group -> exists())
            $error = localize('Please specify a valid user group', 'users');

        /**
         * Check if there are already users with that e-mail or jabber address
         */
        $where = new whereClause;
        $where -> add('AND', 'mail', '=', $mail);
        $where -> add('AND', 'jabber', '=', $jabber);

        $testUser = pantheraUser::fetchOne($where, 0, 1);

        if ($testUser)
        {
            if ($testUser -> mail == $mail)
                $error = localize('This e-mail address is already in use', 'users');

            if ($testUser -> jabber == $jabber)
                $error = localize('This jabber address is already in use', 'users');
        }

        $attributes = array();

        if ($error)
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => $error,
            ));
        }

        $insertConditions = array(
            'login' => $login,
            'passwd' => $password,
            'full_name' => $full_name,
            'primary_group' => $primary_group,
            'attributes' => $attributes,
            'language' => $language,
            'mail' => $mail,
            'jabber' => $jabber,
            'profile_picture' => $avatar,
            '@requiresActivation' => true,
            'gender' => $gender,
            'address' => $address,
            'city' => $city,
            'postal_code' => $postal_code,
        );

        if (user::create($insertConditions))
        {
            ajax_exit(array(
                'status' => 'success',
                'message' => localize('User has been successfully added!', 'users'),
            ));
        } else {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Cannot add new user: Unknown error', 'users'),
            ));
        }
    }



    /**
     * Main function, shows list of users
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        $this -> checkPermissions('admin');

        $this -> panthera -> locale -> loadDomain('users');
        $this -> panthera -> template -> push('action', '');
        $this -> panthera -> template -> push('user_uid', '');
        $this -> panthera -> template -> push('locales', $this -> panthera -> locale -> getLocales());
        $this -> panthera -> template -> push('locale', $this -> panthera -> locale -> getActive());

        $this->tempPermissions = array(
            'canBlockUser' => $this->checkPermissions('admin.users.ban', True),
            'canSeePermissions' => $this->checkPermissions('admin.users.permissions', True),
            'canEditOthers' => $this->checkPermissions('admin.users.seeothers', True)
        );

        $this -> panthera -> template -> push('permissions', $this->tempPermissions);

        $this -> dispatchAction();


        $this -> checkPermissions('can_see_users_table');

        $this -> panthera -> locale -> loadDomain('search');

        $sBar = new uiSearchbar('uiTop');
        $sBar -> setQuery($_GET['query']);
        $sBar -> setAddress('?display=users&cat=admin');
        $sBar -> navigate(True);
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

        $maxOnPage = $this->panthera->config->getKey('paging_users_max', 25, 'int');

        if (intval($maxOnPage) < 2)
        {
            $maxOnPage = 25;
            $this->panthera->config->setKey('paging_users_max', 25);
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
            $order = $_GET['order'];

        if ($_GET['direction'] == 'DESC' or $_GET['direction'] == 'ASC')
            $direction = $_GET['direction'];

        $usersTotal = pantheraUser::fetchAll($w, False, False, $order);

        // uiPager
        $uiPager = new uiPager('users', $usersTotal, 'adminUsersList');
        $uiPager -> setActive($usersPage);
        $uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .Tools::getQueryString($_GET, 'page={$page}', '_'). '\');');
        $limit = $uiPager -> getPageLimit();


        // get groups to display groupName in users list (only id of group is set in pa_users table)
        $groups = pantheraGroup::listGroups();
        $groupsTpl = array();

        foreach ($groups as $group)
        {

            $groupsTpl[$group->group_id] = array(
                'name' => $group->name,
                'description' => $group->description,
                'id' => $group->group_id
            );
        }


        // this we will pass to template
        if (!isset($users))
        {
            $users = array();
            $usersData = pantheraUser::fetchAll($w, $limit[1], $limit[0], $order, $direction);

            foreach ($usersData as $w)
            {
                // superuser cant be listed, it must be hidden
                if ($w->attributes->superuser and !$this->panthera->user->attributes->superuser)
                    continue;

                $users[] = array(
                    'login' => $w->login,
                    'name' => $w->getName(),
                    'primary_group' => $groupsTpl[strval($w->primary_group)]['name'],
                    'joined' => $w->joined,
                    'language' => $w->language,
                    'id' => $w->id,
                    'avatar' => pantheraUrl($w->profile_picture),
                    'lastip' => $w->getRaw('lastip'),
                    'lastlogin' => $w->lastlogin,
                    'banned' => $w->isBanned()
                );
            }
        }

        // groups listing
        if (@$_GET['subaction'] != 'showTable')
        {
            $this -> panthera -> locale -> loadDomain('acl');
            $this -> panthera -> template -> push('groups', $groupsTpl);
        }

        // find all recent 1-10 users
        // var_dump(pantheraUser::fetchAll('', 10, 0));

        // find all recent 1-10 users with default language set to "polski"
        // var_dump(pantheraUser::fetchAll(array('language' => 'polski'), 10, 0));

        /*for ($i=0; $i<100; $i++)
        {
            $users[] = array('login' => 'test', 'full_name' => 'Testowy, nie istniejący user', 'primary_group' => 'non_existing', 'joined' => 'today', 'language' => 'Marsjański', 'id' => 1);
        }*/

        $this -> panthera -> template -> push('avatar_dimensions', explode('x', $this -> panthera -> config -> getKey('avatar_dimensions', '80x80', 'string')));
        $this -> panthera -> template -> push('locales_added', $this->panthera->locale->getLocales());
        $this -> panthera -> template -> push('users_list', $users);
        $this -> panthera -> template -> push('view_users', True);
        $this -> panthera -> template -> push('usersCacheHash', $sid);

        return $this -> panthera -> template -> compile('users.tpl');
    }

    /**
     * Get users JSON api
     *
     * @return null
     */

    public function getUsersAPIAction()
    {
        if (strlen($_REQUEST['query']) < 2)
        {
            ajax_exit(array(
                'status' => 'success',
                'data' => array(),
            ));
        }

        $w = new whereClause();

        if (isset($_GET['group']))
        {
            $w -> add( 'AND', 'name', 'LIKE', '%' .$_REQUEST['query']. '%');
            $w -> add( 'OR', 'description', 'LIKE', '%' .$_REQUEST['query']. '%');

            $fetch = pantheraGroup::fetchAll($w, 0, 25, 'name', 'ASC');
        } else {
            $w -> add( 'AND', 'login', 'LIKE', '%' .$_REQUEST['query']. '%');
            $w -> add( 'OR', 'full_name', 'LIKE', '%' .$_REQUEST['query']. '%');

            $fetch = pantheraUser::fetchAll($w, 0, 25, 'login', 'ASC');
        }
        $results = array();

        if (isset($_GET['fulldata']))
            $fullArray = array();

        foreach ($fetch as $user)
        {
            if (isset($_GET['group']))
            {
                $array[$user->id] = array(
                    'label' => $user -> name,
                    'value' => $user -> name,
                );
            } else {
                $array[$user->login] = array(
                    'label' => $user -> getName(),
                    'value' => $user -> login,
                );
            }

            if (isset($_GET['fulldata']))
            {
                $fullArray[$user->id] = $user -> getData();
                unset($fullArray[$user->id]['passwd']);
            }
        }

        $status = array(
            'status' => 'success',
            'result' => $array,
        );

        if (isset($fullArray))
            $status['data'] = $fullArray;

        ajax_exit($status);
    }
}