<?php
/**
 * User registration form and processing page
 * For modifications this file should be copied to /content/pages directory and edited
 * Please don't modify framework files in /lib directory unless you  
 *
 * @package Panthera\core\pages
 * @author Damian Kęska
 * @license GNU Affero General Public License 3, see license.txt
 */

if (!defined('IN_PANTHERA'))
    exit;

// allow only site administrator and guests to use registration
if (checkUserPermissions($panthera->user, False) and !($panthera -> logging -> debug and checkUserPermissions($panthera->user, True)))
{
    pa_redirect($panthera -> config -> getKey('register.redirectregistered', '.', 'string', 'register')); // where to redirect already registered users
}

// import user registration module - it's class-based module so developers can extend it easily
$panthera -> importModule('form');
$panthera -> importModule('userregistration');
#$panthera -> importModule('myOwnRegistrationModule'); // EXAMPLE

// test with registration always open
#$registration = new myOwnRegistrationThatExtendsDefault($_POST, True); // EXAMPLE
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
        
        $panthera -> template -> push('registrationFields', $registration -> getInput());
        $panthera -> template -> display ('registrationForm.complete.tpl');
        pa_exit();
    }
}

$registration -> displayForm();