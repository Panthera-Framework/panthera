<?php
/**
 * pa-login front controller settings
 *
 * @package Panthera\core\frontcontrollers\palogin
 * @author Damian Kęska
 * @author Mateusz Warzyński
 * @license LGPLv3
 */


/**
 * pa-login front controller settings
 *
 * @package Panthera\core\frontcontrollers\palogin
 * @author Damian Kęska
 * @author Mateusz Warzyński
 */

class settings_pa_loginAjaxControllerSystem extends pageController
{
    protected $permissions = array(
        'admin.controllers.palogin.settings' => array('Users authorization settings', 'register'),
        'admin.conftool' => array('Advanced system configuration editor', 'conftool'),
    );

    protected $uiTitlebar = array(
        'Users authorization settings', 'register'
    );


    /**
     * Display page based on generic template
     *
     * @author Mateusz Warzyński
     * @return string
     */

    public function display()
    {
        // some defaults
        $this -> panthera -> config -> getKey('login.failures.max', 5, 'int', 'pa-login');
        $this -> panthera -> config -> getKey('redirect_after_login', 'index.php', 'string', 'pa-login');
        $this -> panthera -> config -> getKey('login.failures.bantime', 300, 'int', 'pa-login');
        $this -> panthera -> config -> getKey('login.history', 5, 'int', 'pa-login');
        $this -> panthera -> config -> getKey('login.maillogin', 1, 'bool', 'pa-login');
        $this -> panthera -> config -> getKey('login.jabberlogin', 0, 'bool', 'pa-login');

        // load uiSettings with "pa-login" config section
        $config = new uiSettings('palogin');
        $config -> add('login.failures.max', localize('Maximum number of failures', 'register'));

        $config -> add('redirect_after_login', localize('Login redirection', 'register'));
        $config -> setDescription('redirect_after_login', localize('Where to redirect user right after login (internal url)', 'palogin'));

        $config -> add('login.failures.bantime', localize('Block user when reaches maximum number of login failures', 'register'));
        $config -> setDescription('login.failures.bantime', localize('In seconds', 'palogin'));

        $config -> add('login.history', localize('Login history count', 'palogin'));
        $config -> setDescription('login.history', localize('Count of successful logins to keep', 'palogin'));

        // Mail login
        $config -> add('login.maillogin', localize('Allow using mail address to log in', 'palogin'));
        $config -> setFieldType('login.maillogin', 'bool');

        // Jabber login
        $config -> add('login.jabberlogin', localize('Allow type Jabber address to log in', 'palogin'));
        $config -> setFieldType('login.jabberlogin', 'bool');

        $result = $config -> handleInput($_POST);

        if (is_array($result))
            ajax_exit(array('status' => 'failed', 'message' => $result['message'][1], 'field' => $result['field']));

        elseif ($result === True)
            ajax_exit(array('status' => 'success'));


        return $this -> panthera -> template -> compile('settings.genericTemplate.tpl');
    }
}