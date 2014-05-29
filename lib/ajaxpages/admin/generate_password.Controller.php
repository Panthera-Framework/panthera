<?php
/**
 * Generate hash from string (password)
 *
 * @package Panthera\core\adminUI\generate_password
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @license LGPLv3
 */
  
/**
 * Show information about PHP
 *
 * @author Mateusz Warzyński
 * @author Damian Kęska
 * @package Panthera\core\adminUI\generate_password
 */

class generate_passwordAjaxControllerSystem extends pageController
{
    protected $permissions = 'can_generate_hash';
    protected $uiTitlebar = array(
        'Generate password', 'debug'
    );
    
    protected $defaultLength = 12;
    protected $maxLength = 256;
    protected $defaultChars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_.,?!';
    
    /**
     * void main()
     * 
     * @return null
     */

    public function display()
    {
        $this -> dispatchAction();
        $this -> uiTitlebarObject -> addIcon('{$PANTHERA_URL}/images/admin/menu/developement.png', 'left');
        $this -> panthera -> locale -> loadDomain('debug');
        $this -> panthera -> template -> display('generate_password.tpl');
        pa_exit();
    }
    
    /**
     * Generate hash of password
     *
     * @author Mateusz Warzyński
     */
    
    public function generatePasswordAction()
    {
        $password = $_POST['password'];
        $length = intval($_POST['length']);
        $chars = $_POST['range'];
        
        // set default length
        if ($length < 1 or $length > $this -> maxLength)
        {
            $length = $this -> defaultLength;
        }
        
        if (!$chars)
        {
            $chars = $this -> defaultChars;
        }
        
        // generate random string if password not provided
        if (!$password or $this -> panthera -> session -> get('generate.password.last') == $password)
        {
            $password = generateRandomString($length, $chars);
            $this -> panthera -> session -> set('generate.password.last', $password);
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
        
        // on failure
        ajax_exit(array(
            'status' => 'failed', 
            'message' => localize('Cannot generate hash, unknown error', 'generate_password')
        ));
    }
}