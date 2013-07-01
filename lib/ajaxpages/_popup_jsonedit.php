<?php
/**
  * Simple JSON array editor
  *
  * @package Panthera\core\ajaxpages
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;
    
if (isset($_POST['jsonedit_content']))
    ajax_exit(array('status' => 'success', 'result' => serialize(json_decode($_POST['jsonedit_content'], true))));
    
$array = unserialize(base64_decode($_GET['input']));
$template -> push ('code', json_encode($array, JSON_PRETTY_PRINT));
$template -> push ('callback', $_GET['callback']);
$template -> push ('callback_arg', $_GET['callback_arg']);
$template -> display('_popup_jsonedit.tpl');
pa_exit();
