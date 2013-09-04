<?php
/**
  * Autoloader list with option to clear cache
  *
  * @package Panthera\core\autoloader
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if(!checkUserPermissions($user, True))
{
    $n = new uiNoAccess();
    $n -> display();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $items = pantheraAutoloader::updateCache();
    ajax_exit(array('status' => 'success', 'message' => localize('Updated autoloader cache, counting ' .count($items). ' items')));
}

$titlebar = new uiTitlebar(localize('Autoloader cache'));
$panthera -> template -> push('autoloader', $panthera -> config -> getKey('autoloader'));
$panthera -> template -> display('autoloader.tpl');
pa_exit();
