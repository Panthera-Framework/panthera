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

$tpl = 'shellutils.tpl';

if (!getUserRightAttribute($user, 'can_execute_shell_commands')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('debug');

$shellCommands = array('ps u' => 'ps u', 'debug.py' => PANTHERA_DIR. '/tools/debug.py', 'w' => 'w', 'whoami' => 'whoami', 'ls -la ~' => 'ls -la ~', 'uptime' => 'uptime', 'users' => 'users', 'ping google.com' => '/bin/ping google.com -c 2');

switch ($_GET['exec'])
{
    case 'ps u':
        try {
            $output = shell_exec('ps u');
            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

    case 'users':
        try {
            $output = shell_exec('users');
            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

    case 'w':
        try {
            $output = shell_exec('w');
            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

    case 'ls -la ~':
        try {
            $output = shell_exec('ls -la $HOME');
            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

    case 'uptime':
        try {
            $output = shell_exec('uptime');
            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;


    case 'whoami':
        try {
            $output = shell_exec('whoami');
            ajax_exit(array('status' => 'success', 'message' => nl2br($output)));
        } catch (Exception $e) {
            ajax_exit(array('status' => 'failed', 'message' => localize('Cannot execute shell command')));
        }
    break;

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


    /*default:
        ajax_exit(array('status' => 'failed', 'message' => localize('Unknown command')));
    break;*/
}

$panthera -> template -> push('commands', $shellCommands);

$titlebar = new uiTitlebar(localize('Shell utils', 'debug'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Apps-yakuake-icon.png', 'left');
?>
