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

$tpl = 'ajaxpages.tpl';

if (!getUserRightAttribute($user, 'can_see_ajax_pages')) {
    $noAccess = new uiNoAccess; 
    $noAccess -> display();
    pa_exit();
}

$panthera -> locale -> loadDomain('ajaxpages');
$panthera -> importModule('filesystem');

// titlebar
$titlebar = new uiTitlebar(localize('Index of ajax pages', 'ajaxpages'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/Actions-tab-detach-icon.png', 'left');

// scan both lib and content
$files = array_merge(
    scandirDeeply(PANTHERA_DIR. '/ajaxpages/admin'), 
    scandirDeeply(SITE_DIR. '/content/ajaxpages/admin'),
    scandirDeeply(PANTHERA_DIR. '/pages'),
    scandirDeeply(SITE_DIR. '/content/pages')
);

$pages = array();

$pages[] = array(
    'location' => 'lib',
    'directory' => 'admin',
    'path' => PANTHERA_DIR. '/ajaxpages/admin/settings.php',
    'modtime' => date('G:i:s d.m.Y', filemtime(PANTHERA_DIR. '/ajaxpages/admin/settings.php')),
    'name' => 'system_info',
    'link' => '?display=settings&cat=admin&action=system_info'
);

$pages[] = array(
    'location' => 'lib',
    'directory' => 'admin',
    'path' => PANTHERA_DIR. '/ajaxpages/admin/users.php',
    'modtime' => date('G:i:s d.m.Y', filemtime(PANTHERA_DIR. '/ajaxpages/admin/users.php')),
    'name' => 'my_account',
    'link' => '?display=users&cat=admin&action=my_account'
);

foreach ($files as $file)
{
    $pathinfo = pathinfo($file);

    if (strtolower($pathinfo['extension']) != 'php')
        continue;

    if (!is_file($file))
        continue;

    $location = 'unknown';
    
    if (strpos($file, SITE_DIR) !== False)
    {
        $location = 'content';
        $name = str_replace(SITE_DIR, '', str_ireplace('/content/ajaxpages/', '', $file));
    } elseif (strpos($file, PANTHERA_DIR) !== False) {
        $location = 'lib';
        $name = str_replace(PANTHERA_DIR, '', str_ireplace('/content/ajaxpages/', '', $file));
    }
    
    $directory = str_replace('/ajaxpages/', '', dirname($name));
    $name = str_ireplace('.php', '', basename($name));

    $pages[] = array('location' => $location, 'directory' => $directory, 'modtime' => date('G:i:s d.m.Y', filemtime($file)), 'name' => $name, 'path' => $file, 'link' => '?display=' .$name);
}

$pages = $panthera->get_filters('ajaxpages_list', $pages);

$template -> push('pages', $pages);

?>
