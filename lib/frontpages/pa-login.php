<?php
/**
 * Admin panel login front controller
 *
 * @package Panthera\core\user\login
 * @config pa-login redirect_after_login
 * @config pa-login login.failures.max
 * @config pa-login login.failures.bantime
 * @config ajax_url
 * @author Damian Kęska
 * @license LGPLv3
 */

define('SKIP_MAINTENANCE_CHECK', TRUE);

require_once 'content/app.php';
include_once getContentDir('pageController.class.php');

/**
 * Admin panel login front controller
 *
 * @package Panthera\core\user\login
 * @config pa-login redirect_after_login
 * @config pa-login login.failures.max
 * @config pa-login login.failures.bantime
 * @config ajax_url
 * @author Damian Kęska
 */

class pa_loginControllerSystem extends pageController
{
    protected $requirements = array(
        'userregistration', 'login/passwordrecovery',
    );
    
    /**
     * Constructor
     * 
     * @author Damian Kęska
     */
    
    public function __construct()
    {
        parent::__construct();
        $this -> panthera -> template -> setTemplate('admin');
    }
    
    /**
     * Display the template
     * 
     * @author Damian Kęska
     * @return null
     */
    
    public function displayTemplate()
    {
        return $this -> panthera -> template -> display('login.tpl');
    }

    /**
     * Support for login extensions
     *
     * @return null
     * @author Damian Kęska
     */

    public function loadExtensions()
    {
        $extensions = $this -> panthera -> config -> getKey('login.extensions', array(
            'facebook',
            'lastloginhistory',
            'passwordrecovery',
            'mailvalidation',
        ), 'array', 'pa-login');
        
        foreach ($extensions as $extension)
        {
            if ($this -> panthera -> moduleExists('login/' .$extension))
            {
                $object = $this -> panthera -> importModule('login/' .$extension, true);
                
                if (is_object($object) and method_exists($object, 'initialize'))
                    $object -> initialize($this);
            }
        }
    }

    /**
     * Logout action
     *
     * @return null
     */

    public function logoutAction()
    {
        if (isset($_GET['logout']))
            userTools::logoutUser();
    }

    /**
     * Main function
     *
     * @return null
     */

    public function display()
    {
        // logout user, TODO: CHANGE TO POST
        $this -> logoutAction();
        $this -> loadExtensions();

        // redirect user if it's already logged in
        if(checkUserPermissions($user))
        {
            if (!$this -> checkPermissions('admin.accesspanel', true))
            {
                pa_redirect($this -> panthera -> config -> getKey('redirect_after_login', 'index.php', 'string', 'pa-login'));
                pa_exit(); // just in case
            }

            pa_redirect('pa-admin.php');
        }

        /**
         * Get list of all locales to display flags on page
         *
         * @author Damian Kęska
         */

        $locales = $this -> panthera -> locale -> getLocales();
        $localesTpl = array();

        foreach ($locales as $lang => $enabled)
        {
            if ($enabled == True)
            {
                if (is_file(SITE_DIR. '/images/admin/flags/' .$lang. '.png'))
                    $localesTpl[] = $lang;
            }
        }

        $this -> panthera -> template -> push('flags', $localesTpl);

        // check authorization
        if (isset($_POST['log']) or isset($_GET['key']) or isset($_GET['ckey']))
            $this -> checkAuthData();

        // save the referer when logging in
        if (strpos($_SERVER['HTTP_REFERER'], $this -> panthera -> config -> getKey('ajax_url')) !== False and strpos($_SERVER['HTTP_REFERER'], '&cat=admin') !== False)
            $this -> panthera -> session -> set('login_referer', $_SERVER['HTTP_REFERER']);

        $this -> panthera -> template -> setTitle(localize('Log in'));
        $this -> displayTemplate();
        pa_exit();
    }

    /**
     * Check authorization data
     *
     * @feature login.success $pantheraUser On successful login
     * @return null
     */

