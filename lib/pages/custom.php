<?php
/**
  * Custom pages example action handler
  *
  * @package Panthera\core\pages
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

$panthera -> importModule('custompages');
$mode = 'fallback';

if (isset($_GET['forceNativeLanguage']))
    $mode = 'forceNative';

if (isset($_GET['url_id']))
{
    $cpage = customPage::getBy('url_id', $_GET['url_id'], '', $mode);
} elseif(isset($_GET['id'])) {
    $cpage = new customPage('id', $_GET['id']);
} elseif(isset($_GET['unique'])) {
    $cpage = customPage::getBy('unique', $_GET['unique'], '', $mode);
}

if ($cpage == null)
    pa_redirect('?404');
    
if (!$cpage->exists())
    pa_redirect('?404');

$tags = unserialize($cpage->meta_tags);
$panthera -> template -> putKeywords($tags);
$panthera -> template -> setTitle($cpage->title);
$panthera -> template -> push('custom_name', $cpage->title);
$panthera -> template -> push('custom_content', $cpage->html);
$panthera -> template -> display('custom.tpl');
pa_exit();
