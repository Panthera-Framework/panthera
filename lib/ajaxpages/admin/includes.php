<?php
/**
  * List of all included files in current code execution
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'includes.tpl';

if (!getUserRightAttribute($user, 'can_see_debug')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('includes');

$files = get_included_files();
$template -> push('files', $files);

?>
