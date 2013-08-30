<?php
/**
  * Get all input variables listed
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$tpl = 'dumpinput.tpl';

if (!getUserRightAttribute($user, 'can_dump_input')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('debug');

if (!$panthera -> session -> cookies -> exists('Created'))
    $panthera -> session -> cookies -> set('Created', date('G:i:s d.m.Y'), time()+60);

$panthera -> session -> set('Name', 'Damien');

$template -> push('cookie', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_COOKIE, True))));
$template -> push('pantheraCookie', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($panthera->session->cookies->getAll(), True))));
$template -> push('pantheraSession', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($panthera->session->getAll(), True))));
$template -> push('SESSION', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_SESSION, True))));
$template -> push('GET', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_GET, True))));
$template -> push('POST', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_POST, True))));
$template -> push('SERVER', str_replace("    ", "&nbsp;&nbsp;", nl2br(print_r($_SERVER, True))));
?>
