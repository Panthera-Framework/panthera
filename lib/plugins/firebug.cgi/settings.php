<?php
/**
  * FirePHP extension for Panthera
  * Integrates with pantheraLogging
  *
  * @package Panthera\plugins\firebug
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

global $panthera;

if (!defined('IN_PANTHERA'))
    exit;

// check permissions
if (!getUserRightAttribute($panthera->user, 'can_manage_firebug')) {
    $panthera->template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('firebug');

// "firebugSettings" page
if ($_GET['display'] == 'firebugSettings')
{
    $whitelist = str_replace(' ', '', $panthera -> config -> getKey('firebug_whitelist'));
    $list = explode(',', $whitelist);

    $newList = array();

    foreach ($list as $val)
    {
        if ($val == '')
            continue;

        $newList[] = $val;
    }

   $list = $newList;

    switch ($_GET['action'])
    {
        case 'add':
            // check if ip address is valid
            if (!$panthera->types->validate($_POST['addr'], 'ip'))
                ajax_exit(array('status' => 'failed', 'message' => localize('Incorrect IP address')));


            if (!in_array($_POST['addr'], $list))
            {
                $list[] = $_POST['addr'];
                $panthera -> config -> setKey('firebug_whitelist', implode(',', $list));
                ajax_exit(array('status' => 'success'));
            } else
                ajax_exit(array('status' => 'failed', 'message' => localize('Address already exists')));
        break;

        case 'remove':
            if (!in_array($_POST['addr'], $list))
                ajax_exit(array('status' => 'failed', 'message' => 'No such address in table'));

            $newList = array();

            foreach ($list as $val)
            {
                if ($val == $_POST['addr'] or $val == '')
                    continue;

                $newList[] = $val;
            }

            $list = $newList;
            $panthera -> config -> setKey('firebug_whitelist', implode(',', $list));

            ajax_exit(array('status' => 'success'));
        break;


        default:
            if (isset($_SERVER['HTTP_X_FIREPHP_VERSION']))
                $panthera -> template -> push ('client_version', $_SERVER['HTTP_X_FIREPHP_VERSION']);
            else
                $panthera -> template -> push ('client_version', localize('Not detected'));

            if ($list[0] == '')
                unset($list[0]);

            $panthera -> template -> push ('server_version', FirePHP::VERSION);
            $panthera -> template -> push ('current_address', $_SERVER['REMOTE_ADDR']);
            $panthera -> template -> push('whitelist', $list);
            $panthera -> template -> display('firebug.tpl');
        break;
    }

    pa_exit();
}
