<?php
/**
  * Show list of ajax pages
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($user, 'can_see_front_controllers')) {
    $noAccess = new uiNoAccess; 
    $noAccess -> display();
    pa_exit();
}

$panthera -> importModule('filesystem');

// titlebar
$titlebar = new uiTitlebar(localize('Index of front controllers', 'ajaxpages'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-tab-detach-icon.png', 'left');

// scan both lib and content
$files = scandir(SITE_DIR);

$controllers = array();

foreach ($files as $file)
{
    $pathinfo = pathinfo($file);

    if (strtolower($pathinfo['extension']) != 'php')
        continue;

    if (!is_file($file))
        continue;

    $name = basename($file);
    $linked = False;
    
    if (is_link($file))
    {
        if (stripos(readlink($file), '/frontpages') !== False)
        {
            $linked = True;
        }
    }
    

    $controllers[] = array(
        'name' => $name,
        'linked' => $linked,
        'modtime' => date('G:i:s d.m.Y', filemtime($file))
    );
}

$controllers = $panthera->get_filters('pa.frontcontrollers.list', $controllers);
$panthera -> template -> push('list', $controllers);
$panthera -> template -> display('frontcontrollers.tpl');
pa_exit();
