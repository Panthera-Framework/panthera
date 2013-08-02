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
    $template->display('no_access.tpl');
    pa_exit();
}

$panthera -> locale -> loadDomain('ajaxpages');

$lib = scandir(PANTHERA_DIR. '/ajaxpages');
$content = scandir(SITE_DIR. '/content/ajaxpages');
$pages = array();

foreach ($lib as $file)
{
    $pathinfo = pathinfo($file);

    if (strtolower($pathinfo['extension']) != 'php')
        continue;

    if (!is_file(PANTHERA_DIR. '/ajaxpages/' .$file))
        continue;

    $name = str_ireplace('.php', '', $file);

    $pages[] = array('location' => 'lib', 'name' => $name, 'link' => '?display=' .$name);
}

$pages[] = array('location' => 'lib', 'name' => 'system_info', 'link' => '?display=settings&cat=admin&action=system_info');
$pages[] = array('location' => 'lib', 'name' => 'users', 'link' => '?display=settings&cat=admin&action=users');
$pages[] = array('location' => 'lib', 'name' => 'my_account', 'link' => '?display=settings&cat=admin&action=my_account');

foreach ($content as $file)
{
    $pathinfo = pathinfo($file);

    if (strtolower($pathinfo['extension']) != 'php')
        continue;

    if (!is_file(SITE_DIR. '/content/ajaxpages/' .$file))
        continue;

    $name = str_ireplace('.php', '', $file);

    $pages[] = array('location' => 'content', 'name' => $name, 'link' => '?display=' .$name);
}

$pages = $panthera->get_filters('ajaxpages_list', $pages);

$template -> push('pages', $pages);

?>
