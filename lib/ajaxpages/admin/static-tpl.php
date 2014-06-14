<?php
/**
 * Displays static templates
 *
 * @package Panthera\core\adminUI\static-tpl
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

// TODO: Rewrite to objective model, create list of templates, add to menu etc.

if (!defined('IN_PANTHERA'))
    exit;

if (!checkUserPermissions($user, True))
{
    $noAccess = new uiNoAccess; $noAccess -> display();
    $panthera->finish();
    pa_exit();
}

$template -> display($_GET['name']. '.tpl');
pa_exit();