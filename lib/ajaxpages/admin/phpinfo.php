<?php
/**
  * Show information about PHP
  *
  * @package Panthera
  * @subpackage core
  * @copyright (C) Damian Kęska, Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($panthera->user, 'can_see_phpinfo')) {
    $noAccess = new uiNoAccess; $noAccess -> display();
    pa_exit();
}

if ($_GET['action'] == 'iframe')
{
    phpinfo();
    pa_exit();
}

$panthera -> template -> display('phpinfo.tpl');
pa_exit();
