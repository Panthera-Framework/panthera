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


// ==== AJAX-JSON FUNCTIONS
if ($_GET['action'] == 'delete')
{
    $group = False;

    if ($_POST['type'] == 'group')
        $group = True;

    if (meta::removeAcl(trim($_POST['id']), trim($_POST['login']), $group))
    {
        ajax_exit(array('status' => 'success'));
    } else
        ajax_exit(array('status' => 'failed', 'message' => localize('Unknown error')));
}

if ($_GET['action'] == 'add')
{
    if ($_POST['type'] == 'group')
    {
        $u = new pantheraGroup('name', $_POST['login']);
    } else
        $u = new pantheraUser('login', $_POST['login']);

    if (!$u->exists())
        ajax_exit(array('status' => 'failed', 'message' => localize('Entered group name/user login is incorrect')));

    if (empty($_POST['acl']))
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission name is empty')));



    $u -> meta -> set($_POST['acl'], True);
    $u -> meta -> save();

    if ($_POST['type'] == 'user')
        ajax_exit(array('status' => 'success', 'uid' => $u->id, 'name' => $u->login, 'full_name' => $u->full_name, 'group' => $u->primary_group));
    else
        ajax_exit(array('status' => 'success', 'aclName' => $_POST['acl'], 'name' => $u->name, 'description' => $u->description));
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
