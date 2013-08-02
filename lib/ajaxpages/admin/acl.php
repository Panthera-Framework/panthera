<?php
/**
  * Access control list
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (!checkUserPermissions($user, True))
{
    $template->display('no_access.tpl');
    $panthera->finish();
    pa_exit();
}

// we will need meta functions
$panthera -> importModule('meta');
$panthera -> locale -> setDomain('messages');
$panthera -> locale -> loadDomain('acl');


// ==== AJAX-JSON FUNCTIONS

/**
  * Deleting a meta key
  *
  * @author Damian Kęska
  */

if ($_GET['action'] == 'delete')
{
    if ($_POST['type'] == 'group')
    {
        $u = new pantheraGroup('name', $_POST['login']);
    } else
        $u = new pantheraUser('login', $_POST['login']);
        
    if (!$u->exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Entered group name/user login is incorrect', 'acl')));
        
    $u -> acl -> remove($_POST['id']);
    $u -> acl -> save();
    
    ajax_exit(array('status' => 'success'));
    
/**
  * Adding new meta key
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'add') {
    if ($_POST['type'] == 'group')
    {
        $u = new pantheraGroup('name', $_POST['login']);
    } else
        $u = new pantheraUser('login', $_POST['login']);

    if (!$u->exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Entered group name/user login is incorrect', 'acl')));

    if (empty($_POST['acl']))
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission name is empty', 'acl')));

    $u -> acl -> set($_POST['acl'], True);
    $u -> acl -> save();

    if ($_POST['type'] == 'user')
        ajax_exit(array('status' => 'success', 'uid' => $u->id, 'name' => $u->login, 'full_name' => $u->full_name, 'group' => $u->primary_group));
    else
        ajax_exit(array('status' => 'success', 'aclName' => $_POST['acl'], 'name' => $u->name, 'description' => $u->description));
        
/**
  * List group informations
  *
  * @author Damian Kęska
  */
  
} elseif ($_GET['action'] == 'listGroup') {
    $groupName = $_GET['group'];
    $group = new pantheraGroup('name', $groupName);
    
    if (!$group->exists())
    {
        $panthera -> template -> display('no_page.tpl');
        pa_exit();
    }
    
    $metas = $group -> acl -> listAll();
    $metasTpl = array();
    
    $permissionsTable = $panthera->listPermissions();

    foreach ($metas as $meta => $value)
    {
        $metasTpl[$meta] = array('name' => $meta, 'value' => $value);
    
        if (array_key_exists($meta, $permissionsTable))
            $metasTpl[$meta]['name'] = $permissionsTable[$meta]['desc'];
    }
    
    // show some informations
    $panthera -> template -> push('metasAvaliable', $permissionsTable);
    $panthera -> template -> push('metas', $metasTpl);
    $panthera -> template -> push('groupName', $groupName);
    $panthera -> template -> push('groupDescription', $group->description);
    $panthera -> template -> push('groupUsers', $group->findUsers());
    
    // display template
    $panthera -> template -> display('acl_listgroup.tpl');
    pa_exit();
   
/**
  * Manage group meta tags
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'groupMetaSave') {
    $groupName = $_POST['group'];
    $attribute = $_POST['key'];
    $group = new pantheraGroup('name', $groupName);
    
    if (!$group->exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Specified group does not exists', 'acl')));
        
    if ($attribute == '')
        ajax_exit(array('status' => 'failed', 'message' => localize('Please enter an attribute name', 'acl')));
    
    if(preg_match('/[^a-z_\-0-9]/i', $attribute) or strlen($attribute) < 3)
    {
        ajax_exit(array('status' => 'failed', 'message' => localize('Meta key contains not allowed characters', 'acl')));
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
            
            $permissionsTable = $panthera->listPermissions();

            foreach ($metas as $meta => $value)
            {
                $metasTpl[$meta] = array('name' => $meta, 'value' => $value);
            
                if (array_key_exists($meta, $permissionsTable))
                    $metasTpl[$meta]['name'] = $permissionsTable[$meta]['desc'];
            }
            
            ajax_exit(array('status' => 'success', 'metaList' => $metasTpl));
        } else {
            ajax_exit(array('status' => 'failed'));
        }

    /**
      * Removing attribute
      *
      * @author Damian Kęska
      */

    } elseif ($_POST['do'] == 'remove') {
        if ($group->acl->remove($attribute))
            ajax_exit(array('status' => 'success'));
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot remove attribute', 'acl')));
    }
    
/**
  * Manage group members
  *
  * @author Damian Kęska
  */
    
} elseif ($_GET['action'] == 'groupUsers') {
    $userName = $_POST['user'];
    $groupName = $_POST['group'];
    $group = new pantheraGroup('name', $groupName);
    
    if (!$group->exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Specified group does not exists', 'acl')));
        
    /**
      * Setting user's primary group
      *
      * @author Damian Kęska
      */
    
    if ($_POST['subaction'] == 'add' or $_POST['subaction'] == 'remove')
    {
        $usr = new pantheraUser('login', $userName);
        
        if (!$usr->exists())
            ajax_exit(array('status' => 'failed', 'message' => localize('User with specified login does not exists', 'acl')));
            
        // reset user's primary group
        if ($_POST['subaction'] == 'remove')
        {
            if ($groupName != 'users')
                $groupName = 'users';
            else
                $groupName = '';
        }

        // TODO: In future we may support multiple groups for one user        
        $usr -> primary_group = $groupName;
        $usr -> save();
        
        ajax_exit(array('status' => 'success', 'userList' => $group -> findUsers()));
    }

    ajax_exit(array('status' => 'failed'));
}


// ==== LIST OF USERS AND GROUPS
if (isset($_GET['name']))
{
    $aclId = $aclName = $_GET['name'];
    $permissionsTable = $panthera->listPermissions();

    if (isset($permissionsTable[$aclId]))
        $aclName = $permissionsTable[$aclId]['desc'];

    // groups with required permissions we are looking for
    $groupsWhoCan = meta::getUsers($_GET['name'], True);
    $groupList = array();

    foreach ($groupsWhoCan as $key => $gid)
    {
        $group = new pantheraGroup('name', $key);

        if ($group -> exists())
            $groupList[] = array('name' => $group->name, 'description' => $group->description, 'id' => $group->group_id);
    }

    // here we will generate list of users who have required rights we are looking for
    $usersWhoCan = meta::getUsers($_GET['name']);

    foreach ($usersWhoCan as $userID => $value)
    {
        $u = new pantheraUser('id', $userID);

        if ($u -> exists())
            $userList[] = array('login' => $u->login, 'full_name' => $u->full_name, 'id' => $userID, 'group' => $u->primary_group, 'userid' => $u->id);
    }


    $template -> push('acl_name', $aclId);
    $template -> push('group_list', $groupList);
    $template -> push('user_list', $userList);
    $template -> push('action_title', 'Manage global permissions for single variable');
    $template -> push('action', 'manage_variable');
    $template -> push('acl_title', $aclName);
}

$template -> display('acl.tpl');
pa_exit();
?>
