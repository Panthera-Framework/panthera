<?php
if (!defined('IN_PANTHERA'))
      exit;

if (!getUserRightAttribute($panthera->user)) {
    $panthera -> template -> display('no_access.tpl');
    pa_exit();
}

// load language domain
$panthera -> locale -> loadDomain('popups');
$panthera -> locale -> setDomain('popups');

$panthera -> template -> push('callback', $_GET['callback']);
$panthera -> template -> push('type', $_GET['type']);
$panthera -> template -> display('_popup_time.tpl');
pa_exit();
