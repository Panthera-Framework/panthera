<?php
/**
  * Read server log
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'accessparser.tpl';

if (!getUserRightAttribute($user, 'can_read_log')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
}

$panthera -> locale -> loadDomain('accessparser');

$panthera -> importModule('accessparser');

if ($_GET['action'] == 'savePath')
{
    if (!strlen($_POST['path']))
        ajax_exit(array('status' => 'failed', 'message' => localize('Path cannot be empty!', 'accessparser')));
    
    $panthera -> config -> setKey('path_to_server_log', $_POST['path'], 'string');
    ajax_exit(array('status' => 'success'));
}

$parser = new accessParser;

try {
    $lines = $parser->readLog();
} catch (Exception $e) {
    $panthera -> template -> push("error", true);
    $panthera -> template -> push("error_message", localize($e->getMessage(), 'accessparser'));            
}

$page = $_GET['page'];

$uiPager = new uiPager('accessParserLines', count($lines), 'accessParserLines', 100);
$uiPager -> setActive($page);
$uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString('GET', 'page={$page}', '_'). '\');');
$limit = $uiPager -> getPageLimit();

$results = array();

if (is_array($lines))
{
    $results = array_slice($lines, $limit[0], $limit[1]);
}

$panthera -> template -> push('lines', $results);
$panthera -> template -> push('path', $panthera->config->getKey('path_to_server_log'));
$panthera -> template -> display($tpl);
pa_exit();
