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
$cpage = null;

// to be set manually
if (!isset($templateFile))
    $templateFile = 'custom.tpl';

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

if (!$cpage or !$cpage->exists())
{
    pa_redirect($panthera -> config -> getKey('err404.url', '?404', 'string', 'errors'));
}
   
$tags = unserialize($cpage->meta_tags);
$panthera -> template -> putKeywords($tags);
$panthera -> template -> setTitle($cpage->title);
$panthera -> template -> push('custom_name', $cpage->title);
$panthera -> template -> push('custom_content', $cpage->html);
$panthera -> template -> display($templateFile);
pa_exit();