    public function checkAuthData()
    {
        $continueChecking = True;

        $u = userTools::userCreateSession($_POST['log'], null, true, true);
        $this -> getFeatureRef('login.checkauth', $continueChecking, $u);

        // if module decided to break
        if (!$continueChecking or is_string($continueChecking))
        {
            if (is_string($continueChecking))
                $this -> panthera -> template -> push('message', $continueChecking);

            $this -> displayTemplate();
            pa_exit();
        }

        if ($u and $u -> exists())
        {
            if ($u -> attributes -> get('loginFailures') >= intval($this -> panthera -> config -> getKey('login.failures.max', 5, 'int', 'pa-login')) and $u -> attributes -> get('loginFailures') !== 0)
            {
                if (intval($u -> attributes -> get('loginFailureExpiration')) <= time())
                {
                    $u -> attributes -> set('loginFailures', 0);
                    $u -> attributes -> remove('loginFailureExpiration');
                    $u -> save();

                } else {
                    $this -> panthera -> get_options('login.failures.exceeded', array('user' => $u, 'failures' => $u -> attributes -> get('loginFailures'), 'expiration' => $u -> attributes -> get('loginFailureExpiration')));
                    $this -> panthera -> template -> push('message', localize('Number of login failures exceeded, please wait a moment before next try', 'messages'));
                    $this -> displayTemplate();
                    pa_exit();
                }
            }
        }

        $result = userTools::userCreateSession($_POST['log'], $_POST['pwd'], False, True);

        // if user has no active account
        if (!$result -> active)
            $result = false;
        else
            $result = userTools::userCreateSession($_POST['log'], $_POST['pwd']);

        /**
         * Successful login
         *
         * @author Damian Kęska
         */

        if($result and is_object($result))
        {
            $this -> getFeature('login.success', $u);

            $u -> attributes -> set('loginFailures', 0);
            $u -> attributes -> remove('loginFailureExpiration');
            $u -> save();

            // if user cannot access Admin Panel, redirect to other location (specified in redirect_after_login config section)
            if (!$this -> checkPermissions('admin.accesspanel', true))
                pa_redirect($this -> panthera -> config -> getKey('redirect_after_login', 'index.php', 'string', 'pa-login'));

            if ($this -> panthera -> session -> exists('login_referer'))
            {
                header('Location: ' .$this -> panthera -> session -> get('login_referer'));
                $this -> panthera -> session -> remove ('login_referer');
                pa_exit();
            }

            pa_redirect('pa-admin.php');
            pa_exit();

        /**
         * Suspended/banned account
         *
         * @author Damian Kęska
         */

        } elseif ($result === 'BANNED') {
            $this -> panthera -> template -> push('message', localize('This account has been suspended, please contact administrator for details', 'messages'));

            $this -> getFeature('login.failures.suspended', array(
                'user' => $u,
                'failures' => $u -> attributes -> get('loginFailures'),
                'expiration' => $u -> attributes -> get('loginFailureExpiration'),
            ));


        /**
         * Login failure
         *
         * @author Damian Kęska
         */

        } elseif ($result === False) {
            $this -> panthera -> template -> push('message', localize('Invalid user name or password', 'messages'));

            if ($u and $u -> exists())
            {
                $u -> attributes -> set('loginFailures', intval($u -> attributes -> get('loginFailures'))+1);
                $banned = False;

                if ($u -> attributes -> get('loginFailures') >= intval($this -> panthera -> config -> getKey('login.failures.max', 5, 'int', 'pa-login')))
                {
                    $banned = True;
                    $u -> attributes -> set('loginFailureExpiration', (time()+intval($this -> panthera -> config -> getKey('login.failures.bantime', 300, 'int', 'pa-login')))); // 5 minutes by default
                }

                $this -> getFeature('login.failure', array(
                    'user' => $u,
                    'failures' => $u -> attributes -> get('loginFailures'),
                    'expiration' => $u -> attributes -> get('loginFailureExpiration'),
                    'banned' => $banned,
                ));

                $u -> attributes -> set('lastFailLoginIP', $_SERVER['REMOTE_ADDR']);
                $u -> save();
            }
        }
    }
}

// this code will run this controller only if this file is executed directly, not included
pageController::runFrontController(__FILE__, 'pa_loginControllerSystem');