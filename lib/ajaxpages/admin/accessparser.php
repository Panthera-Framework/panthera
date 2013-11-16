<?php
/**
  * Read log
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

$panthera -> template -> push('lines', array_slice($lines, 0 , 50));
$panthera -> template -> display($tpl);
pa_exit();
