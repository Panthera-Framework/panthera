<?php
/**
  * Debug tools and debug.log
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @hook ajaxpages.debug.tools
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'debug.tpl';

if (!getUserRightAttribute($user, 'can_see_debug')) {
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('debug');

$panthera -> logging -> tofile = False;

    /** JSON PAGES **/

/**
 * Change debug value
 * Returns json
 */
if ($_GET['action'] == 'toggle_debug_value')
{
      if (!getUserRightAttribute($user, 'can_manage_debug')) {
          $template->display('no_access.tpl');
          pa_exit();
      }

      if ($panthera -> config -> setKey('debug', !(bool)$panthera -> config -> getKey('debug', False, 'bool'), 'bool'))
            ajax_exit(array('status' => 'success'));

      ajax_exit(array('status' => 'failed'));
}

    /** END OF JSON PAGES **/


    /** Ajax-HTML PAGES **/

// list of links (editable via @hook ajaxpages.debug.tools)
$tools = array();
$tools[] = array('link' => '?display=settings&action=system_info', 'name' => localize('Informations about system and session'));
$tools[] = array('link' => '?display=debhook', 'name' => localize('Plugins debugger'));
$tools[] = array('link' => '?display=includes', 'name' => localize('List of all included files in current code execution'));
$tools[] = array('link' => '?display=errorpages', 'name' => localize('Test system error pages in one place'));
$tools[] = array('link' => '?display=syschecksum', 'name' => localize('Checksum of system files'));
$tools[] = array('link' => '?display=shellutils', 'name' => localize('Shell utils'));
$tools[] = array('link' => '?display=phpinfo', 'name' => localize('phpinfo'));
$tools[] = array('link' => '?display=database', 'name' => localize('Database management'));
$tools[] = array('link' => '?display=dumpinput', 'name' => localize('DumpInput'));
$tools = $panthera -> get_filters('ajaxpages.debug.tools', $tools);

// Displaying main debug site
if (is_file(SITE_DIR. '/content/tmp/debug.log'))
{
      $log = explode("\n", file_get_contents(SITE_DIR. '/content/tmp/debug.log'));
      $template -> push('debug_log', $log);
}

$template -> push('current_log', explode("\n", $panthera -> logging -> getOutput()));
$template -> push('debug', $panthera -> config -> getKey('debug'));
$template -> push('tools', $tools);

    /** END OF Ajax-HTML PAGES **/

?>
