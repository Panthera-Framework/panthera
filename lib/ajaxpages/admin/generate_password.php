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
    {
        $noAccess = new uiNoAccess; 
        $noAccess -> display();
    }
    
    $password = $_POST['password'];
    $length = intval($_POST['length']);
    $chars = $_POST['range'];

    // set default length
    if ($length < 1 or $length > 256)
    {
        $length = 12;
    }
    
    if (!$chars)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_.,?!';
    }
    
    // generate random string if password not provided
    if (!$password or $panthera->session->get('generate.password.last') == $password)
    {
        $password = generateRandomString($length, $chars);
        $panthera->session->set('generate.password.last', $password);
    }
    
    $hash = encodePassword($password);

    if ($hash)
    {
        ajax_exit(array(
            'status' => 'success',
            'hash' => $hash,
            'password' => $password,
            'len' => strlen($password)
        ));
    }
    
    ajax_exit(array('status' => 'failed', 'message' => localize('Cannot generate hash, unknown error', 'generate_password')));
}

$titlebar = new uiTitlebar(localize('Generate password', 'debug'));
$titlebar -> addIcon('{$PANTHERA_URL}/images/admin/menu/developement.png', 'left');

$template -> display('generate_password.tpl');

pa_exit();
