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
$parser = new accessParser;

$lines = $parser->readLog();

$page = $_GET['page'];

$uiPager = new uiPager('accessParserLines', count($lines), 'accessParserLines', 100);
$uiPager -> setActive($page);
$uiPager -> setLinkTemplates('#', 'navigateTo(\'?' .getQueryString($_GET, 'page={$page}', '_'). '\');');
$limit = $uiPager -> getPageLimit();

$panthera -> template -> push('lines', array_slice($lines, $limit[0], $limit[1]));
$panthera -> template -> display($tpl);
pa_exit();
