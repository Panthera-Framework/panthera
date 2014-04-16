<?php
/**
 * Password recovery module for pa-login controller
 * 
 * @package Panthera\core\user\login
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license GNU Lesser General Public License 3, see license.txt
 */

/**
 * Password recovery module for pa-login controller
 * 
 * @package Panthera\core\user\login
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class passwordrecoveryModule extends pageController
{
    /**
     * Initialize module
     * 
     * @return null
     */
    
    public function initialize($controllerObject)
    {
        $this -> panthera -> add_option('login.checkauth', array($this, 'passwordRecovery'));
    }
    
    /**
     * Password recovery functions
     * 
     * @return null
     */
    
    public function passwordRecovery(&$continueChecking, $u)
    {
        if ($_POST['recovery'] == "1" or isset($_GET['key']))
        {
            if (isset($_GET['key']))
            {
                // change user password
                if ($result = recoveryChangePassword($_GET['key']))
                    $continueChecking= localize('Password changed, you can use new one', 'messages');
                else
                    $continueChecking = localize('Invalid recovery key, please check if you copied link correctly', 'messages');
                
                $this -> getFeature('login.passwordrecovery.afterChange', array(
                    'result' => $result,
                    'key' => $_GET['key'],
                ));
    
            } else {
                // send an e-mail with new password
                if ($result = recoveryCreate($_POST['log']))
                    $continueChecking = localize('New password was sent in a e-mail message to you', 'messages');
                else
                    $continueChecking = localize('Invalid user name specified', 'messages');
                
                $this -> getFeature('login.passwordrecovery.afterCreate', array(
                    'result' => $result,
                    'login' => $_GET['login'],
                ));
            }
        }

        // check if account is activated
        $SQL = $this -> panthera -> db -> query('SELECT * FROM `{$db_prefix}password_recovery` WHERE `user_login` = :login AND `type` = "confirmation"', array(
            'login' => $u->login,
        ));

        if ($SQL -> rowCount() > 0)
            $continueChecking = localize('Please activate you\'r account first', 'messages');
    }
}


