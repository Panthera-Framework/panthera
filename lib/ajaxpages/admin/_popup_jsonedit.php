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

    // allow serialized arrays as input    
    if (@unserialize($_POST['jsonedit_content']))
        $_POST['jsonedit_content'] = json_encode(unserialize($_POST['jsonedit_content']));
    
    $response = serialize(json_decode($_POST['jsonedit_content'], true));
    
    // return in "print_r" format
    if ($_POST['responseType'] == 'print_r')
    {
        $response = print_r(unserialize($response), True);

    // var_dump result
    } elseif ($_POST['responseType'] == 'var_dump') {
        ob_start();
        var_dump(unserialize($response));
        $response = str_replace('=&gt; ', '=> ', strip_tags(ob_get_clean()));
        
    } elseif ($_POST['responseType'] == 'json') {
    // and json pretty printed
        if (version_compare(phpversion(), '5.4.0', '>'))
            $response = json_encode(unserialize($response), JSON_PRETTY_PRINT);
        else
            $response = json_encode(unserialize($response));
            
    } elseif ($_POST['responseType'] == 'var_export') {
    // var_export support
        $response = var_export(unserialize($response), True);
    }
    
    ajax_exit(array('status' => 'success', 'result' => stripslashes($response)));
}

$array = unserialize(stripslashes(base64_decode($_GET['input'])));

if (version_compare(phpversion(), '5.4.0', '>'))
    $code = json_encode($array, JSON_PRETTY_PRINT);
else
    $code = json_encode($array);
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
