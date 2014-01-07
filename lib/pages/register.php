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
        try {
            $registration -> execute();
        } catch (Exception $e) {
            $panthera -> template -> push('formError', localize($e -> getMessage(), 'register'));
            $registration -> displayForm();
            pa_exit();
        }
        
        $panthera -> template -> display ('registrationForm.complete.tpl');
        pa_exit();
    }
}

$registration -> displayForm();