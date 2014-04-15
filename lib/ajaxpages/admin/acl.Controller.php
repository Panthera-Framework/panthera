<?php
/**
 * Access control list
 *
 * @package Panthera\core\acl
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */
 
/**
 * Access control list
 *
 * @package Panthera\core\acl
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */

class aclAjaxControllerSystem extends pageController
{
    protected $permissions = 'admin.acl';
    protected $defaultAction = 'getPermissions';
    protected $uiTitlebar = array(
        'Permissions management', 'acl'
    );
    
    protected $requirements = array(
        'meta',
    );
    
    /**
     * Delete permission from user or group
     * 
     * @return null
     */
    
    public function deleteAction()
    {
        if ($_POST['type'] == 'group')
        {
            $u = new pantheraGroup('name', $_POST['login']);
        } else
            $u = new pantheraUser('login', $_POST['login']);
            
        if (!$u->exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Entered group name/user login is incorrect', 'acl'),
            ));
            
        $u -> acl -> remove($_POST['id']);
        $u -> acl -> save();
        
        ajax_exit(array(
            'status' => 'success',
        ));
    }
    
    /**
     * Add permission to a group or user
     * 
     * @return null
     */
    
    public function addAction()
    {
        if ($_POST['type'] == 'group')
        {
            $u = new pantheraGroup('name', $_POST['login']);
        } else
            $u = new pantheraUser('login', $_POST['login']);
    
        if (!$u->exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Entered group name/user login is incorrect', 'acl'),
            ));
    
        if (empty($_POST['acl']))
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Permission name is empty', 'acl')
            ));
    
        $u -> acl -> set($_POST['acl'], True);
        $u -> acl -> save();
        
        if ($_POST['type'] == 'user')
            ajax_exit(array(
                'status' => 'success',
                'uid' => $u->id, // DEPRECATED
                'name' => $u->login, // DEPRECATED
                'full_name' => $u->full_name, // DEPRECATED
                'group' => $u->primary_group, // DEPRECATED
                'data' => $u->getData(),
            ));
        else
            ajax_exit(array(
                'status' => 'success',
                'aclName' => $_POST['acl'],
                'name' => $u->name,
                'description' => $u->description,
                'data' => $u->getData(),
            ));
    }
    
    /**
     * Group meta management
     * 
     * @return null
     */
    
    public function groupMetaSaveAction()
    {
        $groupName = $_POST['group'];
        $attribute = $_POST['key'];
        $group = new pantheraGroup('name', $groupName);
        
        if (!$group->exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Specified group does not exists', 'acl'),
            ));
            
        if ($attribute == '')
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Please enter an attribute name', 'acl'),
            ));
        
        if(preg_match('/[^a-z_\-0-9_\-\.]/i', $attribute) or strlen($attribute) < 3)
        {
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Meta key contains not allowed characters', 'acl'),
            ));
        }
    
        /**
          * Adding and saving attribute
          *
          * @author Damian Kęska
          */
        
        if ($_POST['do'] == 'save' or $_POST['do'] == 'create')
        {
            $value = (bool)intval($_POST['value']);
            
            if ($group->acl->get($attribute) !== null or $_POST['do'] == 'create')
            {
                $group -> acl -> set($attribute, $value);
                
                // generate new list to update it via ajax
                $metas = $group -> acl -> listAll();
                $metasTpl = array();
                
                $permissionsTable = $this -> panthera -> listPermissions();
    
                foreach ($metas as $meta => $value)
                {
                    $metasTpl[$meta] = array('name' => $meta, 'value' => $value);
                
                    if (array_key_exists($meta, $permissionsTable))
                        $metasTpl[$meta]['name'] = $permissionsTable[$meta]['desc'];
                }
                
                ajax_exit(array(
                    'status' => 'success',
                    'metaList' => $metasTpl,
                ));
            } else {
                ajax_exit(array(
                    'status' => 'failed',
                ));
            }
    
        /**
          * Removing attribute
          *
          * @author Damian Kęska
          */
    
        } elseif ($_POST['do'] == 'remove') {
            if ($group->acl->remove($attribute))
                ajax_exit(array(
                    'status' => 'success',
                ));
            else
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('Cannot remove attribute', 'acl'),
                ));
        }
    }

    public function listGroupAction()
    {
        $groupName = $_GET['group'];
        $group = new pantheraGroup('name', $groupName);
        
        if (!$group->exists())
            $this -> checkPermissions(true);
        
        $metas = $group -> acl -> listAll();
        $metasTpl = array();
        
        $permissionsTable = $this -> panthera -> listPermissions();
    
        foreach ($metas as $meta => $value)
        {
            $metasTpl[$meta] = array('name' => $meta, 'value' => $value);
        
            if (array_key_exists($meta, $permissionsTable))
                $metasTpl[$meta]['name'] = $permissionsTable[$meta]['desc'];
        }
        
        $count = $group->findUsers(False);
        
        $uiPager = new uiPager('adminACLGroups', $count);
        $uiPager -> setActive(intval($_GET['page']));
        $uiPager -> setLinkTemplatesFromConfig('acl.listGroup.tpl');
        $limit = $uiPager -> getPageLimit();
        
        // show some informations
        $this -> panthera -> template -> push('metasAvaliable', $permissionsTable);
        $this -> panthera -> template -> push('metas', $metasTpl);
        $this -> panthera -> template -> push('groupName', $groupName);
        $this -> panthera -> template -> push('groupDescription', $group->description);
        $this -> panthera -> template -> push('groupUsers', $group->findUsers($limit[0], $limit[1]));
        
        $this -> uiTitlebarObject -> setTitle(slocalize('Editing group "%s"', 'users', $groupName));
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/users.png', 'left');
        
        // display template
        $this -> panthera -> template -> display('acl.listGroup.tpl');
        pa_exit();
    }
    
    /**
     * Manage group members
     * 
     * @return null
     */
    
    public function groupUsersAction()
    {
        $userName = $_POST['user'];
        $groupName = $_POST['group'];
        $group = new pantheraGroup('name', $groupName);
        
        if (!$group->exists())
            ajax_exit(array(
                'status' => 'failed',
                'message' => localize('Specified group does not exists', 'acl'),
            ));
            
        /**
          * Setting user's primary group
          *
          * @author Damian Kęska
          */
        
        if ($_POST['subaction'] == 'add' or $_POST['subaction'] == 'remove')
        {
            $usr = new pantheraUser('login', $userName);
            
            if (!$usr->exists())
                ajax_exit(array(
                    'status' => 'failed',
                    'message' => localize('User with specified login does not exists', 'acl'),
                ));
                
            // reset user's primary group
            if ($_POST['subaction'] == 'remove')
            {
                if ($groupName != 'users')
                    $groupName = 'users';
                else
                    $groupName = '';
            }
    
            // TODO: In future we may support multiple groups for one user
            $usr -> primary_group = $group->group_id;
            $usr -> save();
            
            ajax_exit(array(
                'status' => 'success',
                'userList' => $group -> findUsers(),
            ));
        }
    
        ajax_exit(array(
            'status' => 'failed',
        ));
    }
    
    /**
     * Default action: get list of permissions
     * 
     * @feature admin.acl.getPermissions.list $_GET['name] On action call
     * @feature admin.acl.getPermissions.display $array On template display
     * @input string|mixed $_GET['name'] List of permissions comma separated, or array of permissions name => title
     * 
     * @return null
     */
    
    public function getPermissionsAction()
    {
        // check if data is serialized, if yes then unserialized it
        if (substr($_GET['name'], 0, 7) == 'base64:')
            $_GET['name'] = unserialize(base64_decode(substr($_GET['name'], 7, strlen($_GET['name']))));
        
        $aclId = $aclName = $this -> getFeature('admin.acl.getPermissions.list', $_GET['name']);
        $permissionsTable = $this -> panthera -> listPermissions();
        
        // @input array name => title
        if (is_array($aclName))
        {
            $permissions = $aclName;
            
            if (isset($_GET['current']))
            {
                if (isset($permissions[$_GET['current']]))
                {
                    $aclId = $_GET['current'];
                    $aclName = $permissions[$_GET['current']];
                }
            } else {
                $aclId = key($aclName);
                $aclName = $aclName[key($aclName)];
            }
            
            $this -> panthera -> template -> push('multiplePermissions', $permissions);
            
        // @input string Comma separated values
        } elseif (strpos($aclName, ',') !== False) {
            
            $multiplePermissions = explode(',', $aclName);
            $permissions = array();
            
            foreach ($multiplePermissions as $permission)
            {
                $permission = trim($permission);
            
                if (!$permission)
                    continue;
            
                $permissions[$permission] = $permission;
            
                if (isset($permissionsTable[$permission]))
                    $permissions[$permission] = $permissionsTable[$permission]['desc'];
            }
            
            $this -> panthera -> template -> push('multiplePermissions', $permissions);
            
            if (isset($_GET['current']))
            {
                $aclId = $aclName = $_GET['current'];
            } else {
                $aclId = $aclName = $multiplePermissions[0];
            }
        }
        
        if (isset($permissionsTable[$aclId]))
            $aclName = $permissionsTable[$aclId]['desc'];
    
        // groups with required permissions we are looking for
        $groupsWhoCan = meta::getUsers($aclId, True);
        $groupList = array();
        
        foreach ($groupsWhoCan as $key => $gid)
        {
            $group = new pantheraGroup('id', $key);
            
            if ($group -> exists())
                $groupList[] = array(
                    'name' => $group->name,
                    'description' => $group->description,
                    'id' => $group->group_id,
                );
        }
        
        // here we will generate list of users who have required rights we are looking for
        $usersWhoCan = meta::getUsers($aclId);
        
        foreach ($usersWhoCan as $userID => $value)
        {
            $u = new pantheraUser('id', $userID);
    
            if ($u -> exists())
                $userList[] = array(
                    'login' => $u->login,
                    'full_name' => $u->full_name,
                    'id' => $userID,
                    'group' => $u->primary_group,
                    'userid' => $u->id,
                );
        }
        
        $this -> panthera -> template -> push($this -> getFeature('admin.acl.getPermissions.display', array(
            'acl_name' => $aclId,
            'group_list' => $groupList,
            'user_list' => $userList,
            'action' => 'manage_variable',
            'acl_title' => $aclName,
        )));
    
        $this -> panthera -> template -> display('acl.tpl');
        
        pa_exit();
    }
}
