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
{
    $panthera -> session -> set('jsonedit_content', $_POST['jsonedit_content']);
    
    $response = serialize(json_decode($_POST['jsonedit_content'], true));
    
    if ($_POST['responseType'] == 'print_r')
        $response = print_r(unserialize($response), True);
    elseif ($_POST['responseType'] == 'var_dump') {
        ob_start();
        var_dump(unserialize($response));
        $response = str_replace('=&gt; ', '=> ', strip_tags(ob_get_clean()));
    }     
    
    ajax_exit(array('status' => 'success', 'result' => $response));
}
   
$array = unserialize(base64_decode($_GET['input']));
$code = json_encode($array, JSON_PRETTY_PRINT);

// remember last code after page refresh
if ($array == False and !isset($_GET['popup']))
{
    $code = '';

    if ($panthera -> session -> exists('jsonedit_content'))
        $code = $panthera -> session -> get('jsonedit_content');
}

$template -> push ('popup', $_GET['popup']);
$template -> push ('code', $code);

$template -> push ('callback', $_GET['callback']);
$template -> push ('callback_arg', $_GET['callback_arg']);
$template -> display('_popup_jsonedit.tpl');
pa_exit();
