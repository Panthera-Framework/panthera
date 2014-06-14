<?php
/**
 * Admin account creator for pantheraInstaller
 *
 * @package Panthera\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */

/**
 * Admin account creator for pantheraInstaller
 *
 * @package Panthera\installer
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class accountInstallerControllerSystem extends installerController
{
    /**
     * Validate input POST data
     *
     * @author Damian Kęska
     * @return bool|array Returns TRUE on successful validate, or array with status, field and message (ajax response)
     */

    public function validateInput()
    {
        if (!preg_match('/^-?[0-9a-zA-Z_]+$/', (string)$_POST['login']))
        {
            return array(
                'status' => 'failed',
                'field' => 'login',
                'message' => localize('Login must be alphanumeric', 'installer'),
            );
        }

        if ($_POST['password'] != $_POST['confirm'])
        {
            return array(
                'status' => 'failed',
                'field' => 'confirm',
                'message' => localize('Passwords doesn\'t match', 'installer'),
            );
         }

         if (strlen($_POST['password']) > 18 or strlen($_POST['password']) < 6)
         {
            return array(
                'status' => 'failed',
                'field' => 'password',
                'message' => localize('Password should be 6-18 characters length', 'installer'),
            );
         }

         if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
         {
            return array(
                'status' => 'failed',
                'field' => 'email',
                'message' => localize('Please input a valid e-mail address', 'installer'),
            );
         }

         return True;
    }

    /**
     * Main function to display everything
     *
     * @feature installer.account.post &array Modify $_POST data
     * @feature installer.account.validateinput &array|bool Post fileds validation result
     * @feature installer.account.user &array Modify user account after creation
     *
     * @author Damian Keska
     * @author Mateusz Warzyński
     * @return null
     */

    public function display()
    {
        $this -> getFeatureRef('installer.account.post', $_POST);

        if (isset($_POST['login']))
        {
            $validate = $this -> validateInput();
            $this -> getFeatureRef('installer.account.validateinput', $validate);

            if (is_array($validate))
                ajax_exit($validate);

            $u = new pantheraUser('login', $_POST['login']);

            // if user already exists lets change its password and e-mail address
            if ($u -> exists())
            {
                $u -> changePassword($_POST['password']);
                $u -> mail = $_POST['email'];
            } else {
                createNewUser($_POST['login'], $_POST['password'], $_POST['login'], 'root', serialize(array('admin' => True)), $this -> locale -> getActive(), $_POST['email'], '');
                $u = new pantheraUser('login', $_POST['login']);
                userTools::userCreateSessionById($u->id); // login user, so we can skip the login step after installation
            }

            // automaticaly add Jabber address if e-mail address is in known jabber+e-mail service
            if (strpos('gmail.com', $_POST['email']) !== False or strpos('jabber.org', $_POST['email']) !== False or strpos('ubuntu.pl', $_POST['email']) !== False)
                $u -> jabber = $_POST['email'];

            $this -> getFeatureRef('installer.account.user', $u);
            $u -> save();

            // unlock next step
            $this -> installer -> enableNextStep();

            ajax_exit(array(
                'status' => 'success',
            ));
        }

        $this -> installer -> template = 'account';
    }
}