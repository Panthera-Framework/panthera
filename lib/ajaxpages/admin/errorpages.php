<?php
/**
  * System error pages
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'errorpages.tpl';

if (!getUserRightAttribute($user, 'can_test_error_pages')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('errorpages');

switch ($_GET['show'])
{
    case 'exception_debug':
        $panthera -> logging -> debug = True;
        throw new Exception('This is a test of an exception page');
    break;

    case 'error_debug':
        $panthera -> logging -> debug = True;
        trigger_error("This is a test of error page", E_USER_ERROR);
    break;

    case 'exception':
        $panthera -> logging -> debug = False;
        throw new Exception('This is a test of an exception page');
    break;

    case 'error':
        $panthera -> logging -> debug = False;
        trigger_error("This is a test of error page", E_USER_ERROR);
    break;

    case 'db_error':
        $e = new Exception("SQLSTATE[42000] [1044] Access denied for user '****'@'****' to database '****'");
        $panthera -> db -> _triggerErrorPage($e);
    break;
}

$pages = array();
$pages['error_debug'] = array('name' => 'Error', 'file' => getErrorPageFile('error_debug'), 'testname' => 'error_debug', 'visibility' => localize("Debugging"));
$pages['exception_debug'] = array('name' => 'Exception', 'file' => getErrorPageFile('exception_debug'), 'testname' => 'exception_debug', 'visibility' => localize("Debugging"));

$errorFile = getErrorPageFile('error');
if (!$errorFile)
    $errorFile = '/content/templates/error.php';

$exceptionsFile = getErrorPageFile('error');
if (!$exceptionsFile)
    $exceptionsFile = '/content/templates/exception.php';

$pages['db_error'] = array('name' => 'Database error', 'file' => getErrorPageFile('db_error'), 'testname' => 'db_error', 'visibility' => localize("Public"));
$pages['error'] = array('name' => 'Error', 'file' => $errorFile, 'testname' => 'exception', 'notice' => !(bool)getErrorPageFile('error'), 'visibility' => localize("Public"));
$pages['exception'] = array('name' => 'Exception', 'file' => $exceptionsFile, 'testname' => 'exception', 'notice' => !(bool)getErrorPageFile('exception'), 'visibility' => localize("Public"));
$panthera -> template -> push ('errorPages', $pages);
?>
