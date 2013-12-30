<?php
/**
  * User registration form and processing page
  *
  * @package Panthera\core\pages
  * @author Damian Kęska
  * @license GNU Affero General Public License 3, see license.txt
  */

if (!defined('IN_PANTHERA'))
    exit;

if (checkUserPermissions($panthera->user, False))
{
    pa_redirect('.');
}

// import user registration module - it's class-based module so developers can extend it easily
$panthera -> importModule('form');
$panthera -> importModule('userregistration');

// test with registration always open
$registration = new userRegistration($_POST, True);
//$registration = new userRegistration;

if ($registration -> isPostingAForm())
{
    if ($registration -> validateForm() === True)
    {
        $registration -> execute();
        $panthera -> template -> display ('registrationForm.complete.tpl');
        pa_exit();
    }
}

$registration -> displayForm();