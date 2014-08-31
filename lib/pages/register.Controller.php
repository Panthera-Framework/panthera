<?php
/**
 * User registration form and processing page
 * For modifications this file should be copied to /content/pages directory and edited
 * Please don't modify framework files in /lib directory unless you
 *
 * @package Panthera\core\pages
 * @author Damian Kęska
 * @license LGPLv3
 */

if (!defined('IN_PANTHERA'))
    exit;

// allow only site administrator and guests to use registration
class registerControllerSystem extends pageController
{
    public $registerClassName = 'userRegistration';
    
    public function display()
    {
        if (checkUserPermissions($this -> panthera->user, False) && !($this -> panthera -> logging -> debug and checkUserPermissions($this -> panthera->user, True)))
            pa_redirect($this -> panthera -> config -> getKey('register.redirectregistered', '.', 'string', 'register')); // where to redirect already registered users
        
        // import user registration module - it's class-based module so developers can extend it easily
        $this -> panthera -> importModule('form');
        $this -> panthera -> importModule('userregistration');
        #$this -> panthera -> importModule('myOwnRegistrationModule'); // EXAMPLE
        
        // test with registration always open
        #$registration = new myOwnRegistrationThatExtendsDefault($_POST, True); // EXAMPLE
        $c = $this -> registerClassName;
        $registration = new $c($_POST, True);
        //$registration = new userRegistration;
        if ($registration -> isPostingAForm())
        {
            $v = $registration -> validateForm();
            $this -> panthera -> template -> push('registrationFields', $registration -> getInput());
            
            if ($v === True)
            {
                try {
                    $registration -> execute();
                    
                } catch (Exception $e) {
                    $this -> template -> push('formState', 'failed');
                    $this -> panthera -> template -> push('formError', localize($e -> getMessage(), 'register'));
                    return $registration -> displayForm();
                }
        
                $this -> template -> push('formState', 'completed');
                return $this -> panthera -> template -> compile('registrationForm.complete.tpl');
            }
            
            $this -> template -> push('formState', 'failed');
            $this -> template -> push('validation', $v);
        }
        
       return $registration -> displayForm();
    }
}