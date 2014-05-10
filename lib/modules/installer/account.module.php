<?php
/**
  * Cache configuration
  * 
  * @package Panthera\installer
  * @author Damian Kęska
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('PANTHERA_INSTALLER'))
    return False;
    
// we will use this ofcourse
global $panthera;
global $installer;

// create missing groups

if (isset($_POST['login']))
{
    if (!preg_match('/^-?[0-9a-zA-Z_]+$/', (string)$_POST['login']))
    {
        ajax_exit(array('status' => 'failed', 'field' => 'login', 'message' => localize('Login must be alphanumeric', 'installer')));
    }

    if ($_POST['password'] != $_POST['confirm'])
    {
        ajax_exit(array('status' => 'failed', 'field' => 'confirm', 'message' => localize('Passwords doesn\'t match', 'installer')));
    }
    
    if (strlen($_POST['password']) > 18 or strlen($_POST['password']) < 6)
    {
        ajax_exit(array('status' => 'failed', 'field' => 'password', 'message' => localize('Password should be 6-18 characters length', 'installer')));
    }
    
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        ajax_exit(array('status' => 'failed', 'field' => 'email', 'message' => localize('Please input a valid e-mail address', 'installer')));
    }
    
    $u = new pantheraUser('login', $_POST['login']);
    
    // if user already exists lets change its password and e-mail address
    if ($u -> exists())
    {
        $u -> changePassword($_POST['password']);
        $u -> mail = $_POST['email'];
    } else {
        createNewUser($_POST['login'], $_POST['password'], $_POST['login'], 'root', serialize(array('admin' => True)), $panthera -> locale -> getActive(), $_POST['email'], '');
        $u = new pantheraUser('login', $_POST['login']);
        userCreateSessionById($u->id); // login user, so we can skip the login step after installation
    }
    
    if (strpos('gmail.com', $_POST['email']) or strpos('jabber.org', $_POST['email']) or strpos('ubuntu.pl', $_POST['email']))
    {
        $u -> jabber = $_POST['email'];
    }

    $u -> save();
    $installer -> enableNextStep();
    ajax_exit(array('status' => 'success'));
}

$installer -> template = 'account';
