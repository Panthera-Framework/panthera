<?php
/**
  * Generate hash from string (password)
  *
  * @package Panthera\core\ajaxpages
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
      exit;

$panthera -> locale -> loadDomain('debug');

$canGenerateHash = getUserRightAttribute($user, 'can_generate_hash');

/**
  * Generate hash of password
  *
  * @author Mateusz Warzyński
  */

if ($_GET['action'] == 'generatePassword') {

    // check permissions
    if (!$canGenerateHash)
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages')));
    
    // check lenght of password
    if (strlen($_POST['password']) < 2)
        ajax_exit(array('status' => 'failed', 'message' => localize('Your password is too short!', 'debug')));

    $hash = encodePassword($_POST['password']);

    if (strlen($hash) == 60)
        ajax_exit(array('status' => 'success', 'hash' => $hash));

    ajax_exit(array('status' => 'failed', 'message' => localize('Cannot generate hash!', 'debug')));


/**
  * Generate random string
  *
  * @author Mateusz Warzyński
  */

} elseif ($_GET['action'] == 'generateRandom') {
    
    // check permissions
    if (!$canGenerateHash)
        ajax_exit(array('status' => 'failed', 'message' => localize('Permission denied. You dont have access to this action', 'messages')));
    
    // generate random string
    if ($_POST['lenght'] != null) {
        if (intval($_POST['lenght']) > 1)  
            $string = generateRandomString(intval($_POST['lenght']));
        else
            ajax_exit(array('status' => 'failed', 'message' => localize('Lenght must be at least 8!', 'debug')));
    } else
        $string = generateRandomString(2);
    
    if (strlen($string) > 1)
        ajax_exit(array('status' => 'success', 'random' => $string));
    
    ajax_exit(array('status' => 'failed', 'message' => localize('Cannot generate random string!', 'debug')));
}


$titlebar = new uiTitlebar(localize('Generate password', 'debug'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/developement.png', 'left');

$template -> display('generate_password.tpl');

pa_exit();
