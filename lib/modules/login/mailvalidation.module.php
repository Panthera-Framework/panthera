<?php
/**
 * Mail validation module for pa-login controller
 * 
 * @package Panthera\core\user\login
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */

/**
 * Mail validation module for pa-login controller
 * 
 * @package Panthera\core\user\login
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */
 
class mailvalidationModule extends pageController
{
    /**
     * Initialize module
     * 
     * @return null
     */
    
    public function initialize($controllerObject)
    {
        $this -> panthera -> add_option('login.checkauth', array($this, 'checkEmail'));
    }
    
    /**
     * Activate user account
     * 
     * @feature login.registration.checkemail $array On email validation array($_GET['ckey'], $validation) where $validation is result of userRegistration::checkEmailValidation
     * @return null
     */
    
    public function checkEmail($continueChecking)
    {
        if (isset($_GET['ckey']))
        {
            $validation = userRegistration::checkEmailValidation($_GET['ckey'], True);
            list($_GET['ckey'], $validation) = $this -> getFeature('login.registration.checkemail', array($_GET['ckey'], $validation));
            
            if ($validation)
            {
                $this -> panthera -> template -> push('message', localize('Your account has been activated', 'messages'));
                $this -> panthera -> template -> setTemplate('admin');
                $this -> panthera -> template -> display('login.tpl');
                pa_exit();
            }
        }
    }
}
