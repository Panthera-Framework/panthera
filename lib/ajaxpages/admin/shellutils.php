<?php
/**
  * Executing shell commands from site
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_execute_shell_commands')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('debug');

$shellCommands = array(
    'ps u' => 'ps u',
    'debug.py' => PANTHERA_DIR. '/tools/debug.py',
    'w' => 'w',
    'whoami' => 'whoami',
    'ls -la ~' => 'ls -la ~',
    'uptime' => 'uptime',
    'users' => 'users',
    'ping google.com' => '/bin/ping google.com -c 2',
    'ls -la .' => 'ls -la .',
    'pwd' => 'pwd',
    'uname -a' => 'uname -a'
);

switch ($_GET['exec'])
{
    case 'debug.py':
        try {
            $output = file_get_contents(SITE_DIR. '/content/tmp/debug.log');

            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

    case 'ping google.com':
        try {
            $output = shell_exec('/bin/ping google.com -c 2');

            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

    default:
        if (isset($shellCommands[$_GET['exec']]))
        {
            try {
                $output = shell_exec($shellCommands[$_GET['exec']]);
                ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
            } catch (Exception $e) {
                ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
            }
        }
    break;

    /*default:
        ajax_exit(array('status' => 'failed', 'message' => localize('Unknown command')));
    break;*/
}

// titlebar
$titlebar = new uiTitlebar(localize('Shell utils', 'debug'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Apps-yakuake-icon.png', 'left');

// template
$panthera -> template -> push('commands', $shellCommands);
$panthera -> template -> display('shellutils.tpl');
pa_exit();
?>
